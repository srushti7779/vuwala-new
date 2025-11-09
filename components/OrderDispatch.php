<?php
namespace app\components;

use app\models\User;
use app\modules\admin\models\DriverRequest;
use app\modules\admin\models\GcOrders;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\WebSetting;
use Yii;
use yii\base\Component;

class OrderDispatch extends Component
{

    public function assignAuto()
    {
        $setting = new WebSetting();
        $auto_dispatch_type = $setting->getSettingBykey('auto_dispatch_type');
        $auto_dispatch_radius = $setting->getSettingBykey('auto_dispatch_radius');
        $auto_dispatch_no_of_drivers = $setting->getSettingBykey('auto_dispatch_no_of_drivers');

        $get = Yii::$app->request->get();
        $id = $get['order_id'];
        if (!empty($get)) {
            $getOrder = Orders::find()
            ->Where(['id' => $get['order_id']])
            ->andWhere(['!=', 'status', Orders::STATUS_DELIVERED])
            ->andWhere(['IS', 'driver_id', null])
// echo $getOrder->createCommand()->getRawSql();exit;

            ->one();
            if (!empty($getOrder)) {
                //get Lat and Lng of Store

                $source_lat = $getOrder['store']->lat; //$post['source_lat'];
                $source_lng = $getOrder['store']->lng; //$post['source_lng'];

                //near by Drivers

                $nearby_drivers = User::find()
                // ->joinWith('driverDetails as dd')
                    ->select("*,	( 6371 * acos( cos( radians({$source_lat}) ) *
            cos( radians( `lat` ) ) * cos( radians( `lng` ) - radians({$source_lng}) ) +
            sin( radians({$source_lat}) ) * sin( radians( `lat` ) ) ) ) AS distance")->having("distance <:distance")->addParams([
                    ':distance' => isset($auto_dispatch_radius) ? $auto_dispatch_radius : 3, //distance kms
                ])->limit(isset($auto_dispatch_no_of_drivers) ? $auto_dispatch_no_of_drivers : 10)
                //->joinWith('alldrivers as dd')
                    ->Where(['user_role' => User::ROLE_DRIVER])
                    ->andWhere(['online_status' => User::DELIVERYBOY_ONLINE, 'user.status' => User::STATUS_ACTIVE])
    
                    ->orderBy([
                        'distance' => SORT_ASC, //specify sort order ASC for ascending DESC for descending
                    ])->all();
                     //make dynamic from settings
// echo $nearby_drivers->createCommand()->getRawSql();exit;
            // print_r( $auto_dispatch_type);exit;
                if (!empty($nearby_drivers)) {
                    //Check which Delivery Assign type
                    switch ($auto_dispatch_type) {

                        case 1:
                            $assigned = Yii::$app->orderDispatch->assignOne($nearby_drivers, $id);
                          
                            if ($assigned['status'] == 'NOK') {
                                $data['status'] = 'NOK';
                                $data['error'] =  $assigned['accept_state'];
                            } else {
                                $data['status'] = 'OK';
                                $data['msg'] = 'Order Assigned';
                            }
                            break;
                        case 2:
                            $assigned = Yii::$app->orderDispatch->assignAll($nearby_drivers, $id);
                            if ($assigned['status'] == 'NOK') {
                                $data['status'] = 'NOK';
                                $data['error'] = $assigned['accept_state'];
                            } else {
                                $data['status'] = 'OK';
                                $data['msg'] = $assigned['accept_state'];
                            }
                            break;
                        case 3:
                            $assigned = Yii::$app->orderDispatch->assignNearby($nearby_drivers, $id);
                            if ($assigned['status'] == 'NOK') {
                                $data['status'] = 'NOK';
                                $data['error'] = 'Order Assigning failed';
                            } else {
                                $data['status'] = 'OK';
                                $data['msg'] = 'Order Assigned to Nearby driver! Waiting for Accept';
                            }
                            break;
                       
                        default:
                            $assigned = Yii::$app->orderDispatch->assignOne($nearby_drivers, $id);
                            if ($assigned['status'] == 'NOK') {
                                $data['status'] = 'NOK';
                                $data['error'] = 'Order Assigning failed';
                            } else {
                                $data['status'] = 'OK';
                                $data['msg'] = 'Order Assigned';
                            }
                            break;

                    }

                }else{
                    $data['status'] = 'NOK';
                    $data['error'] = 'No Nearby drivers found';
                }

            } else {
                $data['status'] = 'NOK';
                $data['error'] = 'No Order found';
            }

        } else {
            $data['status'] = 'NOK';
            $data['error'] = 'No data Posted';
        }
        return $data;

    }

    //Assigning One by One

    public function assignOne($nearby_drivers, $id)
    {
        
        $setting = new WebSetting();
        $auto_dispatch_wait_time = $setting->getSettingBykey('auto_dispatch_wait_time');
        $data = [];

        $i = 0;
        foreach ($nearby_drivers as $nearby_driver) {

            //Check order Accepted or Not
            $check_accept = DriverRequest::find()->Where(['order_id' => $id, 
            'status' => DriverRequest::ORDER_ACCEPT])->one();
            if (empty($check_accept)) {

                // var_dump($nearby_driver);exit;
                $driver_request = new DriverRequest();
                $driver_request->order_id = $id;
                $driver_request->driver_id = $nearby_driver['id'];
                $driver_request->status = DriverRequest::ORDER_ASSIGNED;
                $driver_request->updated_on = date('Y-m-d H:i:s');
                $driver_request->created_on = date('Y-m-d H:i:s');
                if ($driver_request->save()) {

                    $title = 'New Order #' . $id . ' assigned';
                    $body = 'New Order Request';
                    $send_noti = Yii::$app->notification->DriverNotification($id,$driver_request->driver_id,$title,$body );
                    sleep(isset($auto_dispatch_wait_time)?$auto_dispatch_wait_time:30);

                    // Check Status of Accept

                    $check = $driver_request->find()->Where(['id' => $driver_request->id, 'driver_id' => $driver_request->driver_id])->one();

                    if ($check->status == DriverRequest::ORDER_ACCEPT) {

                        $data['status'] = "OK";
                        $data['details'] = $check;
                        // $data['notification'] =  $send_noti;
                        break;
                    } else if ($check->status == DriverRequest::ORDER_REJECT) {
                        $data['status'] = "OK";
                        $data['details'] = $check;
                        //  $data['notification'] =  $send_noti;

                    } else {

                        $check->status = DriverRequest::ORDER_CANCELLED;
                        $check->updated_on = date('Y-m-d H:i:s');
                        if ($check->update(false)) {
                            $data['status'] = "NOK";
                            $data['details'] = $check;
                            $data['accept_state'] = "No Driver accepted your Order.Please contact Admin to assign Order manually to Delivery Driver";
                            //   $data['notification'] =  $send_noti;
                        }

                    }

                } else {
                    $data['status'] = "NOK";
                    $data['details'] = $driver_request;
                }

                //$data['notification'] = $send_noti;
            } else {
                $data['status'] = "NOK";
                $data['details'] = "Order already Accepted";
                $data['accept_state'] = "Order already Accepted";
            }
        }
        return $data;
    }

      //Assigning All Availble Drivers at once

      public function assignAll($nearby_drivers, $id)
      {
          
          $setting = new WebSetting();
          $auto_dispatch_wait_time = $setting->getSettingBykey('auto_dispatch_wait_time');
          $data = [];
  
          $i = 0;
          foreach ($nearby_drivers as $nearby_driver) {
  
              //Check order Accepted or Not
              $check_accept = DriverRequest::find()->Where(['order_id' => $id, 
              'status' => DriverRequest::STATUS_ACCEPTED])->one();
              if (empty($check_accept)) {
  
                  // var_dump($nearby_driver);exit;
                  $driver_request = new DriverRequest();
                  $driver_request->order_id = $id;
                  $driver_request->driver_id = $nearby_driver['id'];
                  $driver_request->status = DriverRequest::STATUS_NEW_REQUEST;
                  $driver_request->updated_on = date('Y-m-d H:i:s');
                  $driver_request->created_on = date('Y-m-d H:i:s');
                  if ($driver_request->save(false)) {
                    $orderStatus = new OrderStatus();
                    $orderStatus->order_id = $id;
                    $orderStatus->driver_id = $nearby_drivers[0]['id'];
                    $orderStatus->status = DriverRequest::STATUS_NEW_REQUEST;
                    $orderStatus->remarks = "New Order of ID# " .$id . " assigned to Driver id# " . $nearby_drivers[0]['id'] . ' at ' . date('Y-m-d H:i:s');
                    $orderStatus->save(false);
                      $title = 'New Order #' . $id . ' assigned';
                      $body = 'New Order Request';
                      $send_noti = Yii::$app->notification->driverNotification($id,$driver_request->driver_id,$title,$body,'newOrder' );
                     
                    
                      
                  } else {
                      $data['status'] = "NOK";
                      $data['details'] = $driver_request;
                      $data['accept_state'] = "failed to save request";
                             
                  }
    // Check Status of Accept code at Accepting Order API
    $data['status'] = "OK";
    $data['details'] = $driver_request;
    $data['accept_state'] = "Order Assigned to all Drivers";
                  //$data['notification'] = $send_noti;
              } else {
                  $data['status'] = "NOK";
                  $data['details'] = "Order already Accepted";
                  $data['accept_state'] = "Order already Accepted";
                             
              }
          }
         

          return $data;
      }

      //    //Assigning Nearby One driver

    public function assignNearby($nearby_drivers, $id)
    {
      
        $setting = new WebSetting();
        $auto_dispatch_wait_time = $setting->getSettingBykey('auto_dispatch_wait_time');
        $data = [];

        $i = 0;
      
            //Check order Accepted or Not
            $check_accept = DriverRequest::find()
            ->Where(['order_id' => $id, 
            'status' => DriverRequest::STATUS_ACCEPTED])->one();
            if (empty($check_accept)) {

                // var_dump($nearby_driver);exit;
                $driver_request = new DriverRequest();
                $driver_request->order_id = $id;
                $driver_request->driver_id = $nearby_drivers[0]['id'];
                $driver_request->status = DriverRequest::STATUS_NEW_REQUEST;
                $driver_request->updated_on = date('Y-m-d H:i:s');
                $driver_request->created_on = date('Y-m-d H:i:s');
                if ($driver_request->save(false)) {
                    $orderStatus = new OrderStatus();
                    $orderStatus->order_id = $id;
                    $orderStatus->driver_id = $nearby_drivers[0]['id'];
                    $orderStatus->status = DriverRequest::STATUS_NEW_REQUEST;
                    $orderStatus->remarks = "New Order of ID# " .$id . " assigned to Driver id# " . $nearby_drivers[0]['id'] . ' at ' . date('Y-m-d H:i:s');
                    $orderStatus->save(false);
                    $title = 'New Order #' . $id . ' assigned';
                    $body = 'New Order Request';
                    if (isset($title)) {

                        $send_noti = Yii::$app->notification->driverNotification($id,$driver_request->driver_id,$title,$body, 'newOrder');
        
                    }
                 //   $send_noti = Yii::$app->notification->driverNotification($id,$driver_request->driver_id,$title,$body );
                    $data['status'] = "OK";
                    $data['details'] = $driver_request;
                    // print_r($send_noti);exit;

                } else {
                    $data['status'] = "NOK";
                    $data['details'] = $driver_request;
                }

                //$data['notification'] = $send_noti;
            } else {
                $data['status'] = "NOK";
                $data['details'] = "Order already Accepted";
            }
        
        return $data;
    }
  
}

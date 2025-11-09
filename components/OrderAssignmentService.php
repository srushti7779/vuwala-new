<?php 
namespace app\components;

use app\modules\admin\models\base\Staff;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\VendorDetails;
use Yii;
use yii\helpers\ArrayHelper;


class OrderAssignmentService
{
    const API_OK = 'OK';
	const API_NOK = 'NOK';

    private $resp = [
		'status' => self::API_NOK
	];

    public  function sendJsonResponse($data = null)
	{
		if ($data != null)
			$this->resp = ArrayHelper::merge($this->resp, $data);

		return $this->resp;
	}



    public static function assign($user_id, $staff_id, $order_id)
    {
          $data = [];
    
        try {
           





            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (!$vendorDetails) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return (new self())->sendJsonResponse($data);
            }

            // Fetch the order
            $order = Orders::findOne(['id' => $order_id]);
            if (!$order) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Order not found.");
                return (new self())->sendJsonResponse($data);
            }


            // Convert schedule time to 24-hour format for comparison
            $scheduledDateTime = \DateTime::createFromFormat('Y-m-d h:i A', $order->schedule_date . ' ' . $order->schedule_time);
            if (!$scheduledDateTime) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Invalid schedule format.");
                return (new self())->sendJsonResponse($data);
            }


            // Check if staff already has an order at the same time
            $conflictOrder = Orders::find()
                ->alias('o')
                ->innerJoin(HomeVisitorsHasOrders::tableName() . ' ho', 'ho.order_id = o.id')
                ->where(['ho.home_visitor_id' => $staff_id])
                ->andWhere(['o.schedule_date' => $order->schedule_date])
                ->andWhere(['o.status' => Orders::STATUS_ASSIGNED_SERVICE_STAFF])
                ->andWhere(['o.service_type' => $order->service_type])
                ->andWhere(['!=', 'o.id', $order_id])
                ->andWhere(['o.schedule_time' => $order->schedule_time])
                ->one();

            if ($conflictOrder) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "This staff member is already assigned to another order at the same time.");
                return (new self())->sendJsonResponse($data);
            }

            // Check if the order is already assigned 
            $existingAssignment = HomeVisitorsHasOrders::find()
                ->where(['order_id' => $order_id])
                ->one();

            if ($existingAssignment) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "This order is already assigned to another home visitor.");
                return (new self())->sendJsonResponse($data);
            }

            // Check if the order is already assigned to the same home visitor
            $sameAssignment = HomeVisitorsHasOrders::findOne([
                'order_id' => $order_id,
                'home_visitor_id' => $staff_id
            ]);

            if ($sameAssignment) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "This order is already assigned to the specified home visitor.");
                return (new self())->sendJsonResponse($data);
            }



            // Validate staff role based on order type
            $staff = Staff::findOne(['id' => $staff_id]);
            if (!$staff) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Staff not found.");
                return (new self())->sendJsonResponse($data);
            }




            if ($staff->status != Staff::STATUS_ACTIVE) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t(
                    "app",
                    $staff->status != Staff::STATUS_ACTIVE
                        ? "Selected staff is not active."
                        : "Selected staff is not active."
                );
                return (new self())->sendJsonResponse($data);
            }


            // Assign order and update status
            $order->status = Orders::STATUS_ASSIGNED_SERVICE_STAFF;


            if (!$order->save(false)) {
                Yii::error("Failed to update order status for order ID: {$order_id}", __METHOD__);
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to update the order status.");
                return (new self())->sendJsonResponse($data);
            }

            $homeVisitorsHasOrders = new HomeVisitorsHasOrders();
            $homeVisitorsHasOrders->order_id = $order_id;
            $homeVisitorsHasOrders->home_visitor_id = $staff_id;
            $homeVisitorsHasOrders->status = $order->status;



            if (!$homeVisitorsHasOrders->save(false)) {
                Yii::error("Failed to assign order ID: {$order_id} to home visitor ID: {$staff_id}", __METHOD__);
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to assign the order to the home visitor.");
                return (new self())->sendJsonResponse($data);
            }


            // Save order status history
            $orderStatus = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status = $order->status;
            $orderStatus->remarks = Yii::t("app", "Order status updated to {status}", ['status' => $order->getStateOptionsBadges()]);
            if (!$orderStatus->save(false)) {
                Yii::error("Failed to save order status history for order ID: {$order_id}", __METHOD__);
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to save order status history.");
                return (new self())->sendJsonResponse($data);
            }

            // Update staff status
            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
            $staff->save(false);


            // Send notifications          
            try {

                // $otp = $order->otp;
                // Determine if the order is a home visit
                $isHomeVisit = $order->service_type == Orders::TRANS_TYPE_HOME_VISIT;

                $titleUser = Yii::t("app", "Your Order Assigned to Staff");
                $bodyUser = $isHomeVisit
                    ? Yii::t("app", "Your order (#{$order_id}) has been assigned to a home visitor.")
                    : Yii::t("app", "Your order (#{$order_id}) has been assigned to a staff member.");

                // Push notification to the user 
                Yii::$app->notification->PushNotification(
                    $order_id,
                    $order->user_id,
                    $titleUser,
                    $bodyUser,
                    $isHomeVisit ? "home_visit" : "walk_in" 
                );

                // Notify home visitor only for home visit orders
                if ($isHomeVisit) {
                    $titleVisitor = Yii::t("app", "New Order Assignment");
                    $bodyVisitor = Yii::t("app", "You have been assigned a new home visit order (#{$order_id}). Please proceed with the service.");

                    // Push notification to the home visitor
                    Yii::$app->notification->PushNotification(
                        $order_id,
                        $staff->user_id,
                        $titleVisitor,
                        $bodyVisitor,
                        "home_visit"
                    );
                }
            } catch (\Exception $e) {
                // Log the error
                Yii::error("Notification error: " . $e->getMessage(), __METHOD__);
            }


            //new updated code for sending push notification 

            // Success response 
            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Order successfully assigned to the staff.");
            return (new self())->sendJsonResponse($data);
        } catch (\Exception $e) {
            Yii::error("Error processing order assignment: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred while processing the request.");
            return (new self())->sendJsonResponse($data);
        }
    }
}

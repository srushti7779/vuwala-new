<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\base\FcmNotification;
use app\modules\admin\models\ShopLikes;
use app\modules\admin\models\StoreLike;
use CURLFile;
use Google_Client;

class FirebaseNotification extends Component
{

    public function FirebaseApi($id = '', $user_id, $title, $body, $api_key, $device_token, $type = '')
    {

        $msg = array(
            'body' => strip_tags($body),
            'title' => $title,
            'vibrate' => 1,
            'sound' => 1,
            'order_id' => $id,
            'type' => $type,

        );

        $msg1 = array(
            'body' => strip_tags($body),
            'title' => $title,
            'vibrate' => 1,
            'sound' => 1,
            'order_id' => $id,
            'type' => $type,
        );
        // var_dump($msg1);exit;
        $fields = array(
            'to' => $device_token,
            'collapse_key' => 'type_a',
            'notification' => $msg1,
            'data' => $msg,

        );
        $headers = array(
            'Authorization: key=' . $api_key,
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        // print_r($result);exit;
        curl_close($ch);
    }

    public function newFirebaseNotificationApi($id, $user_id, $title, $body, $device_token, $order_type)
    {

        $setting = new WebSetting();
        $curl = curl_init();
        $token = $this->getGoogleAccessToken();

        $project_id = $setting->getSettingBykey('project_id');


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/$project_id/messages:send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "message" => array(
                    "token" => $device_token,
                    "notification" => array(
                        "title" => $title,
                        "body" => $body,
                        // "click_action"=>"FLUTTER_NOTIFICATION_CLICK"

                    ),
                    "data" => array(
                        "title" => $title,
                        "body" => $body,
                        "type" => $order_type,
                        // "bookingId"=>'1113',
                        // 'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
                        // "sound" => "siren"
                    ),


                ),
            )),      
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        // var_dump($response);  
        // exit;     
        curl_close($curl);
        return $response;
    }


    
    private function getGoogleAccessToken()
    {

        $credentialsFilePath = 'estetica.json'; //replace this with your actual path and file name
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token['access_token'];
    }
 
    //Send Notification to User
    public function UserNotification($id = '', $user_id, $title, $body, $type = '')
    {

        // var_dump($id);exit;
        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('user_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($user_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id, $user_id, $title, $body, $customer_notification_key, $device_token, $type);
    }



    //Vendor Notification
    public function vendorNotification($id = '', $user_id, $title, $body)
    {


        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('vendor_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($user_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id, $user_id, $title, $body, $customer_notification_key, $device_token);
    }

    public function homevisitorNotification($user_id, $title, $body)
    {
        // Fetch notification settings (Firebase API key)
        $setting = new WebSetting();
        $homevisitor_notification_key = $setting->getSettingBykey('vendor_notification_key');

        // Fetch user device token
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($user_id);

        // Validate device token
        if (empty($device_token)) {
            Yii::error("No device token found for user ID: $user_id", __METHOD__);
            return;
        }

        try {
            // Call the Firebase function to send notification
            Yii::$app->notification->FirebaseApi($user_id, $title, $body, $homevisitor_notification_key, $device_token);
            Yii::info("Notification sent successfully to user $user_id", __METHOD__);
        } catch (\Exception $e) {
            Yii::error("Error sending Firebase notification: " . $e->getMessage(), __METHOD__);
        }
    }




    //Driver Notification 
    public function driverNotification($id = '', $user_id, $title, $body, $type = '')
    {


        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('driver_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($user_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id, $user_id, $title, $body, $customer_notification_key, $device_token, $type);
    }
    //Send SMS
    public function sendSMS($contact_no, $msg)
    {

        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://2factor.in/API/V1/$sms_api_key/ADDON_SERVICES/SEND/TSMS",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('From' => 'TFCTOR', 'To' => '91' . $contact_no, 'Msg' => $msg),
            CURLOPT_HTTPHEADER => array(
                "Cookie: __cfduid=d3873a75f3e6843a5117359bd027d9c7a1588843417",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
    //Notification BY Topi
    public function FirebaseApiTopic($title, $body, $imgUrl, $storeId, $type = '')
    {

        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('user_notification_key');
        $msg = array(
            'image' => $imgUrl,
            'body' => $body,
            'title' => $title,
            'vibrate' => 1,
            'sound' => 1,
            'store_id' => $storeId,
            'type' => $type,

        );

        $msg1 = array(
            'image' => $imgUrl,
            'body' => $body,
            'title' => $title,
            'vibrate' => 1,
            'sound' => 1,
            'store_id' => $storeId,
            'type' => $type,
        );
        // var_dump($msg1);exit;
        $fields = array(
            'to' => '/topics/' . $storeId,
            'collapse_key' => 'type_a',
            'notification' => $msg1,
            'data' => $msg,

        );
        $headers = array(
            'Authorization: key=' . $customer_notification_key,
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        curl_close($ch);

        //Get Subscribers user id
        $liked_users = ShopLikes::find()->Where(['store_id' => $storeId])->all();
        if (!empty($liked_users)) {
            foreach ($liked_users as $liked_user) {


                $fcmNoti = new FcmNotification();
                $fcmNoti->user_id = $liked_user['user_id'];
                $fcmNoti->title = $title;
                $fcmNoti->body = $body;
                $fcmNoti->created_on = date('Y-m-d H:i:s');
                $fcmNoti->shop_id = $storeId;
                $fcmNoti->type = $type;
                $fcmNoti->save(false);
                if (!$fcmNoti->save(false)) {
                    print_r($fcmNoti->getErrors());
                }
            }
        }
        return $result;
    }
                          
    //Send OTP
    public function sendOtp($contact_no)  
    {
                  
        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();
        $otp = rand(1111, 9999);
        // $url = "https://2factor.in/API/V1/$sms_api_key/SMS/+91$contact_no/$otp/OTP+VERIFICATION";  
        // var_dump($url);die();            
        $url = "https://2factor.in/API/V1/$sms_api_key/SMS/+91$contact_no/$otp/OTP+VERIFICATION";
        // var_dump($url);die();  
        // print_r($url);exit;
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://2factor.in/API/V1/$sms_api_key/SMS/+91$contact_no/$otp/OTP+VERIFICATION",

            CURLOPT_RETURNTRANSFER => true,  
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cookie: __cfduid=d3873a75f3e6843a5117359bd027d9c7a1588843417",
            ),
        ));

        $response = curl_exec($curl);

        // var_dump($response);die();     

        curl_close($curl);

        return $response;
    }


    
    //Verify OTP
    public function verifyOtp($session_code, $otp_code)
    {
        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();
        //  var_dump($sms_api_key); exit;

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://2factor.in/API/V1/$sms_api_key/SMS/VERIFY/$session_code/$otp_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cookie: __cfduid=d3873a75f3e6843a5117359bd027d9c7a1588843417",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }




    // public function imageKitUpload($image)
    // {

    //     if (!empty($image->tempName)) {

    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(
    //             CURLOPT_URL => 'https://upload.imagekit.io/api/v1/files/upload',
    //             CURLOPT_RETURNTRANSFER => true,
    //             CURLOPT_ENCODING => '',
    //             CURLOPT_MAXREDIRS => 10,
    //             CURLOPT_TIMEOUT => 0,
    //             CURLOPT_FOLLOWLOCATION => true,
    //             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //             CURLOPT_CUSTOMREQUEST => 'POST',
    //             CURLOPT_POSTFIELDS => array(
    //                 'file' => new CURLFile($image->tempName),
    //                 'fileName' => rand(1111111111, 999999999999)
    //             ),
    //             CURLOPT_HTTPHEADER => array(
    //                 'Authorization: Basic cHJpdmF0ZV9NTTFlRzFZNXFRbkVjbWQwZW5FUFpXS2lvSHM9Og=='
    //             ),
    //         ));

    //         $response = curl_exec($curl);


    //         curl_close($curl);

    //         return json_decode($response, true);
    //     }
    // }




    public function imageKitUpload($image)
{
    if (!empty($image->tempName)) {

        // Replace with your real keys
        $privateKey = 'private_lVl4BmHf43ou9SdZxYK1xhnNg9c=';

        $authHeader = 'Authorization: Basic ' . base64_encode($privateKey . ':');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://upload.imagekit.io/api/v1/files/upload',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'file' => new CURLFile($image->tempName),
                'fileName' => uniqid("img_")
            ),
            CURLOPT_HTTPHEADER => array(
                $authHeader
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    return null;
}





    public function customVendorNoti($id = '', $user_id, $title, $body, $type = '')
    {



        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('vendor_notification_key');
        $auth_sess = new AuthSession();
        $device_tokens = $auth_sess->getDeviceToken($user_id);



        if (!empty($device_tokens)) {

            foreach ($device_tokens as $device_token) {
                $msg = array(
                    'body' => strip_tags($body),
                    'title' => $title,
                    'id' => $id,
                    'order_id' => $id,
                    'type' => $type,
                    "notificationLayout" => "Call",
                    "channelKey" => "call_channel",
                    "showWhen" => true,
                    "wakeUpScreen" => true,

                );

                $content = array(
                    "content" => $msg,
                    "order_id" => $id,
                    'type' => $type,


                );


                $fields = array(
                    'to' => $device_token,
                    'collapse_key' => 'type_a',
                    "mutable_content" => true,
                    "content_available" => true,
                    "priority" => "high",

                    'data' => $content,

                );

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($fields),

                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: key=' . $customer_notification_key,
                    ),
                ));

                $response = curl_exec($curl);
                // var_dump($response);
                // die();

                $fcm_notification = new FcmNotification();
                $fcm_notification->user_id = $user_id;
                $fcm_notification->title = $title;
                $fcm_notification->order_id = $id;
                $fcm_notification->message = strip_tags($body);
                $fcm_notification->is_read = FcmNotification::IS_READ_NO;
                $fcm_notification->status = FcmNotification::STATUS_ACTIVE;
                $fcm_notification->save(false);


                curl_close($curl);
                $response;
            }
        }
    }

    public function PushNotification($id = '', $user_id, $title, $body, $order_type = "")
    {    
 
        $setting = new WebSetting();
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceTokenUser($user_id); 



  

        $data = Yii::$app->notification->newFirebaseNotificationApi($id, $user_id, $title, $body, $device_token, $order_type);

        $fcm_notification = new FcmNotification();
        $fcm_notification->order_id = $id;
        $fcm_notification->user_id = $user_id;
        $fcm_notification->title = $title;
        $fcm_notification->message = strip_tags($body);
        $fcm_notification->is_read = FcmNotification::IS_READ_NO;
        $fcm_notification->status = FcmNotification::STATUS_ACTIVE; 
        
        
        if (!empty($data)) {  
  
       
            $fcm_notification->save(false);

      
        }
         
    }



} 

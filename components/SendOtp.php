<?php

namespace app\components;

use app\modules\admin\models\AuthSession;
use app\modules\admin\models\WebSetting;
use Yii;
use yii\base\Component;

class SendOtp extends Component
{
 
    //Send Notification to Driver
    public function DriverNotification($id = '', $driver_id, $title, $body)
    {
        $setting = new WebSetting();
        $driver_notification_key = $setting->getSettingBykey('driver_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($driver_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id = '', $driver_id, $title, $body, $driver_notification_key, $device_token);

    }
//Send Notification to Store
    public function StoreNotification($id = '', $driver_id, $title, $body)
    {
        $setting = new WebSetting();
        $restaurant_notification_key = $setting->getSettingBykey('restaurant_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($driver_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id = '', $driver_id, $title, $body, $restaurant_notification_key, $device_token);

    }
    //Send Notification to User
    public function UserNotification($id = '', $driver_id, $title, $body)
    {

        $setting = new WebSetting();
        $customer_notification_key = $setting->getSettingBykey('customer_notification_key');
        $auth_sess = new AuthSession();
        $device_token = $auth_sess->getDeviceToken($driver_id);
        $title = $title;
        $body = $body;

        Yii::$app->notification->FirebaseApi($id = '', $driver_id, $title, $body, $customer_notification_key, $device_token);
    }

    //Send SMS
    public function sendOtp($contact_no)
    {

        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/otp?authkey=$sms_api_key&template_id=5efc71d0d6fc0533242e955f&mobile=$contact_no",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    //Verify OTP
    public function verifyOtp($contact_no, $otp_code)
    {
        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/otp/verify?mobile=$contact_no&otp=$otp_code&authkey=$sms_api_key",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    //Resend Otp
    public function resendOtp($contact_no)
    {
        $setting = new WebSetting();
        $sms_api_key = $setting->getSettingBykey('sms_api_key');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/otp/retry?mobile=$contact_no&authkey=$sms_api_key&retrytype=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }
}

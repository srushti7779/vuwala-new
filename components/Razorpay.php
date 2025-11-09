<?php

namespace app\components;

use yii\base\Component;
use app\modules\admin\models\WebSetting;

class Razorpay extends Component
{

  const ORDER_TYPE_SERVICE_ORDER = 'service_order';
  const ORDER_TYPE_SUBSCRIPTION_ORDER = 'subscription_order';







  private static function getAuthorization()
  {
    $settings = new WebSetting();
    $razorpay_username = $settings->getSettingBykey('razorpay_username');
    $razorpay_password = $settings->getSettingBykey('razorpay_password');

    $passWordEncoded = base64_encode("$razorpay_username:$razorpay_password");
    return $passWordEncoded;
  }


  public static function createAnOrder($order_id, $amount)
  {

    $amount = $amount * 100;

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
      "amount": ' . $amount . ',
      "currency": "INR",
      "receipt": "order_' . $order_id . rand(1111, 9999) . time() . '" 
     
    }',
      CURLOPT_HTTPHEADER => array(
        'content-type: application/json',
        'Authorization: Basic ' . self::getAuthorization() 
      ),
    ));

    $response = curl_exec($curl); 

    // var_dump($response);die(); 
    curl_close($curl);
    return  $response;
  }









  public static function createAnOrderVendorSubscription($order_id, $amount)
  {

    $amount = $amount * 100;

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
    "amount": ' . $amount . ',
    "currency": "INR",
    "receipt": "order_' . $order_id . rand(1111, 9999) . time() . '",
    "notes": {
   "subscription_order_id": "' . $order_id . '",
    "order_type": "subscription_order"
 
    }
  }',
      CURLOPT_HTTPHEADER => array(
        'content-type: application/json',
        'Authorization: Basic ' . self::getAuthorization()
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return  $response;
  }


  public static function fetchOrderDetails($order_id)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/orders/' . $order_id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . self::getAuthorization()
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return  $response;
  }


  public static function fetchPaymentDetails($payment_id)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/payments/' . $payment_id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . self::getAuthorization()
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }

  public static function capturePayment($amount, $payment_id)
  {

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
    "amount": ' . $amount . ',
    "currency": "INR"
  }',
      CURLOPT_HTTPHEADER => array(
        'content-type: application/json',
        'Authorization: Basic ' . self::getAuthorization()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }


  public static function verifyPayment($razorpay_order_id, $razorpay_payment_id)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/payments/' . $razorpay_payment_id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,  
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . self::getAuthorization()
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);    


    // var_dump($response);die();   

    // Decode the response
    $payment_details = json_decode($response, true);

    // Check if the order ID in the payment details matches the provided order ID
    if (isset($payment_details['order_id']) && $payment_details['order_id'] == $razorpay_order_id) {
      return $payment_details;
    } else {
      return null; // Order ID mismatch or invalid payment 
    }
  }


}

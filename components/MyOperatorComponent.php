<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\modules\admin\models\WebSetting;

class MyOperatorComponent extends Component
{
    private $api_token;
    private $secret_token;
    private $company_id;
    private $public_ivr_id;
    private $x_api_key;

    public function __construct()
    {
        $setting = new WebSetting();
        $this->api_token = $setting->getSettingBykey('myoperator_api_token');
        $this->secret_token = $setting->getSettingBykey('myoperator_secret_key');
        $this->company_id = $setting->getSettingBykey('myoperator_Company_id');
        $this->x_api_key = $setting->getSettingBykey('myoperator_x_api_key');
        $this->public_ivr_id = $setting->getSettingBykey('myoperator_public_ivr_id'); // optional if you store public_ivr_id
    }

  public function createUser($name, $contact_number, $country_code, $extension, $email = '')
{
    $url = "https://developers.myoperator.co/user";

    // Build POST data in correct format
    $postData = http_build_query([
        'data[adduser][token]' => '596b513c321d913e68d813abb13d3051',
        'data[adduser][name]' => $name,
        'data[adduser][contact_number]' => $contact_number,
        'data[adduser][country_code]' => $country_code,
        'data[adduser][extension]' => $extension,
        'data[adduser][email]' => $email
    ]);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return [
            'status' => 'error',
            'message' => 'cURL Error: ' . $err
        ];
    }

    // Decode and return response as array
    return $response;
}


    // ✅ Make call (peer to peer call)
    public function makeCall($user_id, $customer_number, $reference_id = null, $region = '', $caller_id = '', $group = '')
    {
        $url = "https://obd-api.myoperator.co/obd-api-v1";

        if (empty($reference_id)) {
            $reference_id = uniqid("ref_");
        }

        $payload = [
            "company_id" => $this->company_id,
            "secret_token" => $this->secret_token,
            "type" => "1", // peer to peer
            "user_id" => $user_id,
            "number" => $customer_number,
            "public_ivr_id" => $this->public_ivr_id,
            "reference_id" => $reference_id,
            "region" => $region,
            "caller_id" => $caller_id,
            "group" => $group
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "x-api-key: " . $this->x_api_key,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }







public  function makeAnonymousCall($call_from_number,$call_to_number){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://obd-api.myoperator.co/obd-api-v1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
 "company_id": "'.$this->company_id.'",
 "secret_token": "'.$this->secret_token.'",
 "type": "1", 
 "number": "+91'.$call_from_number.'",
 "number_2": "+91'.$call_to_number.'", 
 "public_ivr_id": "6818736e092d9498",
 "reference_id": "'. uniqid("ref_$call_from_number$call_to_number".time()).'",
 "region": "north",
 "caller_id": "+911234567890",
 "group": "support"
}',
  CURLOPT_HTTPHEADER => array(
    'x-api-key: oomfKA3I2K6TCJYistHyb7sDf0l0F6c8AZro5DJh',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

return $response;



    }





}

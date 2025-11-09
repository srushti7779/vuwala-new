<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\VendorSubscriptions;

/**
 * app\modules\admin\models\search\VendorSubscriptionsSearch represents the model behind the search form about `app\modules\admin\models\VendorSubscriptions`.
 */
 class VendorSubscriptionsSearch extends VendorSubscriptions
{

    public $contact_no;
    public $email;
    public $is_verified;
    public $gst_number;
    public $business_name;
    public $sent_invoice;
    
    public function rules()
    {
        return [
            [['id', 'vendor_details_id', 'subscription_id', 'status', 'create_user_id', 'update_user_id', 'amount', 'duration'], 'integer'],
            [
                [
                    'start_date', 'end_date', 'created_on', 'updated_on',
                    'contact_no', 'email', 'is_verified', 'gst_number',
                    'business_name', 'sent_invoice', 'payment_received_datetime'
                ],
                'safe'
            ],
        ];
    }
    

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = VendorSubscriptions::find()
        ->joinWith('vendorDetails.user');
    
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_on' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'vendor_details_id' => $this->vendor_details_id,
            'subscription_id' => $this->subscription_id,
            'amount' => $this->amount,
            'duration' => $this->duration,
            'start_date' => $this->start_date, 
            'end_date' => $this->end_date,
            'status' => $this->status,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

    $query->andFilterWhere(['like', 'user.contact_no', $this->contact_no])
    ->andFilterWhere(['like', 'user.email', $this->email])
    ->andFilterWhere(['like', 'vendor_details.gst_number', $this->gst_number])
    ->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
    ->andFilterWhere(['like', 'vendor_subscriptions.sent_invoice', $this->sent_invoice]);
    if ($this->is_verified !== null && $this->is_verified !== '') {
        if ($this->is_verified == 1) {
            $query->andWhere(['vendor_details.is_verified' => 1]);
        } else {
            $query->andWhere(['OR',
                ['vendor_details.is_verified' => 0],
                ['vendor_details.is_verified' => null],
                ['vendor_details.is_verified' => '']
            ]);
        }
    }
    

        return $dataProvider;
    }

    /**
     * Creates data provider instance with managersearch query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function managersearch($params)
    {
        $query = VendorSubscriptions::find()
                     ->where(['city_id' => \Yii::$app->user->identity->city_id])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_on' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'vendor_details_id' => $this->vendor_details_id,
            'subscription_id' => $this->subscription_id,
            'amount' => $this->amount,
            'duration' => $this->duration,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        if(isset ($this->created_on)&&$this->created_on!=''){ 
           
           //you dont need the if function if yourse sure you have a not null date
            $date_explode=explode(" - ",$this->created_on);
         //   var_dump($date_explode);exit;
            $date1=trim($date_explode[0]);
           $date2=trim($date_explode[1]);
           $query->andFilterWhere(['between','created_on',$date1,$date2]);
          // var_dump($query->createCommand()->getRawSql());exit;
          }
       if(isset ($this->updated_on)&&$this->updated_on!=''){ 
      
           //you dont need the if function if yourse sure you have a not null date
            $date_explode=explode(" - ",$this->updated_on);
         //   var_dump($date_explode);exit;
            $date1=trim($date_explode[0]);
           $date2=trim($date_explode[1]);
           $query->andFilterWhere(['between','updated_on',$date1,$date2]);
          //  var_dump($query->createCommand()->getRawSql());exit;
          }

        return $dataProvider; 
    }
}

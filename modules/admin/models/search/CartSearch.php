<?php

namespace app\modules\admin\models\search;

use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Cart;

/**
 * app\modules\admin\models\search\CartSearch represents the model behind the search form about `app\modules\admin\models\Cart`.
 */
 class CartSearch extends Cart
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'vendor_details_id', 'quantity', 'coupon_applied_id', 'status', 'service_address', 'type_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['amount', 'tip', 'wallet', 'cgst', 'sgst', 'coupon_discount', 'service_fees', 'other_charges'], 'number'],
            [['service_instructions', 'details', 'coupon_code', 'service_time', 'service_date', 'created_on', 'updated_on'], 'safe'],
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
        $query = Cart::find();

     
    if(User::isVendor())
    {
        $vendor = VendorDetails::findOne(['user_id' => Yii::$app->user->identity->id]);
        // Debuggingpr line, can be removed later
            $query->andWhere(['vendor_details_id' => $vendor->id??null]);
    }

   

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
            'user_id' => $this->user_id,
            'vendor_details_id' => $this->vendor_details_id,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'tip' => $this->tip,
            'wallet' => $this->wallet,
            'cgst' => $this->cgst,
            'sgst' => $this->sgst,
            'coupon_discount' => $this->coupon_discount,
            'coupon_applied_id' => $this->coupon_applied_id,
            'service_fees' => $this->service_fees,
            'other_charges' => $this->other_charges,
            'status' => $this->status,
            'service_address' => $this->service_address,
            'type_id' => $this->type_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'service_instructions', $this->service_instructions])
            ->andFilterWhere(['like', 'details', $this->details])
            ->andFilterWhere(['like', 'coupon_code', $this->coupon_code])
            ->andFilterWhere(['like', 'service_time', $this->service_time])
            ->andFilterWhere(['like', 'service_date', $this->service_date]);

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
        $query = Cart::find()
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
            'user_id' => $this->user_id,
            'vendor_details_id' => $this->vendor_details_id,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'tip' => $this->tip,
            'wallet' => $this->wallet,
            'cgst' => $this->cgst,
            'sgst' => $this->sgst,
            'coupon_discount' => $this->coupon_discount,
            'coupon_applied_id' => $this->coupon_applied_id,
            'service_fees' => $this->service_fees,
            'other_charges' => $this->other_charges,
            'status' => $this->status,
            'service_address' => $this->service_address,
            'type_id' => $this->type_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'service_instructions', $this->service_instructions])
            ->andFilterWhere(['like', 'details', $this->details])
            ->andFilterWhere(['like', 'coupon_code', $this->coupon_code])
            ->andFilterWhere(['like', 'service_time', $this->service_time])
            ->andFilterWhere(['like', 'service_date', $this->service_date]);

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

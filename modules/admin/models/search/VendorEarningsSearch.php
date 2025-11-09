<?php

namespace app\modules\admin\models\search;

use app\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\VendorEarnings;

/**
 * app\modules\admin\models\search\VendorEarningsSearch represents the model behind the search form about `app\modules\admin\models\VendorEarnings`.
 */
 class VendorEarningsSearch extends VendorEarnings
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'vendor_details_id', 'admin_commission_per', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['service_charge', 'order_sub_total', 'admin_commission_amount'], 'number'],
            [['earnings_added_reason', 'cancelled_reason', 'created_on', 'updated_on'], 'safe'],
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
    $query = VendorEarnings::find()
        ->alias('ve'); 

    // Add left join with vendor_settlements to exclude settled records
    $query->leftJoin('vendor_settlements vs', 'vs.vendor_earnings_id = ve.id')
          ->where(['vs.id' => null]); // Only include records with no settlement

    // If logged-in user is vendor, filter only their records
    if (User::isVendor()) {
        $vendor_details_id = User::getVendorIdByUser();
        $query->andWhere(['ve.vendor_details_id' => $vendor_details_id]);
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
        return $dataProvider;
    }

    // Filtering
    $query->andFilterWhere([
        've.id' => $this->id,
        've.service_charge' => $this->service_charge,
        've.order_id' => $this->order_id,
        've.vendor_details_id' => $this->vendor_details_id,
        've.order_sub_total' => $this->order_sub_total,
        've.admin_commission_per' => $this->admin_commission_per,
        've.admin_commission_amount' => $this->admin_commission_amount,
        've.status' => $this->status,
        've.create_user_id' => $this->create_user_id,
        've.update_user_id' => $this->update_user_id,
    ]);

    $query->andFilterWhere(['like', 've.earnings_added_reason', $this->earnings_added_reason])
          ->andFilterWhere(['like', 've.cancelled_reason', $this->cancelled_reason])
          ->andFilterWhere(['like', 've.created_on', $this->created_on])
          ->andFilterWhere(['like', 've.updated_on', $this->updated_on]);

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
        $query = VendorEarnings::find()
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
            'service_charge' => $this->service_charge,
            'order_id' => $this->order_id,
            'vendor_details_id' => $this->vendor_details_id,
            'order_sub_total' => $this->order_sub_total,
            'admin_commission_per' => $this->admin_commission_per,
            'admin_commission_amount' => $this->admin_commission_amount,
            'status' => $this->status,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'earnings_added_reason', $this->earnings_added_reason])
            ->andFilterWhere(['like', 'cancelled_reason', $this->cancelled_reason])
            ->andFilterWhere(['like', 'created_on', $this->created_on])
            ->andFilterWhere(['like', 'updated_on', $this->updated_on]);

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

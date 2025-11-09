<?php

namespace app\modules\admin\models\search;

use app\models\User;
use app\modules\admin\models\base\VendorDetails;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Coupon;

/**
 * app\modules\admin\models\search\CouponSearch represents the model behind the search form about `app\modules\admin\models\Coupon`.
 */
 class CouponSearch extends Coupon
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'min_cart', 'max_use', 'max_use_of_coupon', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['name', 'description', 'code', 'discount', 'max_discount', 'start_date', 'end_date', 'is_global', 'created_on', 'updated_on'], 'safe'],
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
    $query = Coupon::find();

    // If the logged-in user is a vendor, filter by their vendor_details_id
    if (User::isVendor()) {
        $user_id = Yii::$app->user->identity->id;

      
        $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
        $vendor_details_id = $vendorDetails->id ?? null;

        if ($vendor_details_id !== null) {
        
            $query->innerJoinWith('couponVendors as cv')
                  ->where(['cv.vendor_details_id' => $vendor_details_id]);
        } else {
          
            $query->where('0=1');
        }
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

    $query->andFilterWhere([
        'id' => $this->id,
        'min_cart' => $this->min_cart,
        'max_use' => $this->max_use,
        'max_use_of_coupon' => $this->max_use_of_coupon,
        'start_date' => $this->start_date,
        'end_date' => $this->end_date,
        'status' => $this->status,
        'created_on' => $this->created_on,
        'updated_on' => $this->updated_on,
        'create_user_id' => $this->create_user_id,
        'update_user_id' => $this->update_user_id,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
          ->andFilterWhere(['like', 'description', $this->description])
          ->andFilterWhere(['like', 'code', $this->code])
          ->andFilterWhere(['like', 'discount', $this->discount])
          ->andFilterWhere(['like', 'max_discount', $this->max_discount])
          ->andFilterWhere(['like', 'is_global', $this->is_global]);

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
        $query = Coupon::find()
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
            'min_cart' => $this->min_cart,
            'max_use' => $this->max_use,
            'max_use_of_coupon' => $this->max_use_of_coupon,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'discount', $this->discount])
            ->andFilterWhere(['like', 'max_discount', $this->max_discount])
            ->andFilterWhere(['like', 'is_global', $this->is_global]);

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

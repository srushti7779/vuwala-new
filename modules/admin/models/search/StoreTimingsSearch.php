<?php

namespace app\modules\admin\models\search;

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\StoreTimings;

/**
 * app\modules\admin\models\search\StoreTimingsSearch represents the model behind the search form about `app\modules\admin\models\StoreTimings`.
 */
 class StoreTimingsSearch extends StoreTimings
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'vendor_details_id', 'day_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['start_time', 'close_time', 'created_on', 'updated_on'], 'safe'],
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
    $query = StoreTimings::find();

   if (User::isVendor()) {
        $user_id = Yii::$app->user->identity->id;
        $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);

        if ($vendorDetails !== null) {
            $vendor_details_id = $vendorDetails->id;
            $query->andWhere(['vendor_details_id' => $vendor_details_id]);
        } else {
            // No vendor details found, return no results
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
        'vendor_details_id' => $this->vendor_details_id,
        'day_id' => $this->day_id,
        'start_time' => $this->start_time,
        'close_time' => $this->close_time,
        'status' => $this->status,
        'created_on' => $this->created_on,
        'updated_on' => $this->updated_on,
        'create_user_id' => $this->create_user_id,
        'update_user_id' => $this->update_user_id,
    ]);

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
        $query = StoreTimings::find()
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
            'day_id' => $this->day_id,
            'start_time' => $this->start_time,
            'close_time' => $this->close_time,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
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

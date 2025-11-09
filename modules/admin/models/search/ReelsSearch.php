<?php

namespace app\modules\admin\models\search;

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Reels;

/**
 * app\modules\admin\models\search\ReelsSearch represents the model behind the search form about `app\modules\admin\models\Reels`.
 */
 class ReelsSearch extends Reels
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'vendor_details_id','like_count', 'view_count', 'share_count', 'status', 'create_user_id', 'update_user_id', 'created_on', 'updated_on'], 'integer'],
            [['video', 'thumbnail', 'title', 'description'], 'safe'],
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
    $query = Reels::find();

    // Restrict to vendor's own reels if logged-in user is a vendor
    if (User::isVendor()) {
        $vendor = VendorDetails::findOne(['user_id' => Yii::$app->user->identity->id]);

        if ($vendor) {
            // Only show reels belonging to this vendor
            $query->andWhere(['vendor_details_id' => $vendor->id]);
        } else {
            // If vendor details are missing, return no results
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
        'like_count' => $this->like_count,
        'view_count' => $this->view_count,
        'share_count' => $this->share_count,
        'status' => $this->status,
        'create_user_id' => $this->create_user_id,
        'update_user_id' => $this->update_user_id,
        'created_on' => $this->created_on,
        'updated_on' => $this->updated_on,
    ]);

    $query->andFilterWhere(['like', 'video', $this->video])
        ->andFilterWhere(['like', 'thumbnail', $this->thumbnail])
        ->andFilterWhere(['like', 'title', $this->title])
        ->andFilterWhere(['like', 'description', $this->description]);

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
        $query = Reels::find()
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
            'like_count' => $this->like_count,
            'view_count' => $this->view_count,
            'share_count' => $this->share_count,
            'status' => $this->status,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'video', $this->video])
            ->andFilterWhere(['like', 'thumbnail', $this->thumbnail])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description]);

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

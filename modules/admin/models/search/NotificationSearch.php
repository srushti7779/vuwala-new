<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Notification;

/**
 * app\modules\admin\models\search\NotificationSearch represents the model behind the search form about `app\modules\admin\models\Notification`.
 */
 class NotificationSearch extends Notification
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'order_id', 'created_user_id', 'check_on_ajax', 'is_deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['title', 'module', 'icon', 'created_date', 'mark_read', 'status', 'model_type', 'info_delete', 'created_on', 'updated_on'], 'safe'],
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
        $query = Notification::find();

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
            'order_id' => $this->order_id,
            'created_user_id' => $this->created_user_id,
            'created_date' => $this->created_date,
            'check_on_ajax' => $this->check_on_ajax,
            'is_deleted' => $this->is_deleted,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'module', $this->module])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'mark_read', $this->mark_read])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'model_type', $this->model_type])
            ->andFilterWhere(['like', 'info_delete', $this->info_delete]);

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
        $query = Notification::find()
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
            'order_id' => $this->order_id,
            'created_user_id' => $this->created_user_id,
            'created_date' => $this->created_date,
            'check_on_ajax' => $this->check_on_ajax,
            'is_deleted' => $this->is_deleted,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'module', $this->module])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'mark_read', $this->mark_read])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'model_type', $this->model_type])
            ->andFilterWhere(['like', 'info_delete', $this->info_delete]);

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

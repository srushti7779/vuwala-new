<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\AisensyBulkMessageLog;

/**
 * app\modules\admin\models\search\AisensyBulkMessageLogSearch represents the model behind the search form about `app\modules\admin\models\AisensyBulkMessageLog`.
 */
 class AisensyBulkMessageLogSearch extends AisensyBulkMessageLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'campaign_id', 'template_id'], 'integer'],
            [['contact_number', 'message_id', 'status', 'skip_reason', 'sent_datetime', 'delivered_datetime', 'read_datetime', 'error_message', 'response_data', 'created_on', 'updated_on'], 'safe'],
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
        $query = AisensyBulkMessageLog::find()->where(['status'=>AisensyBulkMessageLog::STATUS_ACTIVE])->orWhere(['status'=>AisensyBulkMessageLog::STATUS_INACTIVE]);

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
            'campaign_id' => $this->campaign_id,
            'template_id' => $this->template_id,
            'sent_datetime' => $this->sent_datetime,
            'delivered_datetime' => $this->delivered_datetime,
            'read_datetime' => $this->read_datetime,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'contact_number', $this->contact_number])
            ->andFilterWhere(['like', 'message_id', $this->message_id])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'skip_reason', $this->skip_reason])
            ->andFilterWhere(['like', 'error_message', $this->error_message])
            ->andFilterWhere(['like', 'response_data', $this->response_data]);

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
        $query = AisensyBulkMessageLog::find()
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
            'campaign_id' => $this->campaign_id,
            'template_id' => $this->template_id,
            'sent_datetime' => $this->sent_datetime,
            'delivered_datetime' => $this->delivered_datetime,
            'read_datetime' => $this->read_datetime,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'contact_number', $this->contact_number])
            ->andFilterWhere(['like', 'message_id', $this->message_id])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'skip_reason', $this->skip_reason])
            ->andFilterWhere(['like', 'error_message', $this->error_message])
            ->andFilterWhere(['like', 'response_data', $this->response_data]);

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

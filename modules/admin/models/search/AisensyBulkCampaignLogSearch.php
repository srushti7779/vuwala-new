<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\AisensyBulkCampaignLog;

/**
 * app\modules\admin\models\search\AisensyBulkCampaignLogSearch represents the model behind the search form about `app\modules\admin\models\AisensyBulkCampaignLog`.
 */
 class AisensyBulkCampaignLogSearch extends AisensyBulkCampaignLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'total_contacts', 'sent_count', 'delivered_count', 'failed_count', 'skipped_count', 'create_user_id', 'update_user_id'], 'integer'],
            [['campaign_name', 'campaign_status', 'started_at', 'completed_at', 'created_on', 'updated_on'], 'safe'],
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
        $query = AisensyBulkCampaignLog::find()->where(['status'=>AisensyBulkCampaignLog::STATUS_ACTIVE])->orWhere(['status'=>AisensyBulkCampaignLog::STATUS_INACTIVE]);

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
            'template_id' => $this->template_id,
            'total_contacts' => $this->total_contacts,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'skipped_count' => $this->skipped_count,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'campaign_name', $this->campaign_name])
            ->andFilterWhere(['like', 'campaign_status', $this->campaign_status]);

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
        $query = AisensyBulkCampaignLog::find()
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
            'template_id' => $this->template_id,
            'total_contacts' => $this->total_contacts,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'skipped_count' => $this->skipped_count,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'campaign_name', $this->campaign_name])
            ->andFilterWhere(['like', 'campaign_status', $this->campaign_status]);

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

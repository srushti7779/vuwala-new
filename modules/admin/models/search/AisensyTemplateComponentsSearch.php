<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\AisensyTemplateComponents;

/**
 * app\modules\admin\models\search\AisensyTemplateComponentsSearch represents the model behind the search form about `app\modules\admin\models\AisensyTemplateComponents`.
 */
 class AisensyTemplateComponentsSearch extends AisensyTemplateComponents
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'component_index'], 'integer'],
            [['type', 'format', 'text', 'example', 'buttons', 'raw', 'created_on', 'updated_on'], 'safe'],
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
        $query = AisensyTemplateComponents::find()->where(['status'=>AisensyTemplateComponents::STATUS_ACTIVE])->orWhere(['status'=>AisensyTemplateComponents::STATUS_INACTIVE]);

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
            'component_index' => $this->component_index,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'format', $this->format])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'example', $this->example])
            ->andFilterWhere(['like', 'buttons', $this->buttons])
            ->andFilterWhere(['like', 'raw', $this->raw]);

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
        $query = AisensyTemplateComponents::find()
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
            'component_index' => $this->component_index,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'format', $this->format])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'example', $this->example])
            ->andFilterWhere(['like', 'buttons', $this->buttons])
            ->andFilterWhere(['like', 'raw', $this->raw]);

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

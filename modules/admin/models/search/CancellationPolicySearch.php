<?php

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\CancellationPolicy;

/**
 * CancellationPolicySearch represents the model behind the search form of `app\modules\admin\models\CancellationPolicy`.
 */
class CancellationPolicySearch extends CancellationPolicy
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'hours_before', 'status', 'create_user_id', 'updated_by'], 'integer'],
            [['refundable_amount_percentage'], 'number'],
            [['update_user_id', 'updated_on'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = CancellationPolicy::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'hours_before' => $this->hours_before,
            'refundable_amount_percentage' => $this->refundable_amount_percentage,
            'status' => $this->status,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
            'updated_by' => $this->updated_by,
            'updated_on' => $this->updated_on,
        ]);

        return $dataProvider;
    }
}

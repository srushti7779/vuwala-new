<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\OrderComplaints;
use app\modules\admin\models\User;

/**
 * app\modules\admin\models\search\OrderComplaintsSearch represents the model behind the search form about `app\modules\admin\models\OrderComplaints`.
 */
 class OrderComplaintsSearch extends OrderComplaints
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'user_id', 'store_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['title', 'description', 'response', 'created_on', 'updated_on'], 'safe'],
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
    $query = OrderComplaints::find();

    // Filter for vendor login
    if (!Yii::$app->user->isGuest && User::isVendor()) {
        $vendorDetails = \app\modules\admin\models\VendorDetails::findOne(['user_id' => Yii::$app->user->id]);
        if ($vendorDetails) {
            $query->andWhere(['store_id' => $vendorDetails->id]);
        } else {
            // No complaints shown if vendorDetails not found
            $query->andWhere(['store_id' => 0]); // or any invalid ID
        }
    }

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    $query->andFilterWhere([
        'id' => $this->id,
        'order_id' => $this->order_id,
        'user_id' => $this->user_id,
        'store_id' => $this->store_id,
        'status' => $this->status,
    ]);

    $query->andFilterWhere(['like', 'title', $this->title])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'response', $this->response]);

    return $dataProvider;
}

}

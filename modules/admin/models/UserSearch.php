<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{
	public $first_name;
	public $status;
	public $business_name;
	public $location_name;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'status'], 'integer'],
			[['username', 'email', 'first_name','business_name', 'location_name', 'contact_no'], 'safe'],
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

public function search($params, $role = '', $user_id = null)
{
    if (!empty($role)) {
        $query = User::find()->where(['user_role' => $role]);
    } else {
        $query = User::find();
    }

    // Join with vendor details table via update_user_id
    $query->joinWith(['vendorDetails']);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'defaultOrder' => ['id' => SORT_DESC],
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    $query->andFilterWhere([
        'id'         => $this->id,
        'status'     => $this->status,
        'created_at' => $this->created_at,
    ]);

    $query->andFilterWhere(['like', 'username', $this->username])
        ->andFilterWhere(['like', 'email', $this->email])
        ->andFilterWhere(['like', 'first_name', $this->first_name])
        ->andFilterWhere(['like', 'contact_no', $this->contact_no])
        ->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
        ->andFilterWhere(['like', 'vendor_details.location_name', $this->location_name])
        ->andFilterWhere(['like', 'concat(first_name, " ", last_name)', $this->first_name]);

    return $dataProvider;
}





	public function usersearch($params, $user_id = null)
	{
		if ($user_id != null) {
			$query = User::find()->where(['referral_id' => $user_id]);
		} else {
			$query = User::find()->where(['!=', 'id', \Yii::$app->user->id]);
		}
		// add conditions that should always apply here

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'attributes' => [
					'id',
					'status',
					'username',
					'email',
					'created_at',
					'first_name' => [
						'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
						'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
					],
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		// grid filtering conditions
		$query->andFilterWhere([
			'id'         => $this->id,
			'status'     => $this->status,
			'created_at' => $this->created_at,
		]);

		$query->andFilterWhere(['like', 'username', $this->username])
			->andFilterWhere(['like', 'first_name', $this->first_name])
			->andFilterWhere(['like', 'email', $this->email])
			->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->first_name]);
		//$cmd =  $query->createCommand()->getRawSql();
		//var_dump($cmd); exit;

		return $dataProvider;
	}
}
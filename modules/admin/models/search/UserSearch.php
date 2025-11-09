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
	public $full_name;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'status'], 'integer'],
			[['username', 'email', 'full_name', 'contact_no', 'first_name'], 'safe'],
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

	public function search($params, $role = '', $user_id = null, $type = '')
	{
		if (!empty($role)) {
			
			if (!empty($type)) {
				
				$query = User::find()->Where(['user_role' => $role])->andWhere(['account_type' => $type]);
			
			}else{
			$query = User::find()->Where(['user_role' => $role]);
			}
		} else {
			$query = User::find();
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'attributes' => [
					'id',
					'status',
					'username',
					'email',
					'created_at',
					'contact_no',
					'full_name' => [
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
			->andFilterWhere(['like', 'email', $this->email])
			->andFilterWhere(['like', 'first_name', $this->first_name])
			->andFilterWhere(['like', 'contact_no', $this->contact_no])
			->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name]);


		return $dataProvider;
	}

	public function SubscribedUserSearch($params, $role = '', $user_id = null, $type = '')
	{
		if (!empty($role)) {

			$subUser = SubscriptionOrders::find()->all();
			foreach ($subUser as $sub) {

				$query = User::find()->Where(['user_role' => $role])->andWhere(['id' => $sub->user_id]);
			}
		} else {
			$query = User::find();
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
					'contact_no',
					'full_name' => [
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
			->andFilterWhere(['like', 'email', $this->email])
			->andFilterWhere(['like', 'first_name', $this->first_name])
			->andFilterWhere(['like', 'contact_no', $this->contact_no])
			->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name]);


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
					'full_name' => [
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
			->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name]);
		//$cmd =  $query->createCommand()->getRawSql();
		//var_dump($cmd); exit;

		return $dataProvider;
	}
}

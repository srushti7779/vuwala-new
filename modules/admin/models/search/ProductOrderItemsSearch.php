<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\ProductOrderItems;

/**
 * app\modules\admin\models\search\ProductOrderItemsSearch represents the model behind the search form about `app\modules\admin\models\ProductOrderItems`.
 */
 class ProductOrderItemsSearch extends ProductOrderItems
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'product_order_id', 'product_id', 'quantity', 'tax_percentage', 'tax_amount', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['units', 'created_on', 'updated_on'], 'safe'],
            [['mrp_price', 'selling_price', 'sub_total', 'total_with_tax'], 'number'],
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
        $query = ProductOrderItems::find()->where(['status'=>ProductOrderItems::STATUS_ACTIVE])->orWhere(['status'=>ProductOrderItems::STATUS_INACTIVE]);

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
            'product_order_id' => $this->product_order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'mrp_price' => $this->mrp_price,
            'selling_price' => $this->selling_price,
            'sub_total' => $this->sub_total,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'total_with_tax' => $this->total_with_tax,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'units', $this->units]);

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
        $query = ProductOrderItems::find()
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
            'product_order_id' => $this->product_order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'mrp_price' => $this->mrp_price,
            'selling_price' => $this->selling_price,
            'sub_total' => $this->sub_total,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'total_with_tax' => $this->total_with_tax,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'units', $this->units]);

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

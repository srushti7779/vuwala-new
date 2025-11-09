<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Products;

/**
 * app\modules\admin\models\search\ProductsSearch represents the model behind the search form about `app\modules\admin\models\Products`.
 */
 class ProductsSearch extends Products
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'vendor_details_id', 'sku_id', 'units_id', 'supplier_id', 'received_units_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['discount_allowed', 'batch_number', 'purchase_date', 'expire_date', 'invoice_number', 'created_on', 'updated_on'], 'safe'],
            [['minimum_stock', 'mrp_price', 'selling_price', 'units_received'], 'number'],
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
        $query = Products::find()->where(['status'=>Products::STATUS_ACTIVE])->orWhere(['status'=>Products::STATUS_INACTIVE]);

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
            'sku_id' => $this->sku_id,
            'minimum_stock' => $this->minimum_stock,
            'units_id' => $this->units_id,
            'supplier_id' => $this->supplier_id,
            'purchase_date' => $this->purchase_date,
            'mrp_price' => $this->mrp_price,
            'selling_price' => $this->selling_price,
            'expire_date' => $this->expire_date,
            'units_received' => $this->units_received,
            'received_units_id' => $this->received_units_id,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'discount_allowed', $this->discount_allowed])
            ->andFilterWhere(['like', 'batch_number', $this->batch_number])
            ->andFilterWhere(['like', 'invoice_number', $this->invoice_number]);

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
        $query = Products::find()
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
            'sku_id' => $this->sku_id,
            'minimum_stock' => $this->minimum_stock,
            'units_id' => $this->units_id,
            'supplier_id' => $this->supplier_id,
            'purchase_date' => $this->purchase_date,
            'mrp_price' => $this->mrp_price,
            'selling_price' => $this->selling_price,
            'expire_date' => $this->expire_date,
            'units_received' => $this->units_received,
            'received_units_id' => $this->received_units_id,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'discount_allowed', $this->discount_allowed])
            ->andFilterWhere(['like', 'batch_number', $this->batch_number])
            ->andFilterWhere(['like', 'invoice_number', $this->invoice_number]);

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

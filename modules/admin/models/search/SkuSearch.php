<?php

namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Sku;

/**
 * app\modules\admin\models\search\SkuSearch represents the model behind the search form about `app\modules\admin\models\Sku`.
 */
 class SkuSearch extends Sku
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'vendor_details_id', 'brand_id', 'category_id', 'service_type_id', 'store_service_type_id', 'product_type_id', 'uom_id', 'uom_id_re_order_level', 'min_quantity_need', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['sku_code', 'product_name', 'ean_code', 're_order_level_for_alerts', 'description', 'image', 'created_on', 'updated_on'], 'safe'],
            [['tax_rate'], 'number'],
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
        $query = Sku::find()->where(['status'=>Sku::STATUS_ACTIVE])->orWhere(['status'=>Sku::STATUS_INACTIVE]);

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
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'service_type_id' => $this->service_type_id,
            'store_service_type_id' => $this->store_service_type_id,
            'product_type_id' => $this->product_type_id,
            'uom_id' => $this->uom_id,
            'tax_rate' => $this->tax_rate,
            'uom_id_re_order_level' => $this->uom_id_re_order_level,
            'min_quantity_need' => $this->min_quantity_need,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'sku_code', $this->sku_code])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'ean_code', $this->ean_code])
            ->andFilterWhere(['like', 're_order_level_for_alerts', $this->re_order_level_for_alerts])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'image', $this->image]);

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
        $query = Sku::find()
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
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'service_type_id' => $this->service_type_id,
            'store_service_type_id' => $this->store_service_type_id,
            'product_type_id' => $this->product_type_id,
            'uom_id' => $this->uom_id,
            'tax_rate' => $this->tax_rate,
            'uom_id_re_order_level' => $this->uom_id_re_order_level,
            'min_quantity_need' => $this->min_quantity_need,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'sku_code', $this->sku_code])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'ean_code', $this->ean_code])
            ->andFilterWhere(['like', 're_order_level_for_alerts', $this->re_order_level_for_alerts])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'image', $this->image]);

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

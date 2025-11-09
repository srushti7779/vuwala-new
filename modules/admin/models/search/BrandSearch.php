<?php
namespace app\modules\admin\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Brand;

class BrandSearch extends Brand
{public function rules()
{
    return [
        [['id', 'is_global', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['brand_name', 'image'], 'string', 'max' => 255],
        [['created_on', 'updated_on'], 'safe'],
    ];
}


    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Brand::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'is_global' => $this->is_global,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'brand_name', $this->brand_name])
              ->andFilterWhere(['like', 'image', $this->image]);

        return $dataProvider;
    }
}

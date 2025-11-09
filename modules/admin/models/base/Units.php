<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "units".
 *
 * @property integer $id
 * @property string $unit_name
 * @property string $category
 * @property integer $status
 * @property string $uom_type
 * @property string $factor
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\ProductServices[] $productServices
 * @property \app\modules\admin\models\ProductServicesUsed[] $productServicesUseds
 * @property \app\modules\admin\models\Products[] $products
 * @property \app\modules\admin\models\Sku[] $skus
 * @property \app\modules\admin\models\UOMHierarchy[] $uOMHierarchies
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\WastageProducts[] $wastageProducts
 */
class Units extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'productServices',
            'productServicesUseds',
            'products',
            'skus',
            'uOMHierarchies',
            'updateUser',
            'createUser',
            'wastageProducts'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['unit_name'], 'required'],
            [['category', 'uom_type'], 'string'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['factor'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
            [['unit_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'units';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
        }elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }

    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',
           
        ];
    }

    public function getFeatureOptionsBadges()
    {
        if ($this->is_featured == self::IS_FEATURED) {
            return '<span class="badge badge-success">Featured</span>';
        } elseif ($this->is_featured == self::IS_NOT_FEATURED) {
            return '<span class="badge badge-danger">Not Featured</span>';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'unit_name' => Yii::t('app', 'Unit Name'),
            'category' => Yii::t('app', 'Category'),
            'status' => Yii::t('app', 'Status'),
            'uom_type' => Yii::t('app', 'Uom Type'),
            'factor' => Yii::t('app', 'Factor'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductServices()
    {
        return $this->hasMany(\app\modules\admin\models\ProductServices::className(), ['uom_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductServicesUseds()
    {
        return $this->hasMany(\app\modules\admin\models\ProductServicesUsed::className(), ['uom_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\modules\admin\models\Products::className(), ['received_units_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSkus()
    {
        return $this->hasMany(\app\modules\admin\models\Sku::className(), ['uom_id_re_order_level' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUOMHierarchies()
    {
        return $this->hasMany(\app\modules\admin\models\UOMHierarchy::className(), ['of_units_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWastageProducts()
    {
        return $this->hasMany(\app\modules\admin\models\WastageProducts::className(), ['uom_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value' => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\UnitsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\UnitsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['unit_name'] =  $this->unit_name;
        
                $data['category'] =  $this->category;
        
                $data['status'] =  $this->status;
        
                $data['uom_type'] =  $this->uom_type;
        
                $data['factor'] =  $this->factor;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



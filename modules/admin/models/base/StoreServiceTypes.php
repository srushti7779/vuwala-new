<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "store_service_types".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $service_type_id
 * @property integer $main_category_id
 * @property string $type
 * @property integer $image
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\ServiceType $serviceType
 * @property \app\modules\admin\models\MainCategory $mainCategory
 * @property \app\modules\admin\models\VendorDetails $store
 */
class StoreServiceTypes extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'createUser',
            'updateUser',
            'serviceType',
            'mainCategory',
            'store'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'service_type_id', 'main_category_id', 'type'], 'required'],
            [['store_id', 'service_type_id', 'main_category_id', 'image', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_service_types';
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
            'store_id' => Yii::t('app', 'Store ID'),
            'service_type_id' => Yii::t('app', 'Service Type ID'),
            'main_category_id' => Yii::t('app', 'Main Category ID'),
            'type' => Yii::t('app', 'Type'),
            'image' => Yii::t('app', 'Image'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
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
    public function getUpdateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(\app\modules\admin\models\ServiceType::className(), ['id' => 'service_type_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainCategory()
    {
        return $this->hasOne(\app\modules\admin\models\MainCategory::className(), ['id' => 'main_category_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'store_id']);
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
                'value' => new \yii\db\Expression('NOW()'),
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
     * @return \app\modules\admin\models\StoreServiceTypesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\StoreServiceTypesQuery(get_called_class());
    }
public function asJson(){
                $data = [] ; 
                $data['id'] =  $this->id;
                $data['store_service_type_id'] =  $this->id;

                  $data['store_id'] =  $this->store_id;
        
                $data['service_type_id'] =  $this->service_type_id;
        
                $data['main_category_id'] =  $this->main_category_id;
        
                $data['type'] =  $this->type;
        
                $data['image'] =  $this->serviceType->image;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}









public function getSubCategoryCount() {
    try {
        return Services::find()
            ->where(['store_service_type_id' => $this->id])
            ->andWhere(['vendor_details_id' => $this->store_id])
            ->andWhere([
                'or',
                ['parent_id' => 0],
                ['parent_id' => null],
                ['parent_id' => '']
            ])
            ->count();
    } catch (\Throwable $e) {
        Yii::error("Failed to get subcategory count: " . $e->getMessage(), __METHOD__);
        return 0; // return default safe value
    }
}
 


public function asJsonVendor(){
    $data = [] ; 
            $data['id'] =  $this->id;
            $data['store_service_type_id'] =  $this->id;
            $data['store_id'] =  $this->store_id;
        
                $data['service_type_id'] =  $this->service_type_id;
        
                $data['main_category_id'] =  $this->main_category_id;
        
                $data['type'] =  $this->type;
        
                $data['image'] =  $this->image;

                $data['services_count'] =  $this->getSubCategoryCount();

        
                $data['status'] =  $this->status;
                $sub_category = SubCategory::find()->where(['main_category_id' => $this->main_category_id])
                ->andWhere(['vendor_details_id' => $this->store_id])
                ->andWhere(['service_type_id' => $this->service_type_id])
                ->all();
                if(!empty($sub_category)){
                    foreach($sub_category as $sub){
                        $data['sub_category'][] = $sub->asJsonVendorStoreService();
                    }
                }else{
                    $data['sub_category'] = [];
                }



        
        
            return $data;
}


}



<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "main_category".
 *
 * @property integer $id
 * @property string $title
 * @property string $image
 * @property integer $is_featured
 * @property integer $offer_percentage
 * @property integer $is_required_documents
 * @property integer $status
 * @property integer $show_home
 * @property integer $sortOrder
 * @property integer $position
 * @property integer $type_id
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\SubCategory[] $subCategories
 * @property \app\modules\admin\models\VendorDetails[] $vendorDetails
 */
class MainCategory extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'subCategories',
            'vendorDetails'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const IS_PREMIUM = 1;
    const IS_NOT_PREMIUM = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['is_featured', 'offer_percentage', 'status', 'sortOrder', 'position', 'type_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on','is_scheduled_next_visit'], 'safe'],
            [['title', 'image','icon'], 'string', 'max' => 512],
            [['is_required_documents', 'show_home'], 'string', 'max' => 1]
        ];
    }
 
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'main_category';
    }

    public function getStateOptions()
    {
        return [

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-default">In Active</span>';
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
            'title' => Yii::t('app', 'Title'),
            'image' => Yii::t('app', 'Image'),
            'is_featured' => Yii::t('app', 'Is Featured'),
            'offer_percentage' => Yii::t('app', 'Offer Percentage'),
            'is_required_documents' => Yii::t('app', 'Is Required Documents'),
            'status' => Yii::t('app', 'Status'),
            'show_home' => Yii::t('app', 'Show Home'),
            'sortOrder' => Yii::t('app', 'Sort Order'),
            'position' => Yii::t('app', 'Position'),
            'type_id' => Yii::t('app', 'Type ID'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategories()
    {
        return $this->hasMany(\app\modules\admin\models\SubCategory::className(), ['main_category_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasMany(\app\modules\admin\models\VendorDetails::className(), ['main_category_id' => 'id']);
    }
   // Inside VendorMainCategoryData.php
public function getMainCategory()
{
    return $this->hasOne(MainCategory::class, ['id' => 'main_category_id']);
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
     * @return \app\modules\admin\models\MainCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\MainCategoryQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['title'] =  $this->title;
        
                $data['image'] =  $this->image;

                $data['icon'] =  $this->icon;

        
                $data['is_featured'] =  $this->is_featured;
        
                $data['offer_percentage'] =  $this->offer_percentage;
        
                $data['is_required_documents'] =  $this->is_required_documents;

                $data['is_scheduled_next_visit'] =  $this->is_scheduled_next_visit; 

        
                $data['status'] =  $this->status;
        
                $data['show_home'] =  $this->show_home;
        
                $data['sortOrder'] =  $this->sortOrder;
        
                $data['position'] =  $this->position;
        
                $data['type_id'] =  $this->type_id;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



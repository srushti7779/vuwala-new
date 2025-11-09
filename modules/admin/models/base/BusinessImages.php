<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\models\User;
/**
 * This is the base model class for table "business_images".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $image_file
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 */
class BusinessImages extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'vendorDetails'
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
        [['vendor_details_id', 'status'], 'required'],
        [['vendor_details_id', 'status', 'create_user_id', 'update_user_id'], 'default', 'value' => null],
        [['id', 'vendor_details_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['created_on', 'updated_on'], 'safe'],

        // File validation for uploaded images
        [['image_file'], 'file',
            'skipOnEmpty' => true,
            'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
            'maxSize' => 4 * 1024 * 1024, // 4MB
            'tooBig' => 'The image file is too large. Maximum allowed size is 4MB.',
            'maxFiles' => 10,
        ],

        // Allow DB string for existing images
        [['image_file'], 'safe'], 
    ];
}



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_images';
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
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'image_file' => Yii::t('app', 'Image File'),
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
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

    
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\BusinessImagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BusinessImagesQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['image_file'] =  $this->image_file;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



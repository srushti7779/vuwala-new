<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "temporary_users".
 *
 * @property integer $id
 * @property string $username
 * @property string $contact_no
 * @property string $unique_user_id
 * @property string $first_name
 * @property string $email
 * @property string $device_token
 * @property string $device_type
 * @property string $user_role
 * @property integer $status
 * @property string $referral_code
 * @property string $vendor_store_type
 * @property string $brand_name
 * @property string $brand_logo
 * @property integer $is_featured
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 */
class TemporaryUsers extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            ''
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
            [['username', 'contact_no', 'first_name', 'email', 'user_role'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['username', 'first_name', 'email', 'device_token', 'brand_name', 'brand_logo'], 'string', 'max' => 255],
            [['contact_no'], 'string', 'max' => 20],
            [['unique_user_id', 'device_type', 'user_role', 'referral_code', 'vendor_store_type'], 'string', 'max' => 50],
            [['is_featured'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'temporary_users';
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
            'username' => Yii::t('app', 'Username'),
            'contact_no' => Yii::t('app', 'Contact No'),
            'unique_user_id' => Yii::t('app', 'Unique User ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'email' => Yii::t('app', 'Email'),
            'device_token' => Yii::t('app', 'Device Token'),
            'device_type' => Yii::t('app', 'Device Type'),
            'user_role' => Yii::t('app', 'User Role'),
            'status' => Yii::t('app', 'Status'),
            'referral_code' => Yii::t('app', 'Referral Code'),
            'vendor_store_type' => Yii::t('app', 'Vendor Store Type'),
            'brand_name' => Yii::t('app', 'Brand Name'),
            'brand_logo' => Yii::t('app', 'Brand Logo'),
            'is_featured' => Yii::t('app', 'Is Featured'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
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
     * @return \app\modules\admin\models\TemporaryUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\TemporaryUsersQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['temporary_user_id'] =  $this->id;
        
                $data['username'] =  $this->username;
        
                $data['contact_no'] =  $this->contact_no;
        
                $data['unique_user_id'] =  $this->unique_user_id;
        
                $data['first_name'] =  $this->first_name;
        
                $data['email'] =  $this->email;
        
                $data['device_token'] =  $this->device_token;
        
                $data['device_type'] =  $this->device_type;
        
                $data['user_role'] =  $this->user_role;
        
                $data['status'] =  $this->status;
        
                $data['referral_code'] =  $this->referral_code;
        
                $data['vendor_store_type'] =  $this->vendor_store_type;
        
                $data['brand_name'] =  $this->brand_name;
        
                $data['brand_logo'] =  $this->brand_logo;
        
                $data['is_featured'] =  $this->is_featured;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



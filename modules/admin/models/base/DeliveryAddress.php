<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior; 
/**
 * This is the base model class for table "delivery_address".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $address
 * @property string $location
 * @property string $latitude
 * @property string $longitude
 * @property string $address_label
 * @property string $land_mark
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property string $contact_no
 * @property string $contact_name
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $pincode
 *
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class DeliveryAddress extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'user',
            'createUser',
            'updateUser'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2; 

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const IS_DELETED_YES = 1; 
    const IS_DELETED_NO = 0;       

    
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'address', 'location', 'latitude', 'longitude', 'address_label','pincode'], 'required'],
            [['user_id', 'status', 'create_user_id', 'update_user_id','is_deleted'], 'integer'], 
            [['created_on', 'updated_on','is_deleted'], 'safe'], 
            [['address', 'location', 'latitude', 'longitude', 'address_label', 'land_mark', 'contact_no', 'contact_name','pincode','floor_number'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery_address';
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
            'user_id' => Yii::t('app', 'User ID'),
            'address' => Yii::t('app', 'Address'),
            'location' => Yii::t('app', 'Location'),
            'latitude' => Yii::t('app', 'Latitude'),
            'longitude' => Yii::t('app', 'Longitude'),
            'address_label' => Yii::t('app', 'Address Label'),
            'land_mark' => Yii::t('app', 'Land Mark'),
            'pincode' => Yii::t('app', 'Pin Code'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'contact_no' => Yii::t('app', 'Contact No'),
            'contact_name' => Yii::t('app', 'Contact Name'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
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
     * @return \app\modules\admin\models\DeliveryAddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\DeliveryAddressQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['address_id'] =  $this->id;
        
                $data['user_id'] =  $this->user_id;
        
                $data['address'] =  $this->address;
        
                $data['location'] =  $this->location;

                $data['floor_number'] =  $this->floor_number;

        
                $data['latitude'] =  $this->latitude;
        
                $data['longitude'] =  $this->longitude;

                $data['pincode'] =  $this->pincode; 
        
                $data['address_label'] =  $this->address_label;
        
                $data['land_mark'] =  $this->land_mark;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['contact_no'] =  $this->contact_no;
        
                $data['contact_name'] =  $this->contact_name;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



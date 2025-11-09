<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "staff".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $profile_image
 * @property string $mobile_no
 * @property string $full_name
 * @property string $email
 * @property integer $gender
 * @property string $dob
 * @property integer $role
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 */
class Staff extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'homeVisitorsHasOrders',
            'createUser',
            'updateUser',
            'vendorDetails',
            'user'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;



    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;


    const ROLE_HOME_VISITOR = 'home_visitor';
    const ROLE_STAFF = 'staff';


    const CURRENT_STATUS_IDLE = 1;
    const CURRENT_STATUS_BUSY = 2;

    static public function getRoles()
    {
        return [
            self::ROLE_HOME_VISITOR => 'home visitor',
            self::ROLE_STAFF => 'staff',
        ];
    }


    static public function getGenderOptions()
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
        ];
    }





    /**
     * @inheritdoc
     */
   public function rules()
{
    return [
        [['id', 'user_id', 'vendor_details_id', 'gender', 'current_status', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['profile_image', 'mobile_no', 'full_name', 'email', 'dob', 'experience', 'specialization', 'role', 'created_on', 'updated_on'], 'safe'],
        ['mobile_no', 'match', 'pattern' => '/^\d{10}$/', 'message' => 'Mobile number must be exactly 10 digits.'],
        ['experience', 'integer', 'min' => 0, 'max' => 99],
    ];
}


    /** 
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'staff';
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
        } elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }
    }

     public function getCurrentStatusOptions()
    {
        return [

            self::CURRENT_STATUS_IDLE => 'idle',
            self::CURRENT_STATUS_BUSY => 'Busy',

        ];
    }

    public function getCurrentStatusBadges()
    {

        if ($this->current_status == self::CURRENT_STATUS_IDLE) {
            return '<span class="badge badge-success">Idle</span>';
        } elseif ($this->current_status == self::CURRENT_STATUS_BUSY) {
            return '<span class="badge badge-default">Busy</span>';
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

    
    public function getGenderOptionsBadges()
    {
        if ($this->gender == self::GENDER_MALE) {
            return '<span class="badge badge-success">Male</span>';
        } elseif ($this->gender == self::GENDER_FEMALE) {
            return '<span class="badge badge-danger">female</span>'; 
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
            'profile_image' => Yii::t('app', 'Profile Image'),
            'mobile_no' => Yii::t('app', 'Mobile No'),
            'full_name' => Yii::t('app', 'Full Name'),
            'email' => Yii::t('app', 'Email'),
            'gender' => Yii::t('app', 'Gender'),
            'dob' => Yii::t('app', 'Dob'),
            'role' => Yii::t('app', 'Role'),
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHomeVisitorsHasOrders()
    {
        return $this->hasMany(\app\modules\admin\models\HomeVisitorsHasOrders::className(), ['home_visitor_id' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
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
     * @return \app\modules\admin\models\StaffQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\StaffQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['staff_id'] = $this->id;
        $data['user_id'] = $this->user_id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['profile_image'] = $this->profile_image;
        $data['mobile_no'] = $this->mobile_no;
        $data['full_name'] = $this->full_name;
        $data['email'] = $this->email;
        $data['gender'] = $this->gender;
        $data['dob'] = $this->dob;
        $data['aadhaar_number'] = $this->aadhaar_number;

        $data['experience'] = $this->experience; 
        $data['specialization'] = $this->specialization;
        $data['rating'] = 4; // Static rating; replace with dynamic logic if needed.
    
        // Calculate total bookings using the relation
        $data['total_bookings'] = $this->getHomeVisitorsHasOrders()->count();
    
        $data['role'] = $this->role;
        $data['current_status'] = $this->current_status;
        $data['status'] = $this->status;
        $data['created_on'] = $this->created_on;
        $data['updated_on'] = $this->updated_on;
        $data['create_user_id'] = $this->create_user_id;
        $data['update_user_id'] = $this->update_user_id;
    
        return $data;
    }
    
}

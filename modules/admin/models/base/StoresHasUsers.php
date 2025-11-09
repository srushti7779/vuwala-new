<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "stores_has_users".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $guest_user_id
 * @property integer $vendor_user_id
 * @property integer $status
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $guestUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $vendorUser
 */
class StoresHasUsers extends \yii\db\ActiveRecord
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
            'guestUser',
            'vendorDetails',
            'vendorUser',
            'storesUsersMemberships'
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
            [['vendor_details_id', 'guest_user_id', 'vendor_user_id'], 'required'],
            [['vendor_details_id', 'guest_user_id', 'vendor_user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stores_has_users';
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
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'guest_user_id' => Yii::t('app', 'Guest User ID'),
            'vendor_user_id' => Yii::t('app', 'Vendor User ID'),
            'status' => Yii::t('app', 'Status'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
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
    public function getGuestUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'guest_user_id']);
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
    public function getVendorUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'vendor_user_id']);
    }
    
       public function getStoresUsersMemberships()
    {
        return $this->hasOne(\app\modules\admin\models\StoresUsersMemberships::className(), ['stores_has_users_id' => 'id']);
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
     * @return \app\modules\admin\models\StoresHasUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\StoresHasUsersQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['store_has_user_id'] =  $this->id;
            $data['user'] = $this->guestUser ? $this->guestUser->asJsonUserClient() : null;
            $data['total_spent'] = 0;
            $data['visits'] = 0;
            $data['deposit_balance'] = 0;
            $data['last_visit'] = date('Y-m-d H:i:s');
            $data['next_appointment'] = date('Y-m-d H:i:s');
            $data['is_vip'] = $this->is_vip;
            $data['storesUsersMemberships'] = $this->storesUsersMemberships ? $this->storesUsersMemberships->asJson() : null;
            return $data;
}


public function asJsonView(){
    $data = [] ; 
            $data['store_has_user_id'] =  $this->id;
            $data['total_spent'] = 0;
            $data['visits'] = 0;
            $data['deposit_balance'] = 0;
            $data['last_visit'] = date('Y-m-d H:i:s');
            $data['next_appointment'] = date('Y-m-d H:i:s');
            $data['is_vip'] = $this->is_vip;
            $data['storesUsersMemberships'] = $this->storesUsersMemberships ? $this->storesUsersMemberships->asJson() : null;
            return $data;
}





}



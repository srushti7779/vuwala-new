<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "vendor_subscriptions".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $subscription_id
 * @property double $amount
 * @property string $start_date
 * @property string $end_date
 * @property integer $status
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\Subscriptions $subscription
 */
class VendorSubscriptions extends \yii\db\ActiveRecord
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
            'vendorDetails',
            'subscription'
        ];
    } 
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_PENDING = 3;


    const PAYMENT_STATUS_PENDING = 1;
    const PAYMENT_STATUS_SUCCESS = 2;
    const PAYMENT_STATUS_FAILED = 3;





    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'subscription_id','amount'], 'required'],
            [['vendor_details_id', 'subscription_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['start_date', 'end_date', 'created_on', 'updated_on','duration'], 'safe']     
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_subscriptions';
    }

    public function getStateOptions()
    {
        return [

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_ACTIVE => 'Active',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-default">In Active</span>';
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
            'subscription_id' => Yii::t('app', 'Subscription ID'),
            'amount' => Yii::t('app', 'Amount'),
            'duration' => Yii::t('app', 'Duration'),   
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(\app\modules\admin\models\Subscriptions::className(), ['id' => 'subscription_id']);
    }

    
    // In the VendorDetails model
    public function getMainCategory()
    {
        return $this->hasOne(MainCategory::className(), ['id' => 'main_category_id']);
    }

 
    // In the VendorDetails model
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }


    public static function isActiveSubscription($vendor_details_id)
    {
        // Find the active subscription with an end_date in the future or today
        $activeSubscription = self::find()
            ->where(['vendor_details_id' => $vendor_details_id, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['>=', 'end_date', date('Y-m-d')])  // end_date is in the future or today
            ->one();
        if (!empty($activeSubscription)) {
            return true;
        } else {
            return false;
        }
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

    public static function isVendorSubscriptionActive($vendorId)
    {
        // Check if the vendor ID is valid
        if (empty($vendorId) || !is_numeric($vendorId)) {
            return false; // Invalid vendor ID, return false
        }

        // Find the vendor's subscription
        $subscription = self::find()
            ->where(['vendor_details_id' => $vendorId])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->andWhere(['<=', 'start_date', date('Y-m-d')])  // Ensure the start date has passed
            ->andWhere(['>=', 'end_date', date('Y-m-d')])    // Ensure the end date has not passed
            ->one();

        // Return false if no active subscription is found
        if ($subscription === null) {
            return false;
        }

        // Return true if an active subscription is found
        return true;
    }
    /**
     * @inheritdoc
     * @return \app\modules\admin\models\VendorSubscriptionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorSubscriptionsQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['vendor_subscriptions_id'] =  $this->id;
        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['subscription_id'] =  $this->subscription_id; 

        $data['amount'] =  $this->amount;

       
        $data['duration'] =  $this->duration;

        $data['start_date'] =  $this->start_date;

        $data['end_date'] =  $this->end_date;

        $data['status'] =  $this->status;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        return $data;
    }
}

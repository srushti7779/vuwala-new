<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "subscriptions".
 *
 * @property integer $id
 * @property integer $subscription_type 
 * @property string $title
 * @property string $description
 * @property string $image
 * @property double $price
 * @property double $offer_price
 * @property integer $validity_in_days
 * @property string $validity_in_text
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorSubscriptions $vendorSubscriptions
 */
class Subscriptions extends \yii\db\ActiveRecord
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
            'vendorSubscriptions'
        ];
    }

    const SUBSCRIPTION_TYPE_IS_BASIC = 1;
    const SUBSCRIPTION_TYPE_IS_PREMIUM = 2; 

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
            [['subscription_type', 'title', 'description', 'image', 'price', 'offer_price', 'validity_in_days', 'validity_in_text'], 'required'],
            [['description'], 'string'],
            [['price', 'offer_price'], 'number'],
            [['validity_in_days', 'status', 'create_user_id', 'update_user_id', 'subscription_type'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['title', 'image'], 'string', 'max' => 512],
            [['validity_in_text'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriptions';
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
        } elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }
    }

    public function getSubscriptionTypeOptions()
    {
        return [

            self::SUBSCRIPTION_TYPE_IS_BASIC => 'Basic',
            self::SUBSCRIPTION_TYPE_IS_PREMIUM => 'Premium', 

        ];
    }
    public function getSubscriptionTypeOptionsBadges()
    {

        if ($this->subscription_type == self::SUBSCRIPTION_TYPE_IS_BASIC) {
            return '<span class="badge badge-success">Basic</span>';
        } elseif ($this->subscription_type == self::SUBSCRIPTION_TYPE_IS_PREMIUM) {
            return '<span class="badge badge-default">premium</span>'; 
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
            'subscription_type' => Yii::t('app', 'Subscription Type'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'image' => Yii::t('app', 'Image'),
            'price' => Yii::t('app', 'Price'),
            'offer_price' => Yii::t('app', 'Offer Price'),
            'validity_in_days' => Yii::t('app', 'Validity In Days'),
            'validity_in_text' => Yii::t('app', 'Validity In Text'),
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
    // public function getVendorSubscriptions()
    // {
    //     return $this->hasOne(\app\modules\admin\models\VendorSubscriptions::className(), ['subscription_id' => 'id']);
    // }

    public function getVendorSubscriptions()
    {
        return $this->hasOne(\app\modules\admin\models\VendorSubscriptions::className(), ['subscription_id' => 'id']);
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
     * @return \app\modules\admin\models\SubscriptionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\SubscriptionsQuery(get_called_class());
    }
    public function asJson($vendorId = '')
    {
        $data = [];  
        $data['id'] =  $this->id;
        $data['subscription_type'] =  $this->subscription_type;
        $data['title'] =  $this->title;

        $data['description'] =  strip_tags($this->description);

        $data['image'] =  $this->image;

        $data['price'] =  $this->price;

        $data['offer_price'] =  $this->offer_price;

        $data['validity_in_days'] =  $this->validity_in_days;

        $data['validity_in_text'] =  $this->validity_in_text;


        $data['IsSubscriptionsActive'] = VendorSubscriptions::isVendorSubscriptionActive($vendorId);

        $isActive = VendorSubscriptions::isVendorSubscriptionActive($vendorId);
        if ($isActive) {
            $data['IsSubscriptionsDetails']  = $this->vendorSubscriptions->asJson();
        } else {
            $data['IsSubscriptionsDetails'] = '';
        }


        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        return $data;
    }
}

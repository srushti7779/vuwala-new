<?php
namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "service_has_coupons".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $coupon_id
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\Services $service
 * @property \app\modules\admin\models\Coupon $coupon
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 */
class ServiceHasCoupons extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'service',
            'coupon',
            'updateUser',
            'createUser',
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_DELETE   = 2;

    const IS_FEATURED     = 1;
    const IS_NOT_FEATURED = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'coupon_id'], 'required'],
            [['service_id', 'coupon_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_has_coupons';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE   => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE   => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
        } elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }

    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED     => 'Is Featured',
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
            'id'             => Yii::t('app', 'ID'),
            'service_id'     => Yii::t('app', 'Service ID'),
            'coupon_id'      => Yii::t('app', 'Coupon ID'),
            'status'         => Yii::t('app', 'Status'),
            'created_on'     => Yii::t('app', 'Created On'),
            'updated_on'     => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(\app\modules\admin\models\Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(\app\modules\admin\models\Coupon::className(), ['id' => 'coupon_id']);
    }
  public function getVendorDetails()
{
    return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id'])
        ->via('services'); // link through services table
}

    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
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
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value'              => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class'              => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\ServiceHasCouponsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ServiceHasCouponsQuery(get_called_class());
    }
    public function asJson()
    {
        $data       = [];
        $data['id'] = $this->id;

        $data['service_id'] = $this->service_id;

        $data['coupon_id'] = $this->coupon_id;

        $data['service'] = $this->service ? $this->service->service_name : null;

        $data['status'] = $this->status;



        return $data;
    }

}

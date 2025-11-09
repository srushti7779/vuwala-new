<?php
namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "combo_packages_cart".
 *
 * @property integer $id
 * @property integer $combo_package_id
 * @property integer $user_id
 * @property double $amount
 * @property integer $status
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\ComboPackages $comboPackage
 */
class ComboPackagesCart extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'updateUser',
            'createUser',
            'user',
            'comboPackage',
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
            [['combo_package_id', 'user_id', 'amount'], 'required'],
            [['combo_package_id', 'user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['amount'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'combo_packages_cart';
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
            'id'               => Yii::t('app', 'ID'),
            'combo_package_id' => Yii::t('app', 'Combo Package ID'),
            'user_id'          => Yii::t('app', 'User ID'),
            'amount'           => Yii::t('app', 'Amount'),
            'status'           => Yii::t('app', 'Status'),
            'create_user_id'   => Yii::t('app', 'Create User ID'),
            'update_user_id'   => Yii::t('app', 'Update User ID'),
            'created_on'       => Yii::t('app', 'Created On'),
            'updated_on'       => Yii::t('app', 'Updated On'),
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComboPackage()
    {
        return $this->hasOne(\app\modules\admin\models\ComboPackages::className(), ['id' => 'combo_package_id']);
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
                'value'              => new \yii\db\Expression('NOW()'),
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
     * @return \app\modules\admin\models\ComboPackagesCartQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ComboPackagesCartQuery(get_called_class());
    }

    public function asJson()
    {
        $data       = [];
        $data['id'] = $this->id;

        $data['combo_package_id'] = $this->combo_package_id;

        $data['user_id'] = $this->user_id;

        $data['amount'] = $this->amount;

        $data['status'] = $this->status;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        return $data;
    }

    public function asJsonCart()
    {
        $data                         = [];
        $data['id']                   = $this->id;
        $data['combo_package_id']     = $this->combo_package_id;
        $data['user_id']              = $this->user_id;
        $data['amount']               = $this->amount;
        $data['combo_package_title']  = $this->comboPackage->title ?? null;
        $data['combo_discount_price'] = $this->comboPackage->discount_price ?? null;
        $data['combo_price']          = $this->comboPackage->price ?? null;
        $data['time']                 = $this->comboPackage->time ?? null;
        $data['is_home_visit']        = $this->comboPackage->is_home_visit ?? null;
        $data['is_walk_in']           = $this->comboPackage->is_walk_in ?? null;
        $data['service_for']          = $this->comboPackage->service_for ?? null;
        $data['description']          = $this->comboPackage->description ?? null;
        if (! empty($this->comboPackage->comboServices)) {
            foreach ($this->comboPackage->comboServices as $comboService) {
                $data['comboServices'][] = [
                    'combo_service_id' => $comboService->id,
                    'service_id'       => $comboService->services_id,
                    'service_name'     => $comboService->services->service_name ?? null,
                    'status'           => $comboService->status ?? null,
                    'created_on'       => $comboService->created_on ?? null,
                    'updated_on'       => $comboService->updated_on ?? null,
                ];
            }
        } else {
            $data['comboServices'] = [];
        }

        return $data;
    }
}

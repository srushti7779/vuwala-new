<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\modules\admin\models\User;
use app\modules\admin\models\Sku;
use app\modules\admin\models\Units;

class Hierarchy extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static function tableName()
    {
        return 'u_o_m_hierarchy';
    }



    public function rules()
    {
        return [
            [['sku_id', 'units_id', 'quantity', 'of_units_id'], 'required'],
            [['sku_id', 'units_id', 'quantity', 'of_units_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value' => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku_id' => Yii::t('app', 'SKU'),
            'units_id' => Yii::t('app', 'Units'),
            'quantity' => Yii::t('app', 'Quantity'),
            'of_units_id' => Yii::t('app', 'Of Units'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }

    public function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function asJson()
    {
        return [
            'id' => $this->id,
            'sku_id' => $this->sku_id,
            'units_id' => $this->units_id,
            'quantity' => $this->quantity,
            'of_units_id' => $this->of_units_id,
            'status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ];
    }

    public static function find()
    {
        return parent::find();
    }

    // Relations
    public function getSku()
    {
        return $this->hasOne(Sku::class, ['id' => 'sku_id']);
    }

    public function getUnit()
{
    return $this->hasOne(Units::class, ['id' => 'units_id']);
}


    public function getOfUnit()
    {
        return $this->hasOne(Units::class, ['id' => 'of_units_id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'create_user_id']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'update_user_id']);
    }

    // Aliases to match expected property access
    public function getCreateUser()
    {
        return $this->getCreatedBy();
    }

    public function getUpdateUser()
    {
        return $this->getUpdatedBy();
    }
}

<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cancellation_policys".
 */
class CancellationPolicy extends ActiveRecord
{
    // ✅ Constants for status
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function tableName()
    {
        return 'cancellation_policies';
    }

public function rules()
{
    return [
        [['hours_before', 'refundable_amount_percentage'], 'required'],
       [['refundable_amount_percentage'], 'number', 'min' => 0, 'max' => 100],
        [['hours_before', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['updated_by', 'updated_on'], 'safe'],
    ];
}

public function attributeLabels()
{
    return [
        'id' => 'ID',
        'hours_before' => 'Hours Before',
        'refundable_amount_percentage' => 'Refundable Amount %',
        'status' => 'Status',
        'create_user_id' => 'Created By',
        'created_on' => 'Created On',
        'updated_by' => 'Updated By',
        'updated_on' => 'Updated On',
    ];
}

    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

  public function getStatusName()
{
    $list = self::getStatusList();
    return $list[$this->status] ?? 'Unknown';
}
public function getCreatedByUser()
{
    return $this->hasOne(\app\models\User::className(), ['id' => 'created_by']);
}


}

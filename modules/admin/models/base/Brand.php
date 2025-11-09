<?php

namespace app\modules\admin\models\base;

use app\modules\admin\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class Brand extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    public static function tableName()
    {
        return 'brands';
    }

public $imageFile;

public function rules()
{
    return [
        [['id', 'is_global', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['brand_name', 'image', 'created_on', 'updated_on'], 'safe'],
        [['imageFile'], 'file', 'extensions' => 'jpg, png, jpeg', 'skipOnEmpty' => true],
    ];
}



    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'brand_name' => Yii::t('app', 'Brand Name'),
            'image' => Yii::t('app', 'Image'),
            'is_global' => Yii::t('app', 'Is Global'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Created By'),
            'update_user_id' => Yii::t('app', 'Updated By'),
        ];
    }
     public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }

  public function getCreateUser()
{
    return $this->hasOne(User::class, ['id' => 'create_user_id']);
}
public function getUpdateUser()
{
    return $this->hasOne(User::class, ['id' => 'update_user_id']);
}



}



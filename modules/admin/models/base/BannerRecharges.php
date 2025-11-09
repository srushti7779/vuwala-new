<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "banner_recharges".
 *
 * @property string $id
 * @property string $vendor_id
 * @property string $banner_id
 * @property string $amount
 * @property integer $status
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\VendorDetails $vendor
 * @property \app\modules\admin\models\Banner $banner
 */
class BannerRecharges extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    /**
     * This function helps \mootensai\relation\RelationTrait run faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'vendor',
            'banner',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_recharges';
    }

    /**
     * @inheritdoc
     */
 public function rules()
{
    return [
        [['vendor_id', 'banner_id','amount'], 'required'], 
        [['vendor_id', 'banner_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['amount'], 'number'],
        [['created_on', 'updated_on'], 'safe'],
    ];
}


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'banner_id' => Yii::t('app', 'Banner ID'),
            'amount' => Yii::t('app', 'Amount'),
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
    public function getVendor()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBanner()
    {
        return $this->hasOne(\app\modules\admin\models\Banner::className(), ['id' => 'banner_id']);
    }

    /**
     * @inheritdoc
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
     * @return \app\modules\admin\models\BannerRechargesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BannerRechargesQuery(get_called_class());
    }
}

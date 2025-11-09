<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "banner_charge_logs".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $banner_id
 * @property string $action
 * @property string $charge_amount
 * @property string $ip_address
 * @property string $performed_at
 * @property string $user_agent
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $user
 */
class BannerChargeLogs extends \yii\db\ActiveRecord
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
            'user'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'banner_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['banner_id', 'action', 'user_agent'], 'required'],
            [['action', 'user_agent'], 'string'],
            [['charge_amount'], 'number'],
            [['performed_at', 'created_on', 'updated_on'], 'safe'],
            [['ip_address'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_charge_logs';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'banner_id' => Yii::t('app', 'Banner ID'),
            'action' => Yii::t('app', 'Action'),
            'charge_amount' => Yii::t('app', 'Charge Amount'),
            'ip_address' => Yii::t('app', 'Ip Address'),
            'performed_at' => Yii::t('app', 'Performed At'),
            'user_agent' => Yii::t('app', 'User Agent'),
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
    public function getBanner()
{
    return $this->hasOne(\app\modules\admin\models\Banner::className(), ['id' => 'banner_id']);
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
     * @return \app\modules\admin\models\BannerChargeLogsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BannerChargeLogsQuery(get_called_class());
    }



    public function asJson()
{
    $data = [];

    $data['id'] = $this->id;
    $data['user_id'] = $this->user_id;
    $data['banner_id'] = $this->banner_id;
    $data['action'] = $this->action; 
    $data['charge_amount'] = $this->charge_amount;
    $data['ip_address'] = $this->ip_address;
    $data['performed_at'] = Yii::$app->formatter->asDatetime($this->performed_at, 'php:Y-m-d H:i:s');
    $data['user_agent'] = $this->user_agent;
    $data['status'] = $this->status;
    $data['created_on'] = Yii::$app->formatter->asDatetime($this->created_on, 'php:Y-m-d H:i:s');
    $data['updated_on'] = Yii::$app->formatter->asDatetime($this->updated_on, 'php:Y-m-d H:i:s');
    $data['create_user_id'] = $this->create_user_id;
    $data['update_user_id'] = $this->update_user_id;
   return $data;
}



}

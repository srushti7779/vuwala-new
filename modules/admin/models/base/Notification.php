<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $module
 * @property string $icon
 * @property integer $order_id
 * @property integer $created_user_id
 * @property string $created_date
 * @property integer $mark_read
 * @property integer $status
 * @property string $model_type
 * @property integer $check_on_ajax
 * @property integer $is_deleted
 * @property string $info_delete
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $created_on
 * @property string $updated_on
 */
class Notification extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            ''
        ];
    }

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
            [['user_id', 'order_id', 'created_user_id', 'check_on_ajax', 'is_deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['title', 'created_user_id', 'is_deleted', 'info_delete'], 'required'],
            [['created_date', 'created_on', 'updated_on'], 'safe'],
            [['title', 'module', 'icon', 'model_type', 'info_delete'], 'string', 'max' => 255],
            [['mark_read', 'status'], 'string', 'max' => 4]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
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

    public function getLatestUnreadNotification()
    {
        return self::find()
            ->where(['mark_read' => 0])
            ->orderBy(['created_on' => SORT_DESC]) 
            ->limit(5)
            ->all(); // This ensures it returns an array of Notification objects.
    }   
       

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'title' => Yii::t('app', 'Title'),
            'module' => Yii::t('app', 'Module'),
            'icon' => Yii::t('app', 'Icon'),
            'order_id' => Yii::t('app', 'Order ID'),
            'created_user_id' => Yii::t('app', 'Created User ID'),
            'created_date' => Yii::t('app', 'Created Date'),
            'mark_read' => Yii::t('app', 'Mark Read'),
            'status' => Yii::t('app', 'Status'),
            'model_type' => Yii::t('app', 'Model Type'),
            'check_on_ajax' => Yii::t('app', 'Check On Ajax'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'info_delete' => Yii::t('app', 'Info Delete'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
        ];
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
     * @return \app\modules\admin\models\NotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\NotificationQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['user_id'] =  $this->user_id;

        $data['title'] =  $this->title;

        $data['module'] =  $this->module;

        $data['icon'] =  $this->icon;

        $data['order_id'] =  $this->order_id;

        $data['created_user_id'] =  $this->created_user_id;

        $data['created_date'] =  $this->created_date;

        $data['mark_read'] =  $this->mark_read;

        $data['status'] =  $this->status;

        $data['model_type'] =  $this->model_type;

        $data['check_on_ajax'] =  $this->check_on_ajax;

        $data['is_deleted'] =  $this->is_deleted;

        $data['info_delete'] =  $this->info_delete;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        return $data;
    }
}

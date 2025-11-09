<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "order_status".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $remarks
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\Orders $order
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class OrderStatus extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'order',
            'createUser',
            'updateUser'
        ];
    }

    const STATUS_NEW_ORDER = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_SERVICE_STARTED = 3;
    const STATUS_SERVICE_COMPLETED = 4;
    const STATUS_ASSIGNED_SERVICE_BOY = 5;
    //Values for Cancel or Rejected case
    const STATUS_CANCELLED_BY_OWNER = 6;
    const STATUS_CANCELLED_BY_USER = 7;
    const STATUS_CANCELLED_BY_ADMIN = 8;
    const STATUS_CANCELLED_BY_SERVICE_BOY = 10;

    const STATUS_CANCELLED = 11;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;




    public function getStateOptions()
{
    return [
        self::STATUS_NEW_ORDER => 'New Order',
        self::STATUS_ACCEPTED => 'Order Accepted',
        self::STATUS_SERVICE_STARTED => 'Service Started',
        self::STATUS_SERVICE_COMPLETED => 'Service Completed',
        self::STATUS_ASSIGNED_SERVICE_BOY => 'Assigned Service Boy',
        self::STATUS_CANCELLED_BY_OWNER => 'Cancelled by Owner',
        self::STATUS_CANCELLED_BY_USER => 'Cancelled by User',
        self::STATUS_CANCELLED_BY_ADMIN => 'Cancelled by Admin',
        self::STATUS_CANCELLED_BY_SERVICE_BOY => 'Cancelled by Service Boy',
    ];
}

public function getStateOptionsBadges()
{
    switch ($this->status) {
        case self::STATUS_NEW_ORDER:
            return '<span class="badge badge-success">New Order</span>';
        case self::STATUS_ACCEPTED:
            return '<span class="badge badge-primary">Order Accepted</span>';
        case self::STATUS_SERVICE_STARTED:
            return '<span class="badge badge-info">Service Started</span>';
        case self::STATUS_SERVICE_COMPLETED:
            return '<span class="badge badge-success">Service Completed</span>';
        case self::STATUS_ASSIGNED_SERVICE_BOY:
            return '<span class="badge badge-warning">Assigned Service Boy</span>';
        case self::STATUS_CANCELLED_BY_OWNER:
            return '<span class="badge badge-danger">Cancelled by Owner</span>';
        case self::STATUS_CANCELLED_BY_USER:
            return '<span class="badge badge-danger">Cancelled by User</span>';
        case self::STATUS_CANCELLED_BY_ADMIN:
            return '<span class="badge badge-danger">Cancelled by Admin</span>';
        case self::STATUS_CANCELLED_BY_SERVICE_BOY:
            return '<span class="badge badge-danger">Cancelled by Service Boy</span>';
        default:
            return '<span class="badge badge-secondary">Unknown Status</span>';
    }
}

 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on','remarks'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_status';
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
            'order_id' => Yii::t('app', 'Order ID'),
            'remarks' => Yii::t('app', 'Remarks'),
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
    public function getOrder()
    {
        return $this->hasOne(\app\modules\admin\models\Orders::className(), ['id' => 'order_id']);
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
     * @return \app\modules\admin\models\OrderStatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\OrderStatusQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['order_id'] =  $this->order_id;
        
                $data['remarks'] =  $this->remarks;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



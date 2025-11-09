<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "order_details".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $service_id
 * @property double $price
 * @property double $total_price
 * @property integer $qty
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\Orders $order
 * @property \app\modules\admin\models\Services $service
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class OrderDetails extends \yii\db\ActiveRecord
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
            'service',
            'createUser',
            'updateUser'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const DISCOUNT_TYPE_PERCENTAGE = 1;
    const DISCOUNT_TYPE_FIXED = 2;


    const IS_NEXT_VISIT_YES = 1;
    const IS_NEXT_VISIT_NO=2;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'service_id', 'price', 'total_price', 'qty', 'status'], 'required'],
            [['order_id', 'service_id', 'qty', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['price', 'total_price'], 'number'],
            [['created_on', 'updated_on'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_details';
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
        }elseif ($this->status == self::STATUS_DELETE) {
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'price' => Yii::t('app', 'Price'),
            'total_price' => Yii::t('app', 'Total Price'),
            'qty' => Yii::t('app', 'Qty'),
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
    public function getService()
    {
        return $this->hasOne(\app\modules\admin\models\Services::className(), ['id' => 'service_id']);
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
     * @return \app\modules\admin\models\OrderDetailsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\OrderDetailsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['order_id'] =  $this->order_id;
        
                $data['service_id'] =  $this->service_id;
        
                $data['price'] =  $this->price;
        
                $data['total_price'] =  $this->total_price;
        
                $data['qty'] =  $this->qty;
                $data['delete_allowed'] =  $this->delete_allowed;
                $data['is_package_service'] =  $this->is_package_service;

                $data['service_details'] =   $this->service->asJson();

                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}




public function asJsonForVendorEarnings(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['order_id'] =  $this->order_id;
        
                $data['service_id'] =  $this->service_id;
        
                $data['price'] =  $this->price;
        
                $data['total_price'] =  $this->total_price;
        
                $data['qty'] =  $this->qty;
            $data['service_details'] =   $this->service->asJsonByOrder();

           
        
            return $data;
}


public function partialPaymentOrdersAsJsonOrderDetails(){
    $data = [] ; 
            $data['id'] =  $this->id;
            $data['service_name'] =   $this->service->service_name??null;
            return $data;
}



}
 


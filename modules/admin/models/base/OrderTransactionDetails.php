<?php


namespace app\modules\admin\models\base;

use app\modules\admin\models\Orders;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "order_transaction_details".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $razorpay_order_id
 * @property string $payment_id
 * @property integer $order_type
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id 
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class OrderTransactionDetails extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'createUser',
            'updateUser',
            'order'
        ];
    }
    const STATUS_SUCCESS = 1;
    const STATUS_PENDING = 2;
    const STATUS_FAILED = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 

    const PAYMENT_TYPE_ONLINE = 1;
    const PAYMENT_TYPE_QR = 2;
    const PAYMENT_TYPE_WALLET = 3;
    const PAYMENT_TYPE_COD = 4;


    const PAYMENT_SOURCE_ONLINE = 1;
    const PAYMENT_SOURCE_WALLET = 3;
    const PAYMENT_SOURCE_QR = 2;
    const PAYMENT_SOURCE_COD = 4;

    const ORDER_TYPE_SERVICE_ORDER ='service_order'; 
    const ORDER_TYPE_SUBSCRIPTION_ORDER ='subscription_order'; 
    const ORDER_TYPE_PRODUCT_ORDER ='product_order';




    public function getPaymentTypeBadges()
    {
        if ($this->payment_type == self::PAYMENT_TYPE_ONLINE) {
            return 'Online Payment';
        } elseif ($this->payment_type == self::PAYMENT_TYPE_QR) {
            return 'QR Payment';
        } elseif ($this->payment_type == self::PAYMENT_TYPE_WALLET) {
            return 'Wallet Payment';
        } elseif ($this->payment_type == self::PAYMENT_TYPE_COD) {
            return 'Cash on Delivery';
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'order_type'], 'required'],
            [['order_id', 'order_type', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['razorpay_order_id', 'payment_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_transaction_details';
    }

    public function getStateOptions()
    {
        return [

          
        ];
    }
    public function getStateOptionsBadges()
    {

    

    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',
           
        ];
    }



    public static function checkPendingAmount($order_id, $order_type)
{
    try {
        // 1. Validate input
        if (empty($order_id)) {
            throw new \InvalidArgumentException("Order ID is required.");
        }

        switch ($order_type) {
            case self::ORDER_TYPE_SERVICE_ORDER:
                $order = Orders::findOne(['id' => $order_id]);
                if ($order === null) {
                    throw new \RuntimeException("Service order not found for ID: {$order_id}");
                }

                $order_total = (float) $order->total_w_tax;

                $total_paid = (float) OrderTransactionDetails::find()
                    ->where([
                        'order_id'   => $order_id,
                        'order_type' => $order_type,
                        'status'     => self::STATUS_SUCCESS,
                    ])
                    ->sum('amount') ?? 0;

                return max(0, $order_total - $total_paid);

            case self::ORDER_TYPE_PRODUCT_ORDER:
                $product_order = ProductOrders::findOne(['id' => $order_id]);
                if ($product_order === null) {
                    throw new \RuntimeException("Product order not found for ID: {$order_id}");
                }

                $order_total = (float) $product_order->total_with_tax;

                $total_paid = (float) OrderTransactionDetails::find()
                    ->where([
                        'order_id' => $order_id,
                        'status'   => self::STATUS_SUCCESS,
                    ])
                    ->sum('amount') ?? 0;

                return max(0, $order_total - $total_paid);

            default:
                throw new \InvalidArgumentException("Unsupported order type: {$order_type}");
        }

    } catch (\Throwable $e) {
        Yii::error([
            'method'  => __METHOD__,
            'orderId' => $order_id,
            'type'    => $order_type,
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);

        // Re-throw so caller can handle gracefully
        throw $e;
    }
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
            'razorpay_order_id' => Yii::t('app', 'Razorpay Order ID'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'order_type' => Yii::t('app', 'Order Type'),
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


    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'order_id']);
    }


    public static function generateUniqueTransactionId()
    {
        do {
            $transactionId = 'TXN' . strtoupper(Yii::$app->security->generateRandomString(10));
        } while (self::find()->where(['transaction_order_id' => $transactionId])->exists());

        return $transactionId;
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
     * @return \app\modules\admin\models\OrderTransactionDetailsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\OrderTransactionDetailsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['order_id'] =  $this->order_id;
        
                $data['razorpay_order_id'] =  $this->razorpay_order_id;
        
                $data['payment_id'] =  $this->payment_id;

                $data['vendor_name'] = $this->order->vendorDetails->business_name?? null;

                $data['store_logo'] = $this->order->vendorDetails->logo?? null;


                $data['amount'] = number_format((float)$this->amount, 2, '.', '');

        
                $data['order_type'] =  $this->order_type;

                $data['payment_type'] =  $this->payment_type;

        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}



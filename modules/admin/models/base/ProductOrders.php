<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "product_orders".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $vendor_details_id
 * @property double $sub_total
 * @property double $tax_percentage
 * @property double $tax_amount
 * @property double $total_with_tax
 * @property integer $payment_status
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\ProductOrdersHasDiscounts[] $productOrdersHasDiscounts
 * @property \app\modules\admin\models\ServiceOrdersProductOrders[] $serviceOrdersProductOrders
 */
class ProductOrders extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'user',
            'createUser',
            'updateUser',
            'vendorDetails',
            'productOrdersHasDiscounts',
            'serviceOrdersProductOrders',
            'productServiceOrderMappings',
            'productOrderItems'
        ];
    }



    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    const PAYMENT_TYPE_ONLINE = 1;
    const PAYMENT_TYPE_QR = 2;
    const PAYMENT_TYPE_WALLET = 3;
    const PAYMENT_TYPE_COD = 4;


    const STATUS_NEW_ORDER = 1;
    const STATUS_PENDING = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETED = 4;

    const CURRENT_STATUS_ACTIVE = 1;
    const CURRENT_STATUS_HOLD = 2;
    const CURRENT_STATUS_COMPLETED = 3;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'vendor_details_id', 'sub_total', 'tax_percentage', 'tax_amount', 'total_with_tax'], 'required'],
            [['user_id', 'vendor_details_id', 'payment_status', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['sub_total', 'tax_percentage', 'tax_amount', 'total_with_tax'], 'number'],
            [['created_on', 'updated_on'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_orders';
    }

    public function getStateOptions()
    {
        return [];
    }
    public function getStateOptionsBadges() {}

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
            'user_id' => Yii::t('app', 'User ID'),
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'sub_total' => Yii::t('app', 'Sub Total'),
            'tax_percentage' => Yii::t('app', 'Tax Percentage'),
            'tax_amount' => Yii::t('app', 'Tax Amount'),
            'total_with_tax' => Yii::t('app', 'Total With Tax'),
            'payment_status' => Yii::t('app', 'Payment Status'),
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
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductOrdersHasDiscounts()
    {
        return $this->hasMany(\app\modules\admin\models\ProductOrdersHasDiscounts::className(), ['product_order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceOrdersProductOrders()
    {
        return $this->hasMany(\app\modules\admin\models\ServiceOrdersProductOrders::className(), ['product_order_id' => 'id']);
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


    public function getProductOrderItems()
    {
        return $this->hasMany(\app\modules\admin\models\ProductOrderItems::className(), ['product_order_id' => 'id']);
    }



    public function getProductServiceOrderMappings()
    {
        return $this->hasOne(\app\modules\admin\models\ProductServiceOrderMappings::className(), ['product_order_id' => 'id']);
    }


    public static function checkPendingAmount($order_id, $order_type)
    {
        $order_details = self::findOne(['id' => $order_id]);
        if (!empty($order_details)) {
            $total_amount = !empty($order_details->total_with_tax) ? $order_details->total_with_tax : 0;

            $amount_paid = OrderTransactionDetails::find()
                ->where(['order_id' => $order_id])
                ->andWhere(['order_type' => $order_type])
                ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');
            $amount_paid = !empty($amount_paid) ? $amount_paid : 0;

            $pending_amount = $total_amount - $amount_paid;
            return !empty($pending_amount) ? $pending_amount : 0;
        } else {
            return "No order found";
        }
    }


    public static function getPaymentModes($order_id, $order_type){
        $payment_mode = OrderTransactionDetails::find()
            ->select('payment_type')
                ->where(['order_id' => $order_id])
                ->andWhere(['order_type' => $order_type])
                ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->column();

                $payment_mode = array_unique($payment_mode);
                $getPaymentTypeBadges = [];
                if(!empty($payment_mode)){
                    foreach($payment_mode as $mode){
                        if($mode == OrderTransactionDetails::PAYMENT_TYPE_COD){
                            $getPaymentTypeBadges[] = 'COD';
                        } elseif($mode == OrderTransactionDetails::PAYMENT_TYPE_ONLINE){
                            $getPaymentTypeBadges[] = 'Online';
                        } elseif($mode == OrderTransactionDetails::PAYMENT_TYPE_WALLET){
                            $getPaymentTypeBadges[] = 'Wallet';
                        } elseif($mode == OrderTransactionDetails::PAYMENT_TYPE_QR){
                            $getPaymentTypeBadges[] = 'QR';
                        }
                    }
                }

            return $getPaymentTypeBadges;

    }


    /**
     * @inheritdoc
     * @return \app\modules\admin\models\ProductOrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ProductOrdersQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['user_id'] =  $this->user_id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['completed_on'] =  $this->completed_on;

        $data['sub_total'] =  $this->sub_total;

        $data['tax_percentage'] =  $this->tax_percentage;

        $data['tax_amount'] =  $this->tax_amount;

        $data['total_with_tax'] =  $this->total_with_tax;

        $data['payment_status'] =  $this->payment_status;

        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $productOrderItems = $this->productOrderItems;

        if (!empty($productOrderItems)) {
            foreach ($productOrderItems as $productOrderItem) {
                $data['productOrderItems'][] = $productOrderItem->asJson();
            }
        } else {
            $data['productOrderItems'] = [];
        }
        return $data;
    }

    


    public function asJsonList()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['user_id'] =  $this->user_id;

        $first_name =  !empty($this->user) ? $this->user->first_name : '';
        $last_name =  !empty($this->user) ? $this->user->last_name : '';
        $full_name = $first_name . ' ' . $last_name;
        $data['customer_name'] = $full_name;
        $data['customer_contact'] = !empty($this->user) ? $this->user->contact_no : '';

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['completed_on'] =  $this->completed_on;

        $data['sub_total'] =  $this->sub_total;

        $data['tax_percentage'] =  $this->tax_percentage;

        $data['tax_amount'] =  $this->tax_amount;

        $data['total_with_tax'] =  $this->total_with_tax;

        $data['payment_status'] =  $this->payment_status;

        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $productOrderItemsCount = ProductOrderItems::find()->where(['product_order_id' => $this->id])->count();
        $order_transaction_details = OrderTransactionDetails::find()
        ->where(['order_id' => $this->id])
        ->andWhere(['order_type' => OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER])
        ->andWhere(['status'=>OrderTransactionDetails::STATUS_SUCCESS])
        ->sum('amount');
        $data['amount_paid'] = !empty($order_transaction_details) ? $order_transaction_details : 0;
        $data['amount_pending'] = self::checkPendingAmount($this->id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);
        $data['total_items_count'] =  $productOrderItemsCount;
        $data['payment_modes'] = self::getPaymentModes($this->id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);
        return $data;
    }


    public function asJsonView(){

  $data = [];
        $data['id'] =  $this->id;

        $data['user_id'] =  $this->user_id;

        $data['customer_details'] = !empty($this->user) ? $this->user->asJsonUserClient() : (object)[];

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['completed_on'] =  $this->completed_on;

        $data['sub_total'] =  $this->sub_total;

        $data['tax_percentage'] =  $this->tax_percentage;

        $data['tax_amount'] =  $this->tax_amount;

        $data['total_with_tax'] =  $this->total_with_tax;

        $data['payment_status'] =  $this->payment_status;

        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $productOrderItemsCount = ProductOrderItems::find()->where(['product_order_id' => $this->id])->count();
        $order_transaction_details = OrderTransactionDetails::find()
        ->where(['order_id' => $this->id])
        ->andWhere(['order_type' => OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER])
        ->andWhere(['status'=>OrderTransactionDetails::STATUS_SUCCESS])
        ->sum('amount');
        $data['amount_paid'] = !empty($order_transaction_details) ? $order_transaction_details : 0;
        $data['amount_pending'] = self::checkPendingAmount($this->id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);
        $data['total_items_count'] =  $productOrderItemsCount;
        $data['payment_modes'] = self::getPaymentModes($this->id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);


        $productOrderItems = $this->productOrderItems;

        if (!empty($productOrderItems)) {
            foreach ($productOrderItems as $productOrderItem) {
                $data['productOrderItems'][] = $productOrderItem->asJson();
            }
        } else {
            $data['productOrderItems'] = [];
        }
        return $data;

    }

}

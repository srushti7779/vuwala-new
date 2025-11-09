<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\modules\admin\models\WebSetting;


/**
 * This is the base model class for table "vendor_earnings".
 *
 * @property integer $id
 * @property double $service_charge
 * @property integer $order_id
 * @property integer $vendor_details_id
 * @property double $order_sub_total
 * @property integer $admin_commission_per
 * @property double $admin_commission_amount
 * @property integer $status
 * @property string $earnings_added_reason
 * @property string $cancelled_reason
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\Orders $order
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class VendorEarnings extends \yii\db\ActiveRecord
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
            'vendorDetails',
            'createUser',
            'updateUser'
        ];
    } 

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCELLED = 2;


    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    const PAYMENT_TYPE_ONLINE = 1;
    const PAYMENT_TYPE_QR = 2;
    const PAYMENT_TYPE_WALLET = 3;



    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_FAILED = 0;
    const PAYMENT_STATUS_PENDING = 2; 


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_charge', 'order_sub_total', 'admin_commission_amount'], 'number'],
            [['order_id', 'vendor_details_id', 'order_sub_total', 'admin_commission_per', 'admin_commission_amount', 'status'], 'required'],
            [['order_id', 'vendor_details_id', 'admin_commission_per', 'status', 'create_user_id', 'update_user_id', 'type','payment_status'], 'integer'],
            [['earnings_added_reason', 'razorpay_order_id','transaction_id'], 'string'],
            [['cancelled_reason', 'created_on', 'updated_on'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_earnings';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function getStateOptionsBadges()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '<span class="badge badge-warning">Pending</span>';
            case self::STATUS_APPROVED:
                return '<span class="badge badge-success">Approved</span>';
            case self::STATUS_CANCELLED:
                return '<span class="badge badge-danger">Cancelled</span>';
            default:
                return '<span class="badge badge-secondary">Unknown Status</span>';
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
            'service_charge' => Yii::t('app', 'Service Charge'),
            'order_id' => Yii::t('app', 'Order ID'),
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'order_sub_total' => Yii::t('app', 'Order Sub Total'),
            'admin_commission_per' => Yii::t('app', 'Admin Commission Per'),
            'admin_commission_amount' => Yii::t('app', 'Admin Commission Amount'),
            'status' => Yii::t('app', 'Status'),
            'payment_status' => Yii::t('app', 'Payemnt Status'),
            'earnings_added_reason' => Yii::t('app', 'Earnings Added Reason'),
            'cancelled_reason' => Yii::t('app', 'Cancelled Reason'),
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }
    public function getVendorSettlements()
    {
        return $this->hasOne(\app\modules\admin\models\VendorSettlements::class, ['vendor_earnings_id' => 'id']);
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




    public static function createVendorEaringsFromCompletedOrder($order, $vendorDetails)
    {
        // Check if earnings already exist
        $alreadyExists = self::find()
            ->where(['order_id' => $order->id, 'vendor_details_id' => $vendorDetails->id])
            ->exists();
    
        if ($alreadyExists) {
            return true;
        }
    
        // Check if full payment is done
        $totalPaid = OrderTransactionDetails::find()
            ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->sum('amount');
    
        if ($totalPaid < $order->total_w_tax) {
            return false; // Payment not complete yet
        }

        $settings = new WebSetting();
        $conv_fee = $settings->getSettingBykey('conv_fee')??0;

        // Commission calculation
        $commissionRate = floatval($vendorDetails->commission ?? $conv_fee);
        $commissionAmount = $commissionRate;
            // Calculate order value after removing service charge
        $base_total = floatval($order->sub_total - $order->voucher_amount);
        $vendorReceived = $base_total+$order->Subtotal_tax;
        $net_earning = round($vendorReceived ,2);
        // Create VendorEarnings
        $earnings = new self();
        $earnings->order_id = $order->id;
        $earnings->total_w_tax = $order->total_w_tax;
        $earnings->order_sub_total = $order->sub_total;
        $earnings->vendor_details_id = $vendorDetails->id;
        $earnings->service_charge = floatval($order->service_charge);
        $earnings->service_charge_w_tax = floatval($order->service_charge_w_tax);
        $earnings->admin_commission_per = 0;
        $earnings->admin_commission_amount = round($commissionAmount, 2);
        $earnings->status = self::STATUS_APPROVED;
        $earnings->vendor_received_amount = $vendorReceived;
        return $earnings->save(false);
    }
    




    /**
     * @inheritdoc
     * @return \app\modules\admin\models\VendorEarningsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorEarningsQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_charge'] =  $this->service_charge;

        $data['order_id'] =  $this->order_id;


        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['order_sub_total'] =  $this->order_sub_total;

        $data['admin_commission_per'] =  $this->admin_commission_per;

        $data['admin_commission_amount'] =  $this->admin_commission_amount;

        $data['status'] =  $this->status;

        $data['earnings_added_reason'] =  $this->earnings_added_reason;

        $data['cancelled_reason'] =  $this->cancelled_reason;
        
        $data['transaction_id'] =  $this->transaction_id;
        
        $data['razorpay_order_id'] =  $this->razorpay_order_id;

        $data['created_on'] =  $this->created_on; 

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;
        
        $data['update_user_id'] =  $this->update_user_id; 

        $data['user'] = !empty($this->createUser->first_name) ? $this->createUser->first_name : $this->createUser->contact_no;
 
        return $data;
    }



        public function asJsonList()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_charge'] =  $this->service_charge;

        $data['order_id'] =  $this->order_id;

        $data['customer'] = $this->order->asJsonForVendorEarnings();

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['order_sub_total'] =  $this->order_sub_total;

        $data['admin_commission_per'] =  $this->admin_commission_per;

        $data['admin_commission_amount'] =  $this->admin_commission_amount;

        $data['status'] =  $this->status;

        $data['earnings_added_reason'] =  $this->earnings_added_reason;

        $data['cancelled_reason'] =  $this->cancelled_reason;
        
        $data['transaction_id'] =  $this->transaction_id;
        
        $data['razorpay_order_id'] =  $this->razorpay_order_id;

    
 
        return $data;
    }


    public function scanpayJson() 
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['order_id'] =  $this->order_id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['vendor_name'] =  $this->vendorDetails->business_name;  

        $data['vendor_logo'] =  $this->vendorDetails->logo;  

        $data['razorpay_order_id'] =  $this->razorpay_order_id;

        
        $data['transaction_id'] =  $this->transaction_id;

        $data['payment_status'] =  $this->payment_status; 
         
        $data['type'] =  $this->type;

        $data['order_sub_total'] =  $this->order_sub_total; 

        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;
 
        $data['update_user_id'] =  $this->update_user_id;

        return $data;
    }
}

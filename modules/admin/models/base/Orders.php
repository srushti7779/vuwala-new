<?php


namespace app\modules\admin\models\base;

use app\models\User;
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\ComboPackagesCart;
use app\modules\admin\models\ComboServices;
use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\modules\admin\models\WebSetting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use yii\helpers\ArrayHelper;

/**
 * This is the base model class for table "orders".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $vendor_details_id
 * @property string $json_details
 * @property integer $qty
 * @property string $trans_type
 * @property string $payment_type
 * @property double $sub_total
 * @property double $tip_amt
 * @property double $tax
 * @property double $processing_charges
 * @property double $service_charge
 * @property double $taxable_total
 * @property double $total_w_tax
 * @property integer $status
 * @property string $cancel_reason
 * @property string $cancel_description
 * @property string $services
 * @property string $type
 * @property string $schedule_date
 * @property string $schedule_time
 * @property string $service_instruction
 * @property string $voucher_code
 * @property double $voucher_amount
 * @property string $voucher_type
 * @property integer $payment_status
 * @property string $ip_ress
 * @property integer $service_address
 * @property integer $otp
 * @property double $cgst
 * @property double $sgst
 * @property integer $is_verify
 * @property integer $service_type  
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\CouponsApplied[] $couponsApplieds
 * @property \app\modules\admin\models\OrderDetails[] $orderDetails
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\VendorEarnings[] $vendorEarnings
 * @property \app\modules\admin\models\ProductServiceOrderMappings[] $productServiceOrders
 */
class Orders extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;
    public $home_visitor_id;
    public $type;
    public $gender;



    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'couponsApplieds',
            'homeVisitorsHasOrders',
            'orderDetails',
            'orderStatuses',
            'vendorDetails',
            'createUser',
            'updateUser',
            'user',
            'mainCategory',
            'shopReviews',
            'vendorEarnings',
            'comboOrders',
            'comboOrderServicies',
            'productServiceOrderMappings',

        ];
    }

    const STATUS_NEW_ORDER = 1;

    const STATUS_ACCEPTED = 2;

    const STATUS_SERVICE_STARTED = 3;

    const STATUS_SERVICE_COMPLETED = 4;

    const STATUS_ASSIGNED_SERVICE_STAFF = 5;

    //Values for Cancel or Rejected case
    const STATUS_CANCELLED_BY_OWNER = 6;
    const STATUS_CANCELLED_BY_USER = 7;
    const STATUS_CANCELLED_BY_ADMIN = 8;
    const STATUS_CANCELLED_BY_HOME_VISITORS = 10;
    const STATUS_CANCELLED = 11;
    const STATUS_CANCELLED_BY_SERVICE_BOY = 12;

    const PLATFORM_WEB = 1;
    const PLATFORM_ANDROID = 2;
    const PLATFORM_IOS = 3;


    const PLATFORM_SOURCE_WEB = 1;
    const PLATFORM_SOURCE_APP = 2;
    const PLATFORM_SOURCE_WEB_VENDOR = 3;

    const STATUS_START_TO_LOCATION_HOME_VISIT = 13;
    const STATUS_ARRIVED_CUSTOMER_LOCATION = 14;
    const STATUS_WAITING_FOR_APPROVAL = 15;


    const NEXT_VISIT_ORDER_PAYMENT_TYPE_FULL = 1;

    const NEXT_VISIT_ORDER_PAYMENT_TYPE_SPLIT = 2;




    const ORDER_TYPE_WALK_IN_IMMEDIATE = 1;

    const ORDER_TYPE_PRE_BOOKING = 2;




    const CURRENT_STATUS_ACTIVE = 1;
    const CURRENT_STATUS_HOLD = 2;
    const CURRENT_STATUS_COMPLETED = 3;




    const IS_NEXT_VISIT_YES = 1;
    const IS_NEXT_VISIT_NO = 2;

    const IS_DELETED_YES = 1;
    const IS_DELETED_NO = 0;

    const RATING_FLAG_YES_RATED = 1;

    const RATING_FLAG_NOT_RATED = 2;


    const SERVICE_TYPE_WALK_IN = 1;

    const SERVICE_TYPE_HOME_VISIT = 2;


    public function getOrderServicesAsString()
    {
        return implode(', ', ArrayHelper::getColumn($this->orderDetails, 'service.service_name'));
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
            case self::STATUS_ASSIGNED_SERVICE_STAFF:
                return '<span class="badge badge-warning">Assigned Staff</span>';
            case self::STATUS_CANCELLED_BY_OWNER:
                return '<span class="badge badge-danger">Cancelled by Owner</span>';
            case self::STATUS_CANCELLED_BY_USER:
                return '<span class="badge badge-danger">Cancelled by User</span>';
            case self::STATUS_CANCELLED_BY_ADMIN:
                return '<span class="badge badge-danger">Cancelled by Admin</span>';
            case self::STATUS_CANCELLED_BY_HOME_VISITORS:
                return '<span class="badge badge-danger">Cancelled by Home Visitor</span>';
            case self::STATUS_START_TO_LOCATION_HOME_VISIT:
                return '<span class="badge badge-danger">Started to Location (Home Visit)</span>';
            case self::STATUS_ARRIVED_CUSTOMER_LOCATION:
                return '<span class="badge badge-danger">Arrived at Customer Location</span>';



            default:
                return '<span class="badge badge-secondary">Unknown Status</span>';
        }
    }




    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    const TYPE_COD = 1;
    const TYPE_ONLINE = 2;
    const TYPE_WALLET = 3;

    const PAYMENT_DONE = 1;
    const PAYMENT_PENDING = 2;
    const PAYMENT_FAILED = 3;


    const SERVICE_PAYMENT_TYPE_BEFORE = 1;
    const SERVICE_PAYMENT_TYPE_AFTER = 2;



    const WALK_IN = 1;
    const HOME_VISIT = 2;

    const OTP_VERIFIED = 1;

    const TRANS_TYPE_WALK_IN = 1;
    const TRANS_TYPE_HOME_VISIT = 2;

    const API_OK = 'OK';
    const API_NOK = 'NOK';



    const PAYMENT_MODE_FULL = 1;
    const PAYMENT_MODE_PARTIAL = 2;


    const FULL_PAYMENT_STATUS_DONE = 1;
    const FULL_PAYMENT_STATUS_PENDING = 2;



    static function   formatMoney($amount)
    {
        return (abs($amount) < 0.01) ? '0' : number_format($amount, 2, '.', '');
    }



    public function getStateOptions()
    {
        return [

            self::STATUS_NEW_ORDER => 'New Order',
            self::STATUS_ACCEPTED => 'Order Accepted',

        ];
    }


    public function getServiceTypeOptions()
    {
        return [

            self::WALK_IN => 'Walk In',
            self::HOME_VISIT => 'Home Visit',

        ];
    }
    public function getServiceTypeOptionBadges()
    {

        if ($this->service_type == self::WALK_IN) {
            return '<span class="badge badge-success">Walk In</span>';
        } elseif ($this->service_type == self::HOME_VISIT) {
            return '<span class="badge badge-success">Home Visit</span>';
        }
    }

    public function getPaymentTypeOptions()
    {
        return [

            self::TYPE_COD => 'COD',
            self::TYPE_ONLINE => 'ONLINE',

        ];
    }
    public function getPaymentTypeOptionBadges()
    {

        if ($this->payment_type == self::TYPE_COD) {
            return '<span class="badge badge-success">COD</span>';
        } elseif ($this->payment_type == self::TYPE_ONLINE) {
            return '<span class="badge badge-success">ONLINE</span>';
        } elseif ($this->payment_type == self::TYPE_WALLET) {
            return '<span class="badge badge-success">Wallet</span>';
        }
    }

    public function getPaymentStatusOptions()
    {
        return [

            self::PAYMENT_DONE => 'Payment Done',
            self::PAYMENT_PENDING => 'Payment Pending',

        ];
    }

    public function getPaymentStatusOptionBadges()
    {

        if ($this->payment_status == self::PAYMENT_DONE) {
            return '<span class="badge badge-success">Payment Done</span>';
        } elseif ($this->payment_status == self::PAYMENT_PENDING) {
            return '<span class="badge badge-warning">payment Pending</span>';
        } elseif ($this->payment_status == self::PAYMENT_FAILED) {
            return '<span class="badge badge-danger">payment failed</span>';
        } else {
            return '<span class="badge badge-info">Not set</span>';
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

    public function getTransTypeOptions()
    {
        return [

            self::TRANS_TYPE_WALK_IN => 'WanK In',
            self::TRANS_TYPE_HOME_VISIT => 'Home Visit',

        ];
    }
    public function getTransTypeOptionsBadges()
    {

        if ($this->trans_type == self::TRANS_TYPE_WALK_IN) {
            return '<span class="badge badge-success">WanK In</span>';
        } elseif ($this->trans_type == self::TRANS_TYPE_HOME_VISIT) {
            return '<span class="badge badge-default">Home Visit</span>';
        }
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'vendor_details_id'], 'required'],

            [['user_id', 'vendor_details_id', 'qty', 'status', 'payment_status', 'service_address', 'otp', 'is_verify', 'service_type', 'create_user_id', 'update_user_id', 'is_deleted'], 'integer'],

            [['json_details', 'cancel_reason', 'cancel_description', 'service_instruction'], 'string'],
            [['sub_total', 'tip_amt', 'tax', 'processing_charges', 'service_charge', 'taxable_total', 'total_w_tax', 'voucher_amount', 'cgst', 'sgst'], 'number'],

            [['schedule_date', 'schedule_time', 'created_on', 'updated_on', 'is_deleted'], 'safe'],
            [['trans_type', 'payment_type', 'schedule_time', 'voucher_code', 'voucher_type'], 'string', 'max' => 100],
            [['ip_ress'], 'string', 'max' => 50],

            [['type', 'gender'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
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
            'json_details' => Yii::t('app', 'Json Details'),
            'qty' => Yii::t('app', 'Qty'),
            'trans_type' => Yii::t('app', 'Trans Type'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'sub_total' => Yii::t('app', 'Sub Total'),
            'tip_amt' => Yii::t('app', 'Tip Amt'),
            'tax' => Yii::t('app', 'Tax'),
            'processing_charges' => Yii::t('app', 'Processing Charges'),
            'service_charge' => Yii::t('app', 'Service Charge'),
            'taxable_total' => Yii::t('app', 'Taxable Total'),
            'total_w_tax' => Yii::t('app', 'Total W Tax'),
            'status' => Yii::t('app', 'Status'),
            'cancel_reason' => Yii::t('app', 'Cancel Reason'),
            'cancel_description' => Yii::t('app', 'Cancel Descreption'),
            'schedule_date' => Yii::t('app', 'Schedule Date'),
            'schedule_time' => Yii::t('app', 'Schedule Time'),
            'service_instruction' => Yii::t('app', 'Service Instruction'),
            'voucher_code' => Yii::t('app', 'Voucher Code'),
            'voucher_amount' => Yii::t('app', 'Voucher Amount'),
            'voucher_type' => Yii::t('app', 'Voucher Type'),
            'payment_status' => Yii::t('app', 'Payment Status'),
            'ip_ress' => Yii::t('app', 'Ip Ress'),
            'service_address' => Yii::t('app', 'Service Address'),
            'otp' => Yii::t('app', 'Otp'),
            'cgst' => Yii::t('app', 'Cgst'),
            'sgst' => Yii::t('app', 'Sgst'),
            'is_verify' => Yii::t('app', 'Is Verify'),
            'service_type' => Yii::t('app', 'Service Type'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }





    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponsApplieds()
    {
        return $this->hasMany(\app\modules\admin\models\CouponsApplied::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHomeVisitorsHasOrders()
    {
        return $this->hasOne(\app\modules\admin\models\HomeVisitorsHasOrders::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(\app\modules\admin\models\OrderDetails::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatuses()
    {
        return $this->hasMany(\app\modules\admin\models\OrderStatus::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasOne(VendorDetails::className(), ['id' => 'vendor_details_id']);
    }


    public function getVendorDetail()
    {
        return $this->hasOne(VendorDetails::class, ['id' => 'vendor_details_id']);
    }



    public function getDeliveryAddress()
    {
        return $this->hasOne(\app\modules\admin\models\DeliveryAddress::className(), ['id' => 'service_address']);
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
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainCategory()
    {
        return $this->hasOne(\app\modules\admin\models\MainCategory::className(), ['id' => 'main_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopReviews()
    {
        return $this->hasMany(\app\modules\admin\models\ShopReview::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorEarnings()
    {
        return $this->hasMany(\app\modules\admin\models\VendorEarnings::className(), ['order_id' => 'id']);
    }
    public function getOrders()
    {
        return $this->hasMany(\app\modules\admin\models\ComboOrder::className(), ['order_id' => 'id']);
    }
    public function getValidComboOrders()
    {
        return $this->getComboOrders()->where(['!=', 'combo_package_id', 0]);
    }
    public function getServices()
    {
        return $this->hasMany(\app\modules\admin\models\Services::className(), ['vendor_details_id' => 'id']);
    }




    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComboOrders()
    {
        return $this->hasMany(\app\modules\admin\models\ComboOrder::class, ['order_id' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComboOrderServicies()
    {
        return $this->hasMany(\app\modules\admin\models\ComboOrderServicies::className(), ['order_id' => 'id']);
    }
    public function getProductServiceOrders()
    {
        return $this->hasMany(ProductServiceOrderMappings::class, ['order_id' => 'id']);
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
     * @return \app\modules\admin\models\OrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\OrdersQuery(get_called_class());
    }




    public static function calculateReferralDiscount($user_id, $cartAmount)
    {
        $referral_discount_amount = 0;
        $user = User::findOne(['id' => $user_id]);

        // Only if the user has a referral ID
        if (!empty($user->referral_id)) {
            // Check if this is user's first paid order
            $hasPaidOrder = self::find()
                ->where(['user_id' => $user->id])
                ->andWhere(['payment_status' => self::FULL_PAYMENT_STATUS_DONE])
                ->exists();

            if (!$hasPaidOrder) {
                // Load referral discount percentage
                $settings = new WebSetting();
                $referral_discount_percentage = $settings->getSettingBykey('referral_discount_percentage');

                if (!empty($referral_discount_percentage) && is_numeric($referral_discount_percentage)) {
                    $referral_discount_amount = ($cartAmount * $referral_discount_percentage) / 100;
                }
            }
        }

        return $referral_discount_amount;
    }



    public static function assignPaymentModeByOrder($order_id)
    {
        $order_transaction_details = OrderTransactionDetails::find()->where(['order_id' => $order_id])->select('payment_source')->one();
        if (!empty($order_transaction_details)) {
            return $order_transaction_details->payment_source;
        } else {
            return;
        }
    }


    public function getRequiredNextSession($cart_id)
    {
        // 1. Get all cart items for this cart
        $cart_items = CartItems::find()->where(['cart_id' => $cart_id])->all();
        if (empty($cart_items)) {
            return 0;
        }

        // 2. Get all unique sub_category_ids from cart services
        $subCategoryIds = [];
        foreach ($cart_items as $cart_item) {
            if ($cart_item->serviceItem && $cart_item->serviceItem->sub_category_id) {
                $subCategoryIds[] = $cart_item->serviceItem->sub_category_id;
                $serviceItemId[] = $cart_item->serviceItem->id;
            }
        }
        $subCategoryIds = array_unique($subCategoryIds);
        if (empty($subCategoryIds)) {
            return 0;
        }

        // 3. Get all main_category_ids via sub_category table
        $mainCategoryIds = SubCategory::find()
            ->select('main_category_id')
            ->where(['id' => $subCategoryIds])
            ->column();
        $mainCategoryIds = array_unique($mainCategoryIds);
        if (empty($mainCategoryIds)) {
            return 0;
        }

        $countServiceWithNextVisit = Services::find()
            ->where(['id' => $serviceItemId, 'is_sessions_required' => 1])
            ->count();

        // 4. Check if any main category has is_scheduled_next_visit=1
        $countMainCategory = MainCategory::find()
            ->where(['id' => $mainCategoryIds, 'is_scheduled_next_visit' => 1])
            ->count();
        $count = $countMainCategory + $countServiceWithNextVisit;

        return $count > 0 ? 1 : 0;
    }




    public function saveOrderByCart($type, $user_id)
    {
        $data = [];

        try {
            // Find cart for the user
            $cart = Cart::find()->where(['user_id' => $user_id])->one();

            if (empty($cart)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t('app', 'No cart found for the user.');
                return $data;
            }
            $settings = new WebSetting();


            // Initialize a new order
            $sp_order = new Orders();
            $sp_order->user_id = $user_id;
            $sp_order->vendor_details_id = $cart->vendor_details_id;
            $sp_order->main_category_id = $cart->vendorDetails->main_category_id;
            $sp_order->qty = $cart->quantity;
            $sp_order->trans_type = $cart->type_id;
            $sp_order->service_type = $cart->type_id;
            $sp_order->payment_type = $type;
            $sp_order->sub_total = $cart->amount;
            $sp_order->tip_amt = $cart->tip;
            $sp_order->referral_discount_percentage = $cart->referral_discount_percentage;
            $sp_order->referral_discount_amount = $cart->referral_discount_amount;
            $sp_order->tax = $cart->cgst + $cart->sgst;
            $sp_order->service_charge = $cart->service_fees;
            $sp_order->processing_charges = $cart->other_charges;
            $sp_order->service_charge_w_tax = $cart->service_fees_with_tax ?? 0;
            $sp_order->taxable_total = number_format(floatval($cart->tip) + floatval($sp_order->processing_charges) + floatval($sp_order->service_charge_w_tax), 2, '.', '');
            $sp_order->service_charge_tax_amt  = !empty($sp_order->service_charge_w_tax) ? $sp_order->service_charge_w_tax - $cart->service_fees : 0;
            $cartAmount = floatval($cart->amount);
            $couponDiscount = floatval($cart->coupon_discount ?? 0);


            // Step 1: Subtotal after coupon
            $subTotal = max(0, $cartAmount - $couponDiscount - $cart->referral_discount_amount);
            // Step 2: Tax on subtotal


            $taxRate = floatval($sp_order->tax); // e.g., 0.18
            $taxOnSubTotal = round(($subTotal * $taxRate) / 100, 2);

            // Step 3: Add other taxable charges (e.g., service fee, convenience fee)
            $taxableTotal = floatval($sp_order->taxable_total);

            $sp_order->Subtotal_tax = $taxOnSubTotal;

            $sp_order->total_w_tax = round(
                floatval($subTotal) +
                    floatval($sp_order->taxable_total) +
                    floatval($taxOnSubTotal),
                2
            );


            $sp_order->payment_mode = $cart->payment_mode;
            $advance_pay = $settings->getSettingBykey('advance_pay');




            if ($cart->payment_mode == Orders::PAYMENT_MODE_PARTIAL && is_numeric($advance_pay)) {
                $advance_amount = round(($sp_order->total_w_tax * $advance_pay) / 100, 2);
                // Calculate advance based on percentage
                $sp_order->payable_amount = round(($sp_order->total_w_tax * $advance_pay) / 100, 2);
                $sp_order->balance_amount = round($sp_order->total_w_tax - $advance_amount, 2);
            } else {
                // Default: full payment
                $sp_order->payable_amount = $sp_order->total_w_tax;
                $sp_order->balance_amount = 0;
            }

            $allow_order_approval = $cart->vendorDetails->allow_order_approval;
            if ($allow_order_approval) {
                $sp_order->status = Orders::STATUS_WAITING_FOR_APPROVAL;
            } else {
                $sp_order->status = Orders::STATUS_NEW_ORDER;
            }
            $sp_order->schedule_date = $cart->service_date;
            $sp_order->schedule_time = $cart->service_time;
            $sp_order->platform_source = Orders::PLATFORM_SOURCE_APP;

            $sp_order->service_address = $cart->service_address;
            $sp_order->service_instruction = "";
            $sp_order->voucher_code = $cart->coupon_code;
            $sp_order->voucher_amount = $cart->coupon_discount;
            $sp_order->voucher_type = $cart->coupon_applied_id;
            $sp_order->ip_ress = $_SERVER['REMOTE_ADDR'];
            $sp_order->otp = rand(1111, 9999);
            $sp_order->cgst = $cart->cgst;
            $sp_order->sgst = $cart->sgst;
            // Set payment status



            $sp_order->payment_status = ($type == Orders::TYPE_WALLET) ? Orders::PAYMENT_DONE : Orders::PAYMENT_PENDING;
            $sp_order->next_visit_required  = $this->getRequiredNextSession($cart->id);
            // Save the order
            if (!$sp_order->save(false)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t('app', 'Failed to save the order. Please try again.');
                return $data;
            }




            // Get cart items and add to order details
            $cart_items = CartItems::find()->where(['cart_id' => $cart->id])->andWhere(['!=', 'quantity', 0])->all();
            if (empty($cart_items)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t('app', 'No items found in the cart.');
                return $data;
            }

            $combo_packages_cart = ComboPackagesCart::findAll(['user_id' => $user_id]);

            if (!empty($combo_packages_cart)) {
                foreach ($combo_packages_cart as $combo_cart_order) {
                    $combo_order = new ComboOrder();
                    $combo_order->order_id = $sp_order->id;
                    $combo_order->vendor_details_id = $sp_order->vendor_details_id;
                    $combo_order->combo_package_id = $combo_cart_order->combo_package_id;
                    $combo_order->amount = $combo_cart_order->amount;
                    $combo_order->status = ComboOrder::STATUS_ACTIVE;
                    $combo_order->save(false);


                    $combo_services = ComboServices::find()
                        ->where(['combo_package_id' => $combo_cart_order->combo_package_id])
                        ->all();
                    if (!empty($combo_services)) {
                        foreach ($combo_services as $combo_services_data) {
                            $combo_order_servicies = new ComboOrderServicies();
                            $combo_order_servicies->order_id = $sp_order->id;
                            $combo_order_servicies->combo_order_id  = $combo_order->id;
                            $combo_order_servicies->combo_package_id = $combo_services_data->combo_package_id;
                            $combo_order_servicies->service_id = $combo_services_data->services_id;
                            $combo_order_servicies->save(false);
                        }
                    }
                }
            }




            foreach ($cart_items as $cart_item) {
                $order_items = new OrderDetails();
                $order_items->order_id = $sp_order->id;
                $order_items->service_id = $cart_item->service_item_id;
                $order_items->qty = $cart_item->quantity;
                $order_items->total_price = $cart_item->quantity * $cart_item->amount;
                $order_items->price = $cart_item->amount;
                $order_items->is_package_service = $cart_item->is_package_service;
                $order_items->status = 1;

                if (!$order_items->save(false)) {
                    $data['status'] = self::API_NOK;
                    $data['error'] = Yii::t('app', 'Failed to save order item: {error}', [
                        'error' => json_encode($order_items->getErrors()),
                    ]);
                    return $data;
                }
            }


            return $sp_order;
        } catch (NotFoundHttpException $e) {
            Yii::error('Cart or Cart Items not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t('app', 'Unable to proceed: {message}', ['message' => $e->getMessage()]);
            return $data;
        } catch (ServerErrorHttpException $e) {
            Yii::error('Order or Order Items save failed: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t('app', 'Order process failed: {message}', ['message' => $e->getMessage()]);
            return $data;
        } catch (\Exception $e) {
            Yii::error('Unexpected error in saveOrderByCart: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
            return $data;
        }
    }







    public static function recalculateServiceOrders($orderId)
    {
        // Find the order
        $order = Orders::findOne(['id' => $orderId]);
        if (!empty($order)) {
            $settings = new WebSetting();
            $tax = $settings->getSettingBykey('tax') ?? 0;
            $cgst = $tax / 2;
            $sgst = $tax / 2;
            $order_discounts = OrderDiscounts::find()->where(['order_id' => $orderId])->sum('discount_amount');
            $conv_fee = $settings->getSettingBykey('conv_fee');
            $shopDetails = VendorDetails::findOne(['id' => $order->vendor_details_id]);
            $service_charge = !empty($shopDetails->min_service_fee) ? $shopDetails->min_service_fee : $conv_fee;
            $service_charge_w_tax = $service_charge + (($service_charge * $tax) / 100);
            $couponDiscount = floatval($order->voucher_amount ?? 0);
            // Apply referral and coupon discounts (from existing order data)
            $referralDiscount = floatval($order->referral_discount_amount ?? 0);

            // Fetch order details
            $orderDetailsTotal_price = OrderDetails::find()->where(['order_id' => $orderId])->sum('total_price');
            $orderDetailsSubTotal = $orderDetailsTotal_price - $couponDiscount;
            $comboOrdersSubTotal = ComboOrder::find()->where(['order_id' => $orderId])->sum('amount');
            $subTotal = $orderDetailsSubTotal + $comboOrdersSubTotal - $referralDiscount - $order_discounts;
            $subTotalAfterDiscounts = $subTotal;
            // Update order fields
            $order->sub_total = number_format($subTotal, 2, '.', '');
            $order->referral_discount_amount = $referralDiscount;
            $order->voucher_amount = $couponDiscount;
            $order->cgst = floatval($cgst ?? 0);
            $order->sgst = floatval($sgst ?? 0);
            $order->tax = $order->cgst + $order->sgst;
            // Calculate taxable total (tip + processing charges + service charge with tax)
            $order->service_charge = floatval($service_charge ?? 0);
            $order->processing_charges = floatval($order->processing_charges ?? 0);
            $order->service_charge_w_tax = floatval($service_charge_w_tax ?? 0);
            $order->tip_amt = floatval($order->tip_amt ?? 0);
            $order->taxable_total = number_format(
                $order->tip_amt + $order->processing_charges + $order->service_charge_w_tax,
                2,
                '.',
                ''
            );
            $order->service_charge_tax_amt = !empty($order->service_charge_w_tax) ? $order->service_charge_w_tax - $order->service_charge : 0;

            // Calculate tax on subtotal
            $taxRate = $tax; // e.g., 18 (CGST + SGST)
            $taxOnSubTotal = round(($subTotalAfterDiscounts * $taxRate) / 100, 2);
            $order->Subtotal_tax = $taxOnSubTotal;

            // Calculate total with tax
            $order->total_w_tax = number_format(
                floatval($subTotalAfterDiscounts) +
                    floatval($order->taxable_total) +
                    floatval($taxOnSubTotal),
                2,
                '.',
                ''
            );
            $order->save(false);

            $order_transaction_details_sum = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id])
                ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount') ?? 0;
            if (empty($order_transaction_details_sum)) {
                $order_transaction_details = 0;
            } else {
                $order_transaction_details = $order_transaction_details_sum;
            }

            $payable_amount = $order->total_w_tax - $order_transaction_details;

            // Ensure payable amount is not negative
            if ($payable_amount < 0) {
                $payable_amount = 0;
            }

            // Handle payment mode and advance payment
            $order->payable_amount = number_format($payable_amount, 2, '.', '');
            $order->balance_amount = number_format($payable_amount, 2, '.', '');
            $order->save(false);
        }
    }

    public static function getProductTransactionSum($product_order_id)
    {

        $order_transaction_details_sum = OrderTransactionDetails::find()
            ->where(['product_order_id' => $product_order_id])
            ->andWhere(['order_type' => OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER])
            ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->sum('amount') ?? 0;

        return $order_transaction_details_sum;
    }
    public static function getOrdersTransactionSum($order_id)
    {

        $order_transaction_details_sum = OrderTransactionDetails::find()
            ->where(['order_id' => $order_id])
            ->andWhere(['order_type' => OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER])
            ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->sum('amount') ?? 0;

        return $order_transaction_details_sum;
    }

    public static function recalculateProductOrders($product_order_id)
    {
        $product_orders = ProductOrders::find()->where(['id' => $product_order_id])->one();
        if (!empty($product_orders)) {
            $getProductTransactionSum = self::getProductTransactionSum($product_order_id);
            $sub_total = $product_orders->sub_total;
            $tax_percentage = $product_orders->tax_percentage;
            $tax_amount = $product_orders->tax_amount;
            $total_with_tax = $product_orders->total_with_tax;
            $discount_amount = ProductOrdersHasDiscounts::find()->where(['product_order_id' => $product_order_id])->sum('discount_amount');
            $product_order_items = ProductOrderItems::find()->where(['product_order_id' => $product_order_id])->all();
            $product_order_items_assigned_discounts = ProductOrderItemsAssignedDiscounts::find()->innerJoinWith(['productOrderItem pi'])->where(['pi.product_order_id' => $product_order_id])->sum('discount_amount');
            $total_discount = $discount_amount + $product_order_items_assigned_discounts;
            if (!empty($product_order_items)) {
                foreach ($product_order_items as $item) {
                    $quantity = $item->quantity;
                    $selling_price = $item->selling_price;
                    $sub_total += $quantity * $selling_price;
                    $tax_percentage += $item->tax_percentage;
                    $tax_amount += ($quantity * $selling_price * $item->tax_percentage) / 100;
                    $total_with_tax += $quantity * $selling_price + ($quantity * $selling_price * $item->tax_percentage) / 100;
                    $item->sub_total = $quantity * $selling_price;
                    $item->tax_amount = ($quantity * $selling_price * $item->tax_percentage) / 100;
                    $item->total_with_tax = $item->sub_total + $item->tax_amount;
                    $item->save(false);
                }
            }
            $product_order_item_sub_total_sum = array_sum(array_column($product_order_items, 'selling_price')) - $total_discount;
            $sub_total = array_sum(array_column($product_order_items, 'selling_price'));
            $product_orders->sub_total = $sub_total;
            $product_orders->tax_percentage = $tax_percentage;
            $tax_amount = $sub_total * $tax_percentage / 100;
            $product_orders->tax_amount = $tax_amount;
            $product_orders->total_with_tax = $product_order_item_sub_total_sum + $product_orders->tax_amount;
            $payable_amount = $product_orders->total_with_tax - $getProductTransactionSum;
            if ($payable_amount < 0) {
                $payable_amount = 0;
            }
            $product_orders->payable_amount = number_format($payable_amount, 2, '.', '');
            $product_orders->save(false);
        }
    }





    public static function recalculateOrderPriceService($orderId)
    {


        // Find the order
        $order = Orders::findOne(['id' => $orderId]);
        if (!empty($order)) {
            $settings = new WebSetting();
            $tax = $settings->getSettingBykey('tax') ?? 0;
            $cgst = $tax / 2;
            $sgst = $tax / 2;
            $order_discounts = OrderDiscounts::find()->where(['order_id' => $orderId])->sum('discount_amount');
            $conv_fee = $settings->getSettingBykey('conv_fee');
            $shopDetails = VendorDetails::findOne(['id' => $order->vendor_details_id]);
            $service_charge = !empty($shopDetails->min_service_fee) ? $shopDetails->min_service_fee : $conv_fee;
            $service_charge_w_tax = $service_charge + (($service_charge * $tax) / 100);
            $couponDiscount = floatval($order->voucher_amount ?? 0);
            // Apply referral and coupon discounts (from existing order data)
            $referralDiscount = floatval($order->referral_discount_amount ?? 0);

            // Fetch order details
            $orderDetailsTotal_price = OrderDetails::find()->where(['order_id' => $orderId])->sum('total_price');
            $orderDetailsSubTotal = $orderDetailsTotal_price - $couponDiscount;
            $comboOrdersSubTotal = ComboOrder::find()->where(['order_id' => $orderId])->sum('amount');
            $subTotal = $orderDetailsSubTotal + $comboOrdersSubTotal - $referralDiscount - $order_discounts;
            $subTotalAfterDiscounts = $subTotal;
            // Update order fields
            $order->sub_total = number_format($subTotal, 2, '.', '');
            $order->referral_discount_amount = $referralDiscount;
            $order->voucher_amount = $couponDiscount;
            $order->cgst = floatval($cgst ?? 0);
            $order->sgst = floatval($sgst ?? 0);
            $order->tax = $order->cgst + $order->sgst;
            // Calculate taxable total (tip + processing charges + service charge with tax)
            $order->service_charge = floatval($service_charge ?? 0);
            $order->processing_charges = floatval($order->processing_charges ?? 0);
            $order->service_charge_w_tax = floatval($service_charge_w_tax ?? 0);
            $order->tip_amt = floatval($order->tip_amt ?? 0);
            $order->taxable_total = number_format(
                $order->tip_amt + $order->processing_charges + $order->service_charge_w_tax,
                2,
                '.',
                ''
            );
            $order->service_charge_tax_amt = !empty($order->service_charge_w_tax) ? $order->service_charge_w_tax - $order->service_charge : 0;

            // Calculate tax on subtotal
            $taxRate = $tax; // e.g., 18 (CGST + SGST)
            $taxOnSubTotal = round(($subTotalAfterDiscounts * $taxRate) / 100, 2);
            $order->Subtotal_tax = $taxOnSubTotal;

            // Calculate total with tax
            $order->total_w_tax = number_format(
                floatval($subTotalAfterDiscounts) +
                    floatval($order->taxable_total) +
                    floatval($taxOnSubTotal),
                2,
                '.',
                ''
            );
            $order->save(false);

            $order_transaction_details_sum = self::getOrdersTransactionSum($order->id);
            if (empty($order_transaction_details_sum)) {
                $order_transaction_details = 0;
            } else {
                $order_transaction_details = $order_transaction_details_sum;
            }

            $payable_amount = $order->total_w_tax - $order_transaction_details;

            // Ensure payable amount is not negative
            if ($payable_amount < 0) {
                $payable_amount = 0;
            }

            // Handle payment mode and advance payment
            $order->payable_amount = number_format($payable_amount, 2, '.', '');
            $order->balance_amount = number_format($payable_amount, 2, '.', '');
            $order->save(false);
        }
    }





    public static function recalculateOrderPrice($orderId, $order_type)
    {
        if (!empty($orderId) && $order_type == OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER) {
            self::recalculateOrderPriceService($orderId);
        }
        if (!empty($orderId) && $order_type == OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER) {
            self::recalculateProductOrders($orderId);
        }
    }





    public static function markChildOrdersAsPaid($orderId)
    {
        $order = Orders::findOne($orderId);

        if ($order && !empty($order->parent_order_id)) {
            $childOrders = Orders::find()
                ->where(['parent_order_id' => $order->parent_order_id])
                ->all();

            if (!empty($childOrders)) {
                foreach ($childOrders as $childOrder) {
                    // Check if sub_total is null, empty, zero, or less than zero
                    if (empty($childOrder->sub_total) || floatval($childOrder->sub_total) <= 0) {
                        $childOrder->payment_status = Orders::PAYMENT_DONE;
                        $childOrder->fill_payment_status  = Orders::FULL_PAYMENT_STATUS_DONE;
                        $childOrder->save(false);
                    }
                }
            }
        }
    }







    public function getProductServiceOrderMappings()
    {
        return $this->hasOne(\app\modules\admin\models\ProductServiceOrderMappings::className(), ['order_id' => 'id']);
    }














    public function orderStatus($status = '', $date = '')
    {

        if (Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $vendor_details = VendorDetails::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['status' => VendorDetails::STATUS_ACTIVE])->one();
            $vendor_details_id = $vendor_details['id'];
            if (!empty($status) && empty($date)) {
                $query = Orders::find();
                $query->where(['status' => $status])->andWhere(['vendor_details_id' => $vendor_details_id]);
                return $query->count();
            } else if (!empty($date) && !empty($status)) {
                $query = Orders::find()->andWhere(['vendor_details_id' => $vendor_details_id]);
                $query->where(['status' => $status]);
                $query->andWhere(['between', 'created_on', $date, date('Y-m-d 23:59:59')]);
                return $query->count();
            } else {
                $query = Orders::find()->andWhere(['vendor_details_id' => $vendor_details_id['id']]);
                return $query->count();
            }
        } else {

            if (!empty($status) && empty($date)) {
                $query = Orders::find();
                $query->where(['status' => $status]);
                return $query->count();
            } else if (!empty($date) && !empty($status)) {
                $query = Orders::find();
                $query->where(['status' => $status]);
                $query->andWhere(['between', 'created_on', $date, date('Y-m-d 23:59:59')]);
                return $query->count();
            } else {
                $query = Orders::find();
                return $query->count();
            }
        }
    }








    public function asJson()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['store_latitude'] = $this->vendorDetails->latitude ?? '';
        $data['store_longitude'] = $this->vendorDetails->longitude ?? '';
        $data['is_scheduled_next_visit'] = $this->next_visit_required  ?? 0;
        $data['cancel_reason'] = $this->cancel_reason;
        $data['cancel_description'] = $this->cancel_description;
        $data['otp'] = $this->otp;
        $data['rating_flag'] = $this->rating_flag;
        $data['status_step'] = $this->status_step;


        $data['user_id'] = $this->user_id;
        if (!empty($this->user)) {
            $data['user_details'] = $this->user->asJsonUserForOrder();
        } else {
            $data['user_details'] = (object)[];
        }

        $data['json_details'] = $this->json_details;
        $data['qty'] = $this->qty;
        $data['trans_type'] = $this->trans_type;
        $data['service_type'] = $this->service_type;
        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);
        $data['sub_total'] = $this->sub_total;
        $data['tip_amt'] = $this->tip_amt;
        $data['tax'] = $this->tax;
        $data['processing_charges'] = $this->processing_charges;
        $data['service_charge'] = (float)number_format($this->service_charge, 2, '.', '');
        $data['taxable_total'] = (float) number_format($this->taxable_total, 2, '.', '');
        $data['total_w_tax'] = (float) number_format($this->total_w_tax, 2, '.', '');
        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');

        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = (float)number_format($this->payable_amount, 2, '.', '');
        $data['balance_amount'] = (float)number_format($this->balance_amount, 2, '.', '');



        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['Subtotal_tax'] = (float)number_format($this->Subtotal_tax, 2, '.', '');
        $data['taxable_total'] = (float)number_format($this->taxable_total, 2, '.', '');
        $data['service_charge_tax_amt'] = $this->service_charge_tax_amt ?? 0;


        $schedule_date_time = $data['schedule_date'] . ' ' . $data['schedule_time'];
        $datetime1 = new DateTime();
        $datetime2 = new DateTime($schedule_date_time);
        $interval = $datetime1->diff($datetime2);
        $data['interval'] = $interval;

        $data['service_instruction'] = $this->service_instruction;

        if (!empty($this->service_address)) {
            $service_location = DeliveryAddress::find()->where(['id' => $this->service_address])->one();
            if (!empty($service_location)) {
                $data['service_address'] = ($service_location->address ?? '') . ' ' . ($service_location->location ?? '');
                $data['service_lat'] = $service_location->latitude ?? '';
                $data['service_lng'] = $service_location->longitude ?? '';
            } else {
                $data['service_address'] = '';
                $data['service_lat'] = '';
                $data['service_lng'] = '';
            }
        } else {
            $data['service_address'] = $this->service_address;
            $data['service_lat'] = '';
            $data['service_lng'] = '';
        }

        $data['voucher_code'] = $this->voucher_code;
        $data['voucher_amount'] = $this->voucher_amount;
        $data['voucher_type'] = $this->voucher_type;
        $data['payment_status'] = $this->payment_status;
        $data['ip_ress'] = $this->ip_ress;
        $data['otp'] = $this->otp;
        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;

        $data['is_otp_verify'] = ($this->is_verify == self::OTP_VERIFIED);

        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();


        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'][] = (object)[];
        }

        $checkReviewStatus = ShopReview::findOne([
            'vendor_details_id' => $this->vendor_details_id,
            'user_id' => $this->user_id,
            'order_id' => $this->id,
        ]);

        $data['shop_review_status'] = $checkReviewStatus ? true : false;
        $data['order_review'] = $checkReviewStatus ? $checkReviewStatus->asJson() : '';




        $total_duration = [];

        foreach ($data['order_details'] ?? [] as $order_details) {
            if (isset($order_details->service_details) && isset($order_details->service_details->duration_int)) {
                $total_duration[] = (int) $order_details->service_details->duration_int;
            }
        }


        if (!empty($total_duration)) {
            $data['total_duration_minutes'] = array_sum($total_duration);
            $data['total_duration_hours'] = intdiv($data['total_duration_minutes'], 60) . ':' . ($data['total_duration_minutes'] % 60);
        } else {
            $data['total_duration_minutes'] = false;
            $data['total_duration_hours'] = false;
        }

        if (!empty($this->homeVisitorsHasOrders)) {
            $data['homeVisitorsHasOrders'] = $this->homeVisitorsHasOrders->asJson();
        } else {
            $data['homeVisitorsHasOrders'] = '';
        }
        $data['is_next_visit'] = $this->is_next_visit;

        $next_visit_details = NextVisitDetails::find()->where(['order_id' => $this->id])->all();
        if (!empty($next_visit_details)) {
            foreach ($next_visit_details as $next_visit_details_data) {
                $data['next_visit_details_data'][] = $next_visit_details_data->asJson();
            }
        } else {
            $data['next_visit_details_data'] = [];
        }


        $qrData = [
            'order_id' => $this->id
        ];

        $jsonQrData = json_encode($qrData);



        $qrResult = Builder::create()
            ->writer(new PngWriter())
            ->data($jsonQrData) // Now QR code has full object
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(250)
            ->margin(2)
            ->build();

        $data['qr_code'] = 'data:image/png;base64,' . base64_encode($qrResult->getString());




        $order_transaction_details = OrderTransactionDetails::find()->where(['order_id' => $this->id])
            ->andWHere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->all();
        if (!empty($order_transaction_details)) {
            foreach ($order_transaction_details as $order_transaction_detail) {
                $data['order_transaction_details'][] = $order_transaction_detail->asJson();
            }
        } else {
            $data['order_transaction_details'] = [];
        }



        return $data;
    }



    public function requiredUpdateServiceProducts($order_id)
    {
        try {
            // Validate order_id
            if (empty($order_id)) {
                Yii::error("Order ID is required.", __METHOD__);
                return false;
            }

            $order_details = OrderDetails::find()
                ->select('service_id')
                ->where(['order_id' => $order_id])
                ->asArray()
                ->all();

            if (empty($order_details)) {
                Yii::warning("No order details found for order_id: $order_id", __METHOD__);
                return false;
            }

            $serviceIds = array_column($order_details, 'service_id');
            if (empty($serviceIds)) {
                Yii::warning("No service IDs found for order_id: $order_id", __METHOD__);
                return false;
            }

            $product_services = ProductServices::find()
                ->where(['order_id' => $order_id])
                ->andWhere(['service_id' => $serviceIds])
                ->all();

            return !empty($product_services);
        } catch (\Throwable $e) {
            Yii::error("Error in requiredUpdateServiceProducts for order_id $order_id: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }







    public function asJsonVendor()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['store_latitude'] = $this->vendorDetails->latitude ?? '';
        $data['store_longitude'] = $this->vendorDetails->longitude ?? '';
        $data['is_scheduled_next_visit'] = $this->next_visit_required  ?? 0;
        $data['cancel_reason'] = $this->cancel_reason;
        $data['cancel_description'] = $this->cancel_description;
        $data['otp'] = $this->otp;
        $data['rating_flag'] = $this->rating_flag;
        $data['user_id'] = $this->user_id;
        $data['json_details'] = $this->json_details;
        $data['qty'] = $this->qty;
        $data['trans_type'] = $this->trans_type;
        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);
        $data['sub_total'] = $this->sub_total;
        $data['tip_amt'] = $this->tip_amt;
        $data['tax'] = $this->tax;
        $data['processing_charges'] = $this->processing_charges;
        $data['service_charge'] = (float)number_format($this->service_charge, 2, '.', '');
        $data['taxable_total'] = (float) number_format($this->taxable_total, 2, '.', '');
        $data['total_w_tax'] = (float) number_format($this->total_w_tax, 2, '.', '');
        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = (float)number_format($this->payable_amount, 2, '.', '');
        $data['balance_amount'] = (float)number_format($this->balance_amount, 2, '.', '');
        $data['status_step'] = $this->status_step;
        $data['required_update_service_products'] = $this->requiredUpdateServiceProducts($this->id);

        $data['user_id'] = $this->user_id;
        if (!empty($this->user)) {
            $data['user_details'] = $this->user->asJsonUserForOrder();
        } else {
            $data['user_details'] = (object)[];
        }



        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();


        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'] = [];
        }




        $total_duration = [];

        foreach ($data['order_details'] ?? [] as $order_details) {
            if (isset($order_details->service_details) && isset($order_details->service_details->duration_int)) {
                $total_duration[] = (int) $order_details->service_details->duration_int;
            }
        }

        $order_transaction_details = OrderTransactionDetails::find()->where(['order_id' => $this->id])
            ->andWHere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->all();
        if (!empty($order_transaction_details)) {
            foreach ($order_transaction_details as $order_transaction_detail) {
                $data['order_transaction_details'][] = $order_transaction_detail->asJson();
            }
        } else {
            $data['order_transaction_details'] = [];
        }

        if (!empty($this->homeVisitorsHasOrders)) {
            $data['homeVisitorsHasOrders'] = $this->homeVisitorsHasOrders->asJson();
        } else {
            $data['homeVisitorsHasOrders'] = (object)[];
        }




        return $data;
    }





    public function asJsonMyOrdersUser()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['store_latitude'] = $this->vendorDetails->latitude ?? '';
        $data['store_longitude'] = $this->vendorDetails->longitude ?? '';
        $data['otp'] = $this->otp;
        $data['qty'] = $this->qty;
        $data['trans_type'] = $this->trans_type;
        $data['service_type'] = $this->service_type;
        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);
        $data['sub_total'] = $this->sub_total;
        $data['tip_amt'] = $this->tip_amt;
        $data['tax'] = $this->tax;
        $data['processing_charges'] = $this->processing_charges;
        $data['service_charge'] = (float)number_format($this->service_charge, 2, '.', '');
        $data['taxable_total'] = (float) number_format($this->taxable_total, 2, '.', '');
        $data['total_w_tax'] = (float) number_format($this->total_w_tax, 2, '.', '');
        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = (float)number_format($this->payable_amount, 2, '.', '');
        $data['balance_amount'] = $this->balance_amount;
        $data['status_step'] = $this->status_step;

        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['Subtotal_tax'] = (float)number_format($this->Subtotal_tax, 2, '.', '');
        $data['taxable_total'] = (float)number_format($this->taxable_total, 2, '.', '');
        $data['service_charge_tax_amt'] = $this->service_charge_tax_amt ?? 0;
        $data['voucher_code'] = $this->voucher_code;
        $data['voucher_amount'] = $this->voucher_amount;
        $data['voucher_type'] = $this->voucher_type;
        $data['payment_status'] = $this->payment_status;
        $data['ip_ress'] = $this->ip_ress;
        $data['otp'] = $this->otp;
        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['is_otp_verify'] = ($this->is_verify == self::OTP_VERIFIED);
        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();
        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'][] = (object)[];
        }

        $shop_review = ShopReview::findOne(['order_id' => $this->id]);
        if (!empty($shop_review)) {
            $data['order_shop_review'] = true;
            $data['shop_review'] = $shop_review;
        } else {
            $data['order_shop_review'] = false;
            $data['shop_review'] = $shop_review;
        }




        return $data;
    }







    public function asJsonMyOrdersUserInModel()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['store_latitude'] = $this->vendorDetails->latitude ?? '';
        $data['store_longitude'] = $this->vendorDetails->longitude ?? '';
        $data['qty'] = $this->qty;
        $data['trans_type'] = $this->trans_type;
        $data['service_type'] = $this->service_type;
        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);
        $data['sub_total'] = $this->sub_total;
        $data['tip_amt'] = $this->tip_amt;
        $data['tax'] = $this->tax;
        $data['processing_charges'] = $this->processing_charges;
        $data['service_charge'] = (float)number_format($this->service_charge, 2, '.', '');
        $data['taxable_total'] = (float) number_format($this->taxable_total, 2, '.', '');
        $data['total_w_tax'] = (float) number_format($this->total_w_tax, 2, '.', '');
        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;

        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = $this->payable_amount;
        $data['balance_amount'] = $this->balance_amount;
        $data['rating_flag'] = $this->rating_flag;
        $data['status_step'] = $this->status_step;

        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['Subtotal_tax'] = (float)number_format($this->Subtotal_tax, 2, '.', '');
        $data['taxable_total'] = (float)number_format($this->taxable_total, 2, '.', '');
        $data['service_charge_tax_amt'] = $this->service_charge_tax_amt ?? 0;
        $data['voucher_code'] = $this->voucher_code;
        $data['voucher_amount'] = $this->voucher_amount;
        $data['voucher_type'] = $this->voucher_type;
        $data['payment_status'] = $this->payment_status;
        $data['ip_ress'] = $this->ip_ress;
        $data['otp'] = $this->otp;
        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['is_otp_verify'] = ($this->is_verify == self::OTP_VERIFIED);
        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();
        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'][] = (object)[];
        }




        return $data;
    }










    public function partialPaymentOrdersasJson()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = number_format($this->payable_amount, 2, '.', '');
        $data['total_w_tax'] = number_format($this->total_w_tax, 2, '.', '');
        $data['balance_amount'] = number_format($this->balance_amount, 2, '.', '');
        $data['status_step'] = $this->status_step;

        $data['order_details'] = [];
        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();

        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->partialPaymentOrdersAsJsonOrderDetails();
            }
        } else {
            $data['order_details'][] = (object)[];
        }

        return $data;
    }




    public function asJsonEarningsDetails()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['user_id'] = $this->user_id;
        if (!empty($this->user)) {
            $data['user_details'] = $this->user->asJsonUser();
        } else {
            $data['user_details'] = (object) [];
        }
        $data['qty'] = $this->qty;

        $data['trans_type'] = $this->trans_type;
        $data['service_type'] = $this->service_type;

        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);

        $data['sub_total'] = $this->sub_total;

        $data['tip_amt'] = $this->tip_amt;

        $data['tax'] = $this->tax;

        $data['processing_charges'] = $this->processing_charges;

        $data['service_charge'] = $this->service_charge;

        $data['taxable_total'] = $this->taxable_total;

        $data['total_w_tax'] = $this->total_w_tax;

        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;


        $data['schedule_date'] = $this->schedule_date;

        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');

        $schedule_date_time =  $data['schedule_date'] . ' ' . $data['schedule_time'];
        $data['status_step'] = $this->status_step;



        $data['voucher_code'] = $this->voucher_code;

        $data['voucher_amount'] = $this->voucher_amount;

        $data['voucher_type'] = $this->voucher_type;

        $data['payment_status'] = $this->payment_status;

        $data['ip_ress'] = $this->ip_ress;

        $data['payable_amount'] = $this->payable_amount;

        $data['balance_amount'] = $this->balance_amount;
        $orderDetails = OrderDetails::find()->Where(['order_id' => $this->id])->all();
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'] = (object) [];
        }



        return $data;
    }




    public function asJsonReview()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = $this->payable_amount;
        $data['balance_amount'] = $this->balance_amount;
        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();
        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'][] = (object)[];
        }
        return $data;
    }


    public function asJsonForVendorEarnings()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['customer_name'] = $this->user->first_name ?? $this->user->contact_no;

        $data['service_type'] = $this->service_type;
        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();
        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJsonForVendorEarnings();
            }
        } else {
            $data['order_details'][] = (object)[];
        }
        return $data;
    }









    public function asJsonViewById()
    {
        $data = [];
        $data['order_id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['vendorName'] = $this->vendorDetails->business_name ?? '';
        $data['store_address'] = $this->vendorDetails->address ?? '';
        $data['store_latitude'] = $this->vendorDetails->latitude ?? '';
        $data['store_longitude'] = $this->vendorDetails->longitude ?? '';
        $data['is_scheduled_next_visit'] = $this->next_visit_required  ?? 0;
        $data['cancel_reason'] = $this->cancel_reason;
        $data['cancel_description'] = $this->cancel_description;
        $data['otp'] = $this->otp;
        $data['rating_flag'] = $this->rating_flag;
        $data['status_step'] = $this->status_step;
        $data['parent_order_id'] = $this->parent_order_id;
        if(empty($this->total_w_tax)){
            $support_order = Orders::find()->where(['parent_order_id'=>$this->parent_order_id])->orderBy(['id' => SORT_ASC])->one();
            $data['support_order']= $support_order->id??'';

        }else{
            $data['support_order']='';

        }


        $data['user_id'] = $this->user_id;
        if (!empty($this->user)) {
            $data['user_details'] = $this->user->asJsonUserForOrder();
        } else {
            $data['user_details'] = (object)[];
        }

        $data['json_details'] = $this->json_details;
        $data['qty'] = $this->qty;
        $data['trans_type'] = $this->trans_type;
        $data['service_type'] = $this->service_type;
        $data['payment_type'] = $this->assignPaymentModeByOrder($this->id);
        $data['sub_total'] = $this->sub_total;
        $data['tip_amt'] = $this->tip_amt;
        $data['tax'] = $this->tax;
        $data['processing_charges'] = $this->processing_charges;
        $data['service_charge'] = (float)number_format($this->service_charge, 2, '.', '');
        $data['taxable_total'] = (float) number_format($this->taxable_total, 2, '.', '');
        $data['total_w_tax'] = (float) number_format($this->total_w_tax, 2, '.', '');
        $data['status'] = $this->status;
        $data['current_status'] = !empty($this->current_status) ? $this->current_status : self::CURRENT_STATUS_ACTIVE;
        $data['schedule_date'] = $this->schedule_date;
        $data['schedule_time'] = (new \DateTime($this->schedule_time))->format('H:i');
        $data['service_type'] = $this->service_type;
        $data['payable_amount'] = (float)number_format($this->payable_amount, 2, '.', '');
        $data['balance_amount'] = (float)number_format($this->balance_amount, 2, '.', '');
        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;
        $data['Subtotal_tax'] = (float)number_format($this->Subtotal_tax, 2, '.', '');
        $data['taxable_total'] = (float)number_format($this->taxable_total, 2, '.', '');
        $data['service_charge_tax_amt'] = $this->service_charge_tax_amt ?? 0;
        $schedule_date_time = $data['schedule_date'] . ' ' . $data['schedule_time'];
        $datetime1 = new DateTime();
        $datetime2 = new DateTime($schedule_date_time);
        $interval = $datetime1->diff($datetime2);
        $data['interval'] = $interval;
        $data['service_instruction'] = $this->service_instruction;

        if (!empty($this->service_address)) {
            $service_location = DeliveryAddress::find()->where(['id' => $this->service_address])->one();
            if (!empty($service_location)) {
                $data['service_address'] = ($service_location->address ?? '') . ' ' . ($service_location->location ?? '');
                $data['service_lat'] = $service_location->latitude ?? '';
                $data['service_lng'] = $service_location->longitude ?? '';
            } else {
                $data['service_address'] = '';
                $data['service_lat'] = '';
                $data['service_lng'] = '';
            }
        } else {
            $data['service_address'] = $this->service_address;
            $data['service_lat'] = '';
            $data['service_lng'] = '';
        }

        $data['voucher_code'] = $this->voucher_code;
        $data['voucher_amount'] = $this->voucher_amount;
        $data['voucher_type'] = $this->voucher_type;
        $data['payment_status'] = $this->payment_status;
        $data['ip_ress'] = $this->ip_ress;
        $data['otp'] = $this->otp;
        $data['cgst'] = $this->cgst;
        $data['sgst'] = $this->sgst;

        $data['is_otp_verify'] = ($this->is_verify == self::OTP_VERIFIED);

        $orderDetails = OrderDetails::find()->where(['order_id' => $this->id])->all();


        $data['order_details'] = [];
        if (!empty($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $data['order_details'][] = $orderDetail->asJson();
            }
        } else {
            $data['order_details'][] = (object)[];
        }

        $checkReviewStatus = ShopReview::findOne([
            'vendor_details_id' => $this->vendor_details_id,
            'user_id' => $this->user_id,
            'order_id' => $this->id,
        ]);

        $data['shop_review_status'] = $checkReviewStatus ? true : false;
        $data['order_review'] = $checkReviewStatus ? $checkReviewStatus->asJson() : '';




        $total_duration = [];

        foreach ($data['order_details'] ?? [] as $order_details) {
            if (isset($order_details->service_details) && isset($order_details->service_details->duration_int)) {
                $total_duration[] = (int) $order_details->service_details->duration_int;
            }
        }


        if (!empty($total_duration)) {
            $data['total_duration_minutes'] = array_sum($total_duration);
            $data['total_duration_hours'] = intdiv($data['total_duration_minutes'], 60) . ':' . ($data['total_duration_minutes'] % 60);
        } else {
            $data['total_duration_minutes'] = false;
            $data['total_duration_hours'] = false;
        }

        if (!empty($this->homeVisitorsHasOrders)) {
            $data['homeVisitorsHasOrders'] = $this->homeVisitorsHasOrders->asJson();
        } else {
            $data['homeVisitorsHasOrders'] = '';
        }
        $data['is_next_visit'] = $this->is_next_visit;

        $next_visit_details = NextVisitDetails::find()->where(['order_id' => $this->id])->all();
        if (!empty($next_visit_details)) {
            foreach ($next_visit_details as $next_visit_details_data) {
                $data['next_visit_details_data'][] = $next_visit_details_data->asJson();
            }
        } else {
            $data['next_visit_details_data'] = [];
        }


        $qrData = [
            'order_id' => $this->id
        ];

        $jsonQrData = json_encode($qrData);



        $qrResult = Builder::create()
            ->writer(new PngWriter())
            ->data($jsonQrData) // Now QR code has full object
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(250)
            ->margin(2)
            ->build();

        $data['qr_code'] = 'data:image/png;base64,' . base64_encode($qrResult->getString());


        $data['productServiceOrderMappings'] = $this->productServiceOrderMappings ? $this->productServiceOrderMappings->asJson() : '';


        $order_transaction_details = OrderTransactionDetails::find()->where(['order_id' => $this->id])
            ->andWHere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->all();
        if (!empty($order_transaction_details)) {
            foreach ($order_transaction_details as $order_transaction_detail) {
                $data['order_transaction_details'][] = $order_transaction_detail->asJson();
            }
        } else {
            $data['order_transaction_details'] = [];
        }
        return $data;
    }
}

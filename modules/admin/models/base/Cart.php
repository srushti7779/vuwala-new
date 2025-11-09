<?php


namespace app\modules\admin\models\base;

use app\modules\admin\models\base\Services;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\ComboPackagesCart;
use app\modules\admin\models\WebSetting;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "cart".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $vendor_details_id
 * @property integer $quantity
 * @property double $amount
 * @property double $tip
 * @property double $wallet 
 * @property string $service_instructions
 * @property string $details
 * @property double $cgst
 * @property double $sgst
 * @property string $coupon_code
 * @property double $coupon_discount
 * @property integer $coupon_applied_id
 * @property double $service_fees
 * @property double $other_charges
 * @property integer $status
 * @property integer $service_address
 * @property string $service_time
 * @property string $service_date
 * @property integer $type_id
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\CartItems[] $cartItems
 * @property \app\modules\admin\models\CouponsApplied[] $couponsApplieds
 */
class Cart extends \yii\db\ActiveRecord
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
            'vendorDetails',
            'createUser',
            'updateUser',
            'cartItems',
            'couponsApplieds'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const PAYMENT_MODE_FULL = 1;
    const PAYMENT_MODE_PARTIAL = 2;




    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'vendor_details_id', 'quantity'], 'required'],
            [['user_id', 'vendor_details_id', 'quantity', 'coupon_applied_id', 'status', 'service_address', 'type_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['amount', 'tip', 'wallet', 'cgst', 'sgst', 'coupon_discount', 'service_fees', 'other_charges'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
            [['service_instructions', 'details', 'coupon_code', 'service_time', 'service_date'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cart';
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'amount' => Yii::t('app', 'Amount'),
            'tip' => Yii::t('app', 'Tip'),
            'wallet' => Yii::t('app', 'Wallet'),
            'service_instructions' => Yii::t('app', 'Service Instructions'),
            'details' => Yii::t('app', 'Details'),
            'cgst' => Yii::t('app', 'Cgst'),
            'sgst' => Yii::t('app', 'Sgst'),
            'coupon_code' => Yii::t('app', 'Coupon Code'),
            'coupon_discount' => Yii::t('app', 'Coupon Discount'),
            'coupon_applied_id' => Yii::t('app', 'Coupon Applied ID'),
            'service_fees' => Yii::t('app', 'Service Fees'),
            'other_charges' => Yii::t('app', 'Other Charges'),
            'status' => Yii::t('app', 'Status'),
            'service_address' => Yii::t('app', 'Service Address'),
            'service_time' => Yii::t('app', 'Service Time'),
            'service_date' => Yii::t('app', 'Service Date'),
            'type_id' => Yii::t('app', 'Type ID'),
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
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
    public function getCartItems()
    {
        return $this->hasMany(\app\modules\admin\models\CartItems::className(), ['cart_id' => 'id']);
    }
    public function getComboPackage()
    {
        return $this->hasOne(ComboPackages::class, ['id' => 'combo_package_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponsApplieds()
    {
        return $this->hasMany(\app\modules\admin\models\CouponsApplied::className(), ['cart_id' => 'id']);
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
     * @return \app\modules\admin\models\CartQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\CartQuery(get_called_class());
    }




    public static function hasPriceRangeService($cart_id)
    {
        $cartItems = CartItems::find()
            ->alias('ci')
            ->joinWith('serviceItem s')
            ->where(['ci.cart_id' => $cart_id])
            ->andWhere(['ci.is_package_service' => 0])
            ->all();

        foreach ($cartItems as $item) {
            // If any item has a price range (from_price is not empty), return false
            if (!empty($item->serviceItem->from_price)) {
                return false;
            }
        }

        return true; // No items with price range found
    }


    public static function resetCartItems($user_id)
    {
        $cart_items = CartItems::findAll(['user_id' => $user_id, 'is_package_service' => 0]);
        if (!empty($cart_items)) {
            foreach ($cart_items as $item) {
                $item->delete();
            }
        }
    }


    public static function updateCartTotalsByUser($user_id)
    {
        // Get the user's cart
        $cart = Cart::findOne(['user_id' => $user_id]);
        if (!empty($cart)) {

            $settings = new WebSetting();
            $tax = $settings->getSettingBykey('tax') ?? 0;
            $cgst = $tax / 2;
            $sgst = $tax / 2;

            // 1. Calculate service & package totals
            $combo_packages_cart_sum = ComboPackagesCart::find()
                ->where(['user_id' => $user_id])
                ->sum('amount');
            $cart_items_sum = CartItems::find()
                ->where(['user_id' => $user_id])
                ->sum('amount');


            $combo_packages_cart_qty = ComboPackagesCart::find()
                ->where(['user_id' => $user_id])
                ->count();

            $cart_items_qty = CartItems::find()
                ->where(['cart_id' => $cart->id])
                ->andWhere(['is_package_service' => 0])
                ->count();

            $cart_amount = ($cart_items_sum ?: 0) + $cart->package_amount;

            // 2. Set cart base values
            $cart->package_amount = $combo_packages_cart_sum ?: 0;
            $cart->amount = $cart_amount;

            // 3. Taxes
            $cart->cgst = $cgst;
            $cart->sgst = $sgst;
            $cart->tax = $cgst + $sgst;

            // 4. Referral discount
            $referral_discount_amount = Orders::calculateReferralDiscount($user_id, $cart_amount);
            $cart->referral_discount_percentage = $settings->getSettingBykey('referral_discount_percentage') ?? 0;
            $cart->referral_discount_amount = $referral_discount_amount;

            // 5. Service fee and tax
            $conv_fee = $settings->getSettingBykey('conv_fee');
            $shopDetails = VendorDetails::findOne(['id' => $cart->vendor_details_id]);
            $cart->service_fees = !empty($shopDetails->min_service_fee) ? $shopDetails->min_service_fee : $conv_fee;

            $serviceFeeTax = number_format(($cart->service_fees * $tax) / 100, 2, '.', '');
            $cart->service_fees_with_tax = number_format($cart->service_fees + $serviceFeeTax, 2, '.', '');

            // 6. DO NOT recalculate coupon_discount — just retain what's already in DB
            $cart->quantity = $combo_packages_cart_qty + $cart_items_qty;

            // 7. Save
            $cart->save(false);
        }
    }





    public function asJsonAddToCart()
    {
        $data = [];

        $data['id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['vendor_details_id'] = $this->vendor_details_id;

        $data['quantity'] = $this->quantity;

        $data['amount'] = number_format((float)$this->amount, 2, '.', '');

        $data['service_fees'] = $this->service_fees;

        $data['service_fees_with_tax'] = number_format((float)$this->service_fees_with_tax, 2, '.', '');

        $data['other_charges'] = $this->other_charges;


        $data['sgst'] = $this->sgst;
        $data['sgst'] = $this->sgst;

        $data['package_order_exist'] = $this->package_order_exist;

        $data['status'] = $this->status;

        $data['type_id'] = $this->type_id;

        return $data;
    }






    public function asJson()
    {
        $data = [];

        // Basic order details
        $data['id'] = $this->id;
        $data['user_id'] = $this->user_id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['quantity'] = $this->quantity;
        $data['amount'] = $this->amount;
        $data['services_amount'] = $this->amount - $this->package_amount;
        $data['package_amount'] = $this->package_amount;
        $data['tip'] = $this->tip;
        $data['coupon_discount'] = $this->coupon_discount;
        $data['referral_discount_percentage'] = $this->referral_discount_percentage;
        $data['referral_discount_amount'] = $this->referral_discount_amount;
        $data['wallet'] = $this->wallet;
        $data['service_instructions'] = $this->service_instructions;
        $data['details'] = $this->details;
        $data['coupon_code'] = $this->coupon_code;
        $data['coupon_applied_id'] = $this->coupon_applied_id;
        $data['package_order_exist'] = $this->package_order_exist;
        $data['is_coupon_applied'] = !empty($this->coupon_applied_id);
        $data['service_fees'] = $this->service_fees;
        $data['other_charges'] = $this->other_charges;
        $data['status'] = $this->status;
        $data['type_id'] = $this->type_id;
        $data['tax'] = $this->cgst + $this->sgst;
        $data['service_fees_with_tax'] = $this->service_fees_with_tax;

        // Calculate subtotal and taxes
        $amount = (float) ($this->amount ?? 0);
        $couponDiscount = (float) ($this->coupon_discount ?? 0);
        $taxRate = (float) ($data['tax'] ?? 0);
        $subtotal = max(0, $amount - $couponDiscount - $this->referral_discount_amount);
        $data['subtotal_tax'] = ($taxRate > 0) ? round(($subtotal * $taxRate) / 100, 2) : 0;
        $data['combo_packages_cart_amount'] = ComboPackagesCart::find()
            ->where(['user_id' => $this->user_id])
            ->sum('amount') ?? 0;
        $data['total_with_tax'] = $this->amount - $this->coupon_discount + $this->service_fees_with_tax + $data['subtotal_tax'] - $this->referral_discount_amount;

        // Service address handling
        $data['service_address'] = '';
        if (!empty($this->service_address)) {
            $service_location = DeliveryAddress::find()
                ->where(['id' => $this->service_address])
                ->one();
            if (!empty($service_location)) {
                $data['service_address'] = $service_location['address'] . ' ' . $service_location['location'];
            }
        }

        // Additional order details
        $data['store_address'] = $this->vendorDetails->storeAddressAsJson();
        $data['payment_mode'] = $this->payment_mode;
        $data['advance_pay_in_percentage'] = (int) $this->advance_pay_in_percentage;
        $data['service_date'] = $this->service_date;
        $data['service_time'] = !empty($this->service_time) ? date('g:i A', strtotime($this->service_time)) : null;
        $data['created_on'] = $this->created_on;
        $data['updated_on'] = $this->updated_on;
        $data['create_user_id'] = $this->create_user_id;
        $data['update_user_id'] = $this->update_user_id;
        $data['allow_full_payment'] = self::hasPriceRangeService($this->id);

        // Cart Items
        $data['cartItems'] = empty($this->cartItems)
            ? (object) []
            : array_map(fn($cartItem) => $cartItem->asJson(), $this->cartItems);

        // Combo Packages
        $combo_packages_cart = ComboPackagesCart::findAll(['user_id' => $this->user_id]);
        $data['combo_packages'] = empty($combo_packages_cart)
            ? (object) []
            : array_map(fn($combo) => $combo->asJsonCart(), $combo_packages_cart);

        // Service-Related Coupons
        $data['available_coupons'] = [];
        $service_ids = [];

        // Collect service IDs from cartItems
        if (!empty($this->cartItems)) {
            $service_ids = array_map(fn($cartItem) => $cartItem->service_item_id, $this->cartItems);
        }

        // Collect service IDs from combo packages
        if (!empty($combo_packages_cart)) {
            foreach ($combo_packages_cart as $combo) {
                if (!empty($combo->comboServices)) {
                    $service_ids = array_merge(
                        $service_ids,
                        array_map(fn($comboService) => $comboService->service_id, $combo->comboServices)
                    );
                }
            }
        }

        // Fetch coupons applicable to the services
        if (!empty($service_ids)) {
            $query = Coupon::find()
                ->where([
                    'or',
                    ['offer_type' => Coupon::OFFER_TYPE_ALL_SERVICES],
                    ['in', 'id', (new \yii\db\Query())
                        ->select('coupon_id')
                        ->from('service_has_coupons')
                        ->where(['service_id' => $service_ids])]
                ])
                ->andWhere(['<=', 'start_date', date('Y-m-d H:i:s')])
                ->andWhere(['>=', 'end_date', date('Y-m-d H:i:s')]);

            if ($this->amount > 0) {
                $query->andWhere(['<=', 'min_cart', $this->amount]);
            }

            $coupons = $query->all();

            foreach ($coupons as $coupon) {
                $data['available_coupons'][] = [
                    'coupon_id'   => $coupon->id,
                    'coupon_code' => $coupon->code,
                    'name'        => $coupon->name,
                    'description' => $coupon->description,
                    'discount'    => $coupon->discount,
                    'discount_type' => $coupon->discount_type == 1 ? 'percentage' : 'fixed',
                    'max_discount'  => $coupon->max_discount,
                    'min_cart'      => $coupon->min_cart,
                    'valid_from'    => $coupon->start_date,
                    'valid_until'   => $coupon->end_date,
                    'is_global'     => (bool) $coupon->is_global,
                    'coupon_type'   => $coupon->coupon_type == Coupon::COUPON_TYPE_HAPPY_HOUR
                        ? Coupon::COUPON_TYPE_HAPPY_HOUR
                        : Coupon::COUPON_TYPE_NORMAL,
                    'offer_type'    => $coupon->offer_type == Coupon::OFFER_TYPE_ALL_SERVICES
                        ? Coupon::OFFER_TYPE_ALL_SERVICES
                        : Coupon::OFFER_TYPE_SPECIFIC_SERVICES,
                    'is_applied'    => ($this->coupon_applied_id == $coupon->id)
                ];
            }
        }

        return $data;
    }
}

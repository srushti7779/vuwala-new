<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\BadRequestHttpException;

// use app\modules\admin\models\CouponsApplied; 
// use app\modules\admin\models\Orders;

/**
 * This is the base model class for table "coupon".
 *
 * @property integer $id
 * @property string $name
 * @property string $description 
 * @property string $code
 * @property string $discount
 * @property string $max_discount
 * @property integer $min_cart
 * @property integer $max_use
 * @property integer $max_use_of_coupon
 * @property string $start_date
 * @property string $end_date
 * @property integer $is_global
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\CouponVendor[] $couponVendors
 * @property \app\modules\admin\models\CouponsApplied[] $couponsApplieds
 */
class Coupon extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;
    public $service_ids;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'createUser',
            'updateUser',
            'couponVendors',
            'couponsApplieds',
            'productOrderItemsAssignedDiscounts',
            'serviceHasCoupons',
            'couponHasDays',
            'couponHasTimeSlots'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const IS_GLOBAL_YES = 1;
    const IS_GLOBAL_NO = 0;

    const DISCOUNT_TYPE_PERCENTAGE = 1;
    const DISCOUNT_TYPE_FIXED = 2;

    const COUPON_TYPE_HAPPY_HOUR = 1;
    const COUPON_TYPE_NORMAL  = 2;

    const OFFER_TYPE_ALL_SERVICES = 1;
    const OFFER_TYPE_SPECIFIC_SERVICES = 2;

    const DAY_MONDAY = 'Monday';
    const DAY_TUESDAY = 'Tuesday';
    const DAY_WEDNESDAY = 'Wednesday';
    const DAY_THURSDAY = 'Thursday';
    const DAY_FRIDAY = 'Friday';
    const DAY_SATURDAY = 'Saturday';
    const DAY_SUNDAY = 'Sunday';

    /**
     * @inheritdoc
     */
public function rules()
{
    return [
        [['name', 'code', 'discount', 'discount_type', 'coupon_type', 'offer_type'], 'required'],
        [['discount', 'max_discount', 'daily_redeem_limit'], 'number'],
        [['description'], 'string'],
        [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
        [['is_global', 'status', 'is_new_customer_offer', 'is_auto_apply_offer'], 'integer'],
        [['name', 'code'], 'string', 'max' => 255],
        [['service_ids'], 'each', 'rule' => ['integer'], 'when' => function ($model) {
            return $model->offer_type == self::OFFER_TYPE_SPECIFIC_SERVICES;
        }],
        ['code', 'match', 'pattern' => '/^[a-zA-Z0-9]+$/', 'message' => 'Code can only contain letters and numbers.'],
    ];
}

      public static function dayList()
    {
        return [
            1 => self::DAY_MONDAY,
            2 => self::DAY_TUESDAY,
            3 => self::DAY_WEDNESDAY,
            4 => self::DAY_THURSDAY,
            5 => self::DAY_FRIDAY,
            6 => self::DAY_SATURDAY,
            7 => self::DAY_SUNDAY,
        ];
    }


public static function getCouponOffersByDays($time, $day, $vendor_details_id)
{
    $data = [];

    try {
        // Validate inputs
        if (empty($time) || empty($day) || empty($vendor_details_id)) {
            throw new BadRequestHttpException(Yii::t("app", "Time, day, and vendor_details_id are required."));
        }

        // Validate and normalize time format (accepts HH:MM, HH:MM:SS, or 12-hour AM/PM)
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $time)) {
            // Already in 24-hour format (HH:MM or HH:MM:SS)
            $time = date('H:i:s', strtotime($time));
        } elseif (preg_match('/^(1[0-2]|0?\d):([0-5]\d)\s*(AM|PM)$/i', $time) ||
                  preg_match('/^(1[0-2]|0?\d):([0-5]\d):([0-5]\d)\s*(AM|PM)$/i', $time)) {
            // Convert 12-hour AM/PM to 24-hour format
            $time = date('H:i:s', strtotime($time));
        } else {
            throw new BadRequestHttpException(Yii::t("app", "Invalid time format. Use HH:MM, HH:MM:SS, or HH:MM AM/PM."));
        }

        // Validate day
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (!in_array($day, $validDays)) {
            throw new BadRequestHttpException(Yii::t("app", "Invalid day. Must be one of: " . implode(', ', $validDays)));
        }

        // Find coupons matching the criteria
        $coupons = Coupon::find()
            ->joinWith(['couponVendors cv' => function ($query) use ($vendor_details_id) {
                $query->andWhere(['cv.status' => CouponVendor::STATUS_ACTIVE])
                    ->andWhere(['cv.vendor_details_id' => $vendor_details_id]);
            }])
            ->joinWith(['couponHasDays chd' => function ($query) use ($day) {
                $query->andWhere(['chd.day' => $day, 'chd.status' => CouponHasDays::STATUS_ACTIVE]);
            }])
            ->joinWith(['couponHasTimeSlots chts' => function ($query) use ($time) {
                $query->andWhere(['chts.status' => CouponHasTimeSlots::STATUS_ACTIVE])
                    ->andWhere([
                        'or',
                        // Handle HH:MM:SS format
                        ['and', ['<=', 'chts.start_time', $time], ['>=', 'chts.end_time', $time]],
                        // Handle 12-hour AM/PM format by converting to 24-hour
                        [
                            'and',
                            ['<=', "DATE_FORMAT(chts.start_time, '%H:%i:%s')", $time],
                            ['>=', "DATE_FORMAT(chts.end_time, '%H:%i:%s')", $time]
                        ]
                    ])
                    
                    ;
            }])
            ->where(['coupon.status' => Coupon::STATUS_ACTIVE])
            ->andWhere(['coupon.coupon_type' => Coupon::COUPON_TYPE_HAPPY_HOUR])
            ->all();

        // Check if any coupons exist
        if (!empty($coupons)) {
            $couponDetails = [];
            foreach ($coupons as $coupon) {
                $couponDetails[] = [
                    'id' => $coupon->id,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount' => $coupon->discount,
                    'max_discount' => $coupon->max_discount
                ];
            }

            $data['exists'] = true;
            $data['details'] = $couponDetails;
        } else {
            $data['exists'] = false;
            $data['details'] = Yii::t("app", "No active Happy Hour coupons found for the specified time, day, and vendor.");
        }
    } catch (BadRequestHttpException $e) {
        $data['exists'] = false;
        $data['error'] = $e->getMessage();
    } catch (\Exception $e) {
        // Log the error for debugging
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'time' => $time,
            'day' => $day,
            'vendor_details_id' => $vendor_details_id,
        ], __METHOD__);
        $data['exists'] = false;
        $data['error'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
    }

    return $data;
}



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coupon';
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

    public function getGlobalOptionsBadges()
    {
        if ($this->is_global == self::IS_GLOBAL_YES) {
            return '<span class="badge badge-success">Yes</span>';
        } elseif ($this->is_global == self::IS_GLOBAL_NO) {
            return '<span class="badge badge-danger">No</span>';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'code' => Yii::t('app', 'Code'),
            'discount' => Yii::t('app', 'Discount'),
            'max_discount' => Yii::t('app', 'Max Discount'),
            'min_cart' => Yii::t('app', 'Min Cart'),
            'max_use' => Yii::t('app', 'Max Use'),
            'max_use_of_coupon' => Yii::t('app', 'Max Use Of Coupon'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
            'is_global' => Yii::t('app', 'Is Global'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponVendors()
    {
        return $this->hasMany(\app\modules\admin\models\CouponVendor::className(), ['coupon_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponsApplieds()
    {
        return $this->hasMany(\app\modules\admin\models\CouponsApplied::className(), ['coupon_id' => 'id']);
    }
    public function getDay()
{
    return $this->hasOne(Days::class, ['id' => 'day_id']);
}



        public function getCouponHasDays()
    {
        return $this->hasMany(\app\modules\admin\models\CouponHasDays::className(), ['coupon_id' => 'id']);
    }



     public function getCouponHasTimeSlots()
    {
        return $this->hasMany(\app\modules\admin\models\CouponHasTimeSlots::className(), ['coupon_id' => 'id']);
    }


        /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceHasCoupons()
    {
        return $this->hasMany(\app\modules\admin\models\ServiceHasCoupons::className(), ['coupon_id' => 'id']);
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
     * @return \app\modules\admin\models\CouponQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\CouponQuery(get_called_class());
    }



    public function getTotalDiscountGiven()
    {
        return $this->getCouponsApplieds()
            ->joinWith(['order o']) // Using alias 'o' for the 'order' relation
            ->andWhere([
                'o.payment_status' => Orders::PAYMENT_DONE,
                'o.voucher_code' => $this->code, // Ensure voucher_code matches $this->code
            ])
            ->sum('o.voucher_amount') ?? 0;
    }


    public function getTotalCouponCount()
    {
        return $this->getCouponsApplieds()
            ->joinWith(['order o']) // Using alias 'o' for the 'order' relation
            ->andWhere([
                'o.payment_status' => Orders::PAYMENT_DONE,
                'o.voucher_code' => $this->code, // Ensure voucher_code matches $this->code
            ])
            ->count() ?? 0;
    }


    public function getTotalRedemptions()
{
    return (int)CouponsApplied::find()
        ->where(['coupon_id' => $this->id, 'status' => 1])
        ->count();
}

public function getUniqueUsers()
{
    $orderIds = CouponsApplied::find()
        ->select('order_id')
        ->where(['coupon_id' => $this->id, 'status' => 1])
        ->column();

    if (empty($orderIds)) {
        return 0;
    }

    return (int)Orders::find()
        ->where(['id' => $orderIds])
        ->select('user_id')
        ->distinct()
        ->count();
}


public function getTotalRevenueGenerated()
{
    $orderIds = CouponsApplied::find()
        ->select('order_id')
        ->where(['coupon_id' => $this->id, 'status' => 1])
        ->column();

    if (empty($orderIds)) {
        return 0;
    }

    return (float)Orders::find()
        ->where(['id' => $orderIds])
        ->sum('payable_amount');
}


public function getAverageDiscountPerOrder()
{
    $orderIds = CouponsApplied::find()
        ->select('order_id')
        ->where(['coupon_id' => $this->id, 'status' => 1])
        ->column();

    $totalRedemptions = count($orderIds);
    if ($totalRedemptions == 0) {
        return 0;
    }

    $totalDiscount = (float)Orders::find()
        ->where(['id' => $orderIds])
        ->sum('voucher_amount'); // Use your discount column if different

    return round($totalDiscount / $totalRedemptions, 2);
}


public function getCouponTimeSlotsData($coupon_id){
    $coupon_has_time_slots = CouponHasTimeSlots::find()
        ->innerJoinWith(['couponHasDay chd' => function($query) use ($coupon_id) {
            $query->where(['chd.coupon_id' => $coupon_id]);
        }])
        ->all();
    return $coupon_has_time_slots;
}



    public function asJson()
    {
        $data = [];
        $data['id'] = $this->id;
                  $data['coupon_id'] = $this->id;

                $data['name'] =  $this->name;
        
                $data['description'] =  $this->description;
        
                $data['code'] =  $this->code;
        
                $data['discount_type'] =  $this->discount_type;
        
                $data['discount'] =  $this->discount;
        
                $data['max_discount'] =  $this->max_discount;
        
                $data['min_cart'] =  $this->min_cart;
        
                $data['max_use'] =  $this->max_use;
        
                $data['max_use_of_coupon'] =  $this->max_use_of_coupon;
        
                $data['start_date'] =  $this->start_date;
        
                $data['end_date'] =  $this->end_date;
        
                $data['is_global'] =  $this->is_global;
        
                $data['coupon_type'] =  $this->coupon_type;
        
                $data['offer_type'] =  $this->offer_type;
        
                $data['daily_redeem_limit'] =  $this->daily_redeem_limit;
        
                $data['is_new_customer_offer'] =  $this->is_new_customer_offer;
        
                $data['is_auto_apply_offer'] =  $this->is_auto_apply_offer;
        
                $data['status'] =  $this->status;


                $data['coupon_has_time_slots'] = $this->getCouponTimeSlotsData($this->id);
        

                $data['total_discount_given'] = $this->getTotalDiscountGiven();
                $data['getTotalCouponCount'] = $this->getTotalCouponCount();
                $data['total_redemptions'] = $this->getTotalRedemptions();
                $data['unique_users'] = $this->getUniqueUsers();
                $data['total_revenue_generated'] = $this->getTotalRevenueGenerated();
                $data['average_discount_per_order'] = $this->getAverageDiscountPerOrder();

        return $data;
    }




      public function asJsonView()
    {
        $data = [];
        $data['id'] = $this->id;
                $data['coupon_id'] = $this->id;

          
                $data['name'] =  $this->name;
        
                $data['description'] =  $this->description;
        
                $data['code'] =  $this->code;
        
                $data['discount_type'] =  $this->discount_type;
        
                $data['discount'] =  $this->discount;
        
                $data['max_discount'] =  $this->max_discount;
        
                $data['min_cart'] =  $this->min_cart;
        
                $data['max_use'] =  $this->max_use;
        
                $data['max_use_of_coupon'] =  $this->max_use_of_coupon;
        
                $data['start_date'] =  $this->start_date;
        
                $data['end_date'] =  $this->end_date;
        
                $data['is_global'] =  $this->is_global;
        
                $data['coupon_type'] =  $this->coupon_type;
        
                $data['offer_type'] =  $this->offer_type;
        
                $data['daily_redeem_limit'] =  $this->daily_redeem_limit;
        
                $data['is_new_customer_offer'] =  $this->is_new_customer_offer;
        
                $data['is_auto_apply_offer'] =  $this->is_auto_apply_offer;
        
                $data['status'] =  $this->status;

        $data['total_discount_given'] = $this->getTotalDiscountGiven();
        $data['getTotalCouponCount'] = $this->getTotalCouponCount();
        $data['total_redemptions'] = $this->getTotalRedemptions();
        $data['unique_users'] = $this->getUniqueUsers();
        $data['total_revenue_generated'] = $this->getTotalRevenueGenerated();
        $data['average_discount_per_order'] = $this->getAverageDiscountPerOrder();
        if($this->couponHasDays){
        foreach($this->couponHasDays as $couponHasDay){
            $data['couponHasDays'][] = $couponHasDay->asJson();
        }

        }else{
            $data['couponHasDays'] = [];

        }

        $couponHasTimeSlots = $this->getCouponTimeSlotsData($this->id);

        if(!empty($couponHasTimeSlots)){
            $data['couponHasTimeSlots'] = [];
            foreach($couponHasTimeSlots as $couponHasTimeSlot){
                $data['couponHasTimeSlots'][] = $couponHasTimeSlot->asJson();
            }
        }else{
            $data['couponHasTimeSlots'] = [];
        }

        if(!empty($this->serviceHasCoupons)){
            foreach($this->serviceHasCoupons as $serviceHasCoupon){
                $data['serviceHasCoupons'][] = $serviceHasCoupon->asJson();
            }
        }else{
            $data['serviceHasCoupons'] = [];
        }

        return $data;
    }
}

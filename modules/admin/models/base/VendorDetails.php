<?php
namespace app\modules\admin\models\base;

use app\components\DrivingDistance;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\Services;
use app\modules\admin\models\VendorMainCategoryData;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "vendor_details".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $business_name
 * @property integer $main_category_id
 * @property string $website_link
 * @property string $gst_number
 * @property string $account_number
 * @property string $ifsc_code
 * @property double $latitude
 * @property double $longitude
 * @property string $address
 * @property string $logo
 * @property string $shop_licence_no
 * @property string $avg_rating
 * @property double $min_order_amount
 * @property integer $commission_type
 * @property double $commission
 * @property string $offer_tag
 * @property integer $service_radius
 * @property double $min_service_fee
 * @property double $discount
 * @property integer $gender_type
 * @property integer $status
 *  @property integer $is_premium
 *  @property integer $is_featured
 * @property integer $service_type_home_visit
 * @property integer $service_type_walk_in
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *@property decimal $qr_scan_discount_percentage
 *
 * @property \app\modules\admin\models\BusinessDocuments[] $businessDocuments
 * @property \app\modules\admin\models\BusinessImages[] $businessImages
 * @property \app\modules\admin\models\Services[] $services
 * @property \app\modules\admin\models\Staff[] $staff
 * @property \app\modules\admin\models\StoreTimings[] $storeTimings
* @property \app\modules\admin\models\ServiceHasCoupons[] $serviceHasCoupons
 * @property \app\modules\admin\models\SubCategory[] $subCategories
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\MainCategory $mainCategory
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class VendorDetails extends \yii\db\ActiveRecord
{
    public $file;
    public $contact_no;
                        // In VendorDetails.php
    public $store_name;
   public $main_category_ids; // if you're using it only for filtering/searching

    use \mootensai\relation\RelationTrait;

    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'businessDocuments',
            'businessImages',
            'carts',
            'couponVendors',
            'orders',
            'reels',
            'services',
            'shopLikes',
            'shopReviews',
            'staff',
            'storeTimings',
            'subCategories',
            'user',
            'mainCategory',
            'createUser',
            'updateUser',
            'city',
            'vendorEarnings',
            'vendorPayouts',
            'vendorSubscriptions',
            'vendorMainCategoryDatas',
            'serviceHasCoupons',
            'storesHasUsers'
        ];
    }

    const IS_FEATURED     = 1;
    const IS_NOT_FEATURED = 0;

    const IS_PREMIUM     = 1;
    const IS_NOT_PREMIUM = 0;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_DELETE   = 2;

    const STATUS_VERIFICATION_PENDING  = 3;
    const STATUS_VERIFICATION_REJECTED = 4;

    const GENDER_MALE   = 1;
    const GENDER_FEMALE = 2;
    const GENDER_UNISEX = 3;

    public const COMMISSION_TYPE_PERCENTAGE = 1;

    public const COMMISSION_TYPE_FIXED = 2;

    // Commission type options
    public function getCommissionTypeOptions()
    {
        return [
            self::COMMISSION_TYPE_PERCENTAGE => 'Percentage',
            self::COMMISSION_TYPE_FIXED      => 'Fixed',
        ];
    }

    // Commission type badges
    public function getCommissionTypeBadge()
    {
        switch ($this->commission_type) {
            case self::COMMISSION_TYPE_PERCENTAGE:
                return '<span class="badge badge-info">Percentage</span>';
            case self::COMMISSION_TYPE_FIXED:
                return '<span class="badge badge-success">Fixed</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorMainCategoryDatas()
    {
        return $this->hasMany(\app\modules\admin\models\VendorMainCategoryData::className(), ['vendor_details_id' => 'id']);
    }

    public function getGenderOptions()
    {
        return [
            self::GENDER_MALE   => 'Male',
            self::GENDER_FEMALE => 'Female',
            self::GENDER_UNISEX => 'Unisex',
        ];
    }

    // Gender badges
    public function getGenderBadge()
    {
        switch ($this->gender_type) {
            case self::GENDER_MALE:
                return '<span class="badge badge-primary">Male</span>';
            case self::GENDER_FEMALE:
                return '<span class="badge badge-danger">Female</span>';
            case self::GENDER_UNISEX:
                return '<span class="badge badge-warning">Unisex</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE                => 'Active',
            self::STATUS_VERIFICATION_PENDING  => 'pending',
            self::STATUS_VERIFICATION_REJECTED => 'Rejected',
            self::STATUS_INACTIVE              => 'In Active',
            self::STATUS_DELETE                => 'Deleted',

        ];
    }
      public function getStatusLabel()
    {
        $options = $this->getStateOptions();
        return $options[$this->status] ?? null;
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-default">In Active</span>';
        } elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        } elseif ($this->status == self::STATUS_VERIFICATION_PENDING) {
            return '<span class="badge badge-info">Pending</span>';
        } elseif ($this->status == self::STATUS_VERIFICATION_REJECTED) {
            return '<span class="badge badge-danger">Rejected</span>';
        }
    }

  public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'city_id', 'commission_type', 'service_radius', 'gender_type', 'status', 'create_user_id', 'update_user_id', 'is_featured', 'is_premium'], 'integer'],
            [['gender_type'], 'default', 'value' => null],
            [['description', 'address'], 'string'],
            [['latitude', 'longitude', 'avg_rating', 'min_order_amount', 'commission', 'min_service_fee', 'discount', 'qr_scan_discount_percentage'], 'number'],
            [['created_on', 'updated_on', 'is_premium', 'is_featured', 'contact_no', 'is_gst_number_verified'], 'safe'],
            [['business_name', 'catalog_file', 'coordinates', 'shop_licence_no'], 'string', 'max' => 255],
            [['website_link', 'logo', 'offer_tag'], 'string', 'max' => 512],
            [['gst_number', 'ifsc_code', 'account_number'], 'string', 'max' => 50],
            [['service_type_home_visit', 'service_type_walk_in', 'is_verified'], 'integer'],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'csv, xls, xlsx', 'checkExtensionByMimeType' => true],
            [['qr_scan_discount_percentage'], 'number', 'min' => 0, 'max' => 100, 'message' => 'Discount percentage must be between 0 and 100.'],
            [['locality', 'sublocality', 'postal_code', 'administrative_area', 'country'], 'safe'],
            [['main_category_ids'], 'safe'], // Allow array for multiple categories
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_details';
    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED     => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',

        ];
    }

    public function getOptionsPremium()
    {
        return [

            self::IS_PREMIUM     => 'Yes',
            self::IS_NOT_PREMIUM => 'No',

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

    public function getPremiumBadges()
    {
        if ($this->is_premium == self::IS_PREMIUM) {
            return '<span class="badge badge-success">Yes</span>';
        } elseif ($this->is_premium == self::IS_NOT_PREMIUM) {
            return '<span class="badge badge-danger">No</span>';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                          => Yii::t('app', 'ID'),
            'user_id'                     => Yii::t('app', 'User ID'),
            'business_name'               => Yii::t('app', 'Business Name'),
            'main_category_id'            => Yii::t('app', 'Main Category ID'),
            'website_link'                => Yii::t('app', 'Website Link'),
            'gst_number'                  => Yii::t('app', 'Gst Number'),
            'account_number'              => Yii::t('app', 'Account Number'),
            'ifsc_code'                   => Yii::t('app', 'IFSC Code'),
            'latitude'                    => Yii::t('app', 'Latitude'),
            'longitude'                   => Yii::t('app', 'Longitude'),
            'address'                     => Yii::t('app', 'Address'),
            'logo'                        => Yii::t('app', 'Logo'),
            'shop_licence_no'             => Yii::t('app', 'Shop Licence No'),
            'avg_rating'                  => Yii::t('app', 'Avg Rating'),
            'min_order_amount'            => Yii::t('app', 'Min Order Amount'),
            'commission_type'             => Yii::t('app', 'Commission Type'),
            'commission'                  => Yii::t('app', 'Commission'),
            'offer_tag'                   => Yii::t('app', 'Offer Tag'),
            'service_radius'              => Yii::t('app', 'Service Radius'),
            'min_service_fee'             => Yii::t('app', 'Convenience Fee'),
            'discount'                    => Yii::t('app', 'Discount'),
            // 'is_top_shop' => Yii::t('app', 'Is Top Shop'),
            'gender_type'                 => Yii::t('app', 'Gender Type'),
            'is_featured'                 => Yii::t('app', 'Featured'),

            'is_premium'                  => Yii::t('app', 'Premium'),
            'qr_scan_discount_percentage' => Yii::t('app', 'Qr Scan Discount Percentage'),
            'status'                      => Yii::t('app', 'Status'),
            'service_type_home_visit'     => Yii::t('app', 'Service Type Home Visit'),
            'service_type_walk_in'        => Yii::t('app', 'Service Type Walk In'),
            'created_on'                  => Yii::t('app', 'Created On'),
            'updated_on'                  => Yii::t('app', 'Updated On'),
            'create_user_id'              => Yii::t('app', 'Create User ID'),
            'update_user_id'              => Yii::t('app', 'Update User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBusinessDocuments()
    {
        return $this->hasMany(\app\modules\admin\models\BusinessDocuments::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBusinessImages()
    {
        return $this->hasMany(\app\modules\admin\models\BusinessImages::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCarts()
    {
        return $this->hasMany(\app\modules\admin\models\Cart::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponVendors()
    {
        return $this->hasMany(\app\modules\admin\models\CouponVendor::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(\app\modules\admin\models\Orders::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReels()
    {
        return $this->hasMany(\app\modules\admin\models\Reels::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Services::class, ['vendor_details_id' => 'id']);
    }

    public function getVendorMainCategories()
    {
        return $this->hasMany(VendorMainCategoryData::class, ['vendor_details_id' => 'id'])
            ->joinWith('mainCategory');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopLikes()
    {
        return $this->hasMany(\app\modules\admin\models\ShopLikes::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopReviews()
    {
        return $this->hasMany(\app\modules\admin\models\ShopReview::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasMany(\app\modules\admin\models\Staff::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreTimings()
    {
        return $this->hasMany(\app\modules\admin\models\StoreTimings::className(), ['vendor_details_id' => 'id']);
    }
public function getServiceHasCoupons()
{
    return $this->hasMany(\app\modules\admin\models\ServiceHasCoupons::class, ['service_id' => 'id'])
     ->joinwith('services');   // <--- IMPORTANT
}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategories()
    {
        return $this->hasMany(\app\modules\admin\models\SubCategory::className(), ['vendor_details_id' => 'id']);
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
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
    public function getStoreName()
    {
        return $this->business_name; // or however it's derived
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorEarnings()
    {
        return $this->hasMany(\app\modules\admin\models\VendorEarnings::className(), ['vendor_details_id' => 'id']);
    }


      public function getStoresHasUsers()
    {
        return $this->hasMany(StoresHasUsers::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorPayouts()
    {
        return $this->hasMany(\app\modules\admin\models\VendorPayout::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorSubscriptions()
    {
        return $this->hasOne(\app\modules\admin\models\VendorSubscriptions::className(), ['vendor_details_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value'              => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class'              => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }

    public function extractNumericSublocality($areaName, $plotOrRoad)
    {
        // Regex for road numbers (e.g., "Road No. 5", "Lane 12")
        $roadPattern = '/\b(?:Road|Lane|Street|Avenue|No\.?)\s*[:\-]?\s*\d{1,6}[A-Za-z]?\b/i';
        // Regex for plot numbers (e.g., "Plot No: 1303", "Flat 21")
        $plotPattern = '/\b(?:Plot|Flat|House|Sector|Block|Apartment)\s*[:\-]?\s*\d{1,6}[A-Za-z]?\b/i';

        // Check plotOrRoad for road number
        if (! empty($plotOrRoad) && preg_match($roadPattern, $plotOrRoad)) {
            return $plotOrRoad; // Return full text of plotOrRoad
        }

        // Check areaName for plot number
        if (! empty($areaName) && preg_match($plotPattern, $areaName)) {
            return $areaName; // Return full text of areaName
        }

        // Fallback: return plotOrRoad if non-empty, else areaName, else empty string
        if (! empty($plotOrRoad)) {
            return $plotOrRoad;
        }
        if (! empty($areaName)) {
            return $areaName;
        }

        return '';
    }

    public static function getServiceScheduleSlots($duration, $break, $stTime, $enTime)
    {
        $periods       = [];
        $start         = new \DateTime($stTime);
        $end           = new \DateTime($enTime);
        $interval      = new \DateInterval("PT" . $duration . "M");
        $breakInterval = new \DateInterval("PT" . $break . "M");

        for (
            $intStart = $start;
            $intStart < $end;
            $intStart->add($interval)->add($breakInterval)
        ) {
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if ($endPeriod > $end) {
                $endPeriod = $end;
            }
            $periods[] = $intStart->format('h:i A');
        }

        return $periods;
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\VendorDetailsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorDetailsQuery(get_called_class());
    }

    public function getActiveCoupon()
    {
        $currentDate = date('Y-m-d H:i:s');
        $storeCoupon = Coupon::find()
            ->alias('coupon')
            ->joinWith('couponVendors cs')
            ->where([
                'cs.status'            => CouponVendor::STATUS_ACTIVE,
                'cs.vendor_details_id' => $this->id,
                'coupon.status'        => Coupon::STATUS_ACTIVE,
            ])
            ->andWhere(['<=', 'coupon.start_date', $currentDate])
            ->andWhere([
                'or',
                ['>=', 'coupon.end_date', $currentDate],
                ['coupon.end_date' => null],
            ])
            ->orderBy(['coupon.id' => SORT_DESC])
            ->one();
        return $storeCoupon ?? null;
    }

    public static function getVendorDetailsByUserId($user_id)
    {
        $vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();
        if (! empty($vendor_details)) {
            return $vendor_details;
        } else {
            return;
        }
    }

    public function asJson($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        if (! empty($this->mainCategory)) {
            $data['main_category'] = $this->mainCategory->asJson();

        } else {
            $data['main_category'] = (object) [];
        }

        $data['gst_number']  = $this->gst_number;
        $data['msme_number'] = $this->msme_number;

        $data['account_number'] = $this->account_number;

        $data['ifsc_code'] = $this->ifsc_code;

        $data['ifsc_code']    = $this->ifsc_code;
        $data['bank_name']    = $this->bank_name;
        $data['bank_branch']  = $this->bank_branch;
        $data['bank_state']   = $this->bank_state;
        $data['bank_city']    = $this->bank_city;
        $data['bank_address'] = $this->bank_address;

        $data['account_holder_name'] = $this->account_holder_name;

        $data['latitude'] = $this->latitude;

        $data['no_of_branches'] = $this->no_of_branches;
        $data['no_of_sitting']  = $this->no_of_sitting;
        $data['no_of_staff']    = $this->no_of_staff;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['commission_type'] = $this->commission_type;

        $data['commission'] = $this->commission;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['is_featured'] = $this->is_featured;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['qr_scan_discount_percentage'] = $this->qr_scan_discount_percentage;

        if (! empty($this->staff)) {
            foreach ($this->staff as $staff) {
                if ($staff->status == Staff::STATUS_ACTIVE) { // Assuming 1 means active
                    $data['staff'][] = $staff->asJson();
                }
            }
        }

        // If no active staff exist, return a default empty structure
        if (empty($data['staff'])) {
            $data['staff'][] = [
                'staff_id'          => null,
                'vendor_details_id' => null,
                'profile_image'     => null,
                'mobile_no'         => null,
                'full_name'         => null,
                'email'             => null,
                'gender'            => null,
                'dob'               => null,
                'role'              => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,
            ];
        }

        if (! empty($this->storeTimings)) {
            foreach ($this->storeTimings as $storeTimings) {
                $data['storeTimings'][] = $storeTimings->asJson();
            }
        } else {
            $data['storeTimings'][] = [
                'store_timings_id'  => null,
                'vendor_details_id' => null,
                'day'               => null,
                'start_time'        => null,
                'close_time'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,
            ];
        }

        if (! empty($this->businessImages)) {
            foreach ($this->businessImages as $businessImages) {
                $data['businessImages'][] = $businessImages->asJson();
            }
        } else {
            $data['businessImages'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'image_file'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }

        // var_dump($this->shopReviews);die();

        if (! empty($this->shopReviews)) {
            foreach ($this->shopReviews as $shopReviews) {
                $data['shopReviews'][] = $shopReviews->asJson();
            }
        } else {
            $data['shopReviews'] = [];
        }

        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        $coupon         = $this->getActiveCoupon();
        $data['coupon'] = $coupon ? $coupon->asJson() : [];

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        return $data;
    }

    public function asJsonWeb($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        if (! empty($this->mainCategory)) {
            $data['main_category'] = $this->mainCategory->asJson();

        } else {
            $data['main_category'] = (object) [];
        }

        $data['gst_number']  = $this->gst_number;
        $data['msme_number'] = $this->msme_number;

        $data['account_number'] = $this->account_number;

        $data['ifsc_code'] = $this->ifsc_code;

        $data['ifsc_code']    = $this->ifsc_code;
        $data['bank_name']    = $this->bank_name;
        $data['bank_branch']  = $this->bank_branch;
        $data['bank_state']   = $this->bank_state;
        $data['bank_city']    = $this->bank_city;
        $data['bank_address'] = $this->bank_address;

        $data['account_holder_name'] = $this->account_holder_name;

        $data['latitude'] = $this->latitude;

        $data['no_of_branches'] = $this->no_of_branches;
        $data['no_of_sitting']  = $this->no_of_sitting;
        $data['no_of_staff']    = $this->no_of_staff;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['commission_type'] = $this->commission_type;

        $data['commission'] = $this->commission;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['is_featured'] = $this->is_featured;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        if (! empty($this->storeTimings)) {
            foreach ($this->storeTimings as $storeTimings) {
                $data['storeTimings'][] = $storeTimings->asJson();
            }
        } else {
            $data['storeTimings'][] = [
                'store_timings_id'  => null,
                'vendor_details_id' => null,
                'day'               => null,
                'start_time'        => null,
                'close_time'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,
            ];
        }

        if (! empty($this->businessImages)) {
            foreach ($this->businessImages as $businessImages) {
                $data['businessImages'][] = $businessImages->asJson();
            }
        } else {
            $data['businessImages'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'image_file'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        // var_dump($this->shopReviews);die();

        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        return $data;
    }

    public function asJsonMyFaverats($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        $data['main_category']    = $this->mainCategory->asJson();
        $data['latitude']         = $this->latitude;
        $data['longitude']        = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['avg_rating'] = $this->avg_rating;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['is_featured'] = $this->is_featured;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;
        $driving_distance                = new DrivingDistance();

        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        $coupon         = $this->getActiveCoupon();
        $data['coupon'] = $coupon ? $coupon->asJson() : [];

        return $data;
    }

    public function asCustomJson($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category'] = $this->mainCategory->title;

        $data['gst_number'] = $this->gst_number;

        $data['latitude'] = $this->latitude;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        if (! empty($this->storeTimings)) {
            foreach ($this->storeTimings as $storeTimings) {
                $data['storeTimings'][] = $storeTimings->asJson();
            }
        } else {
            $data['storeTimings'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'day_id '           => null,
                'start_time'        => null,
                'close_time'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {
            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }

        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }
        return $data;
    }

    public function asJsonVendor()
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id']  = $this->main_category_id;
        $vendor_main_category_data = VendorMainCategoryData::find()->where(['vendor_details_id' => $this->id])->all();

        if (! empty($vendor_main_category_data)) {
            foreach ($vendor_main_category_data as $vendor_main_category) {
                $data['main_category'][] = $vendor_main_category->asJson();

            }
        } else {
            $data['main_category'] = [];
        }

        $data['no_of_branches'] = $this->no_of_branches;
        $data['no_of_sitting']  = $this->no_of_sitting;
        $data['no_of_staff']    = $this->no_of_staff;

        $data['account_number'] = $this->account_number;
        $data['ifsc_code']      = $this->ifsc_code;
        $data['ifsc_code']      = $this->ifsc_code;
        $data['bank_name']      = $this->bank_name;
        $data['bank_branch']    = $this->bank_branch;
        $data['bank_state']     = $this->bank_state;
        $data['bank_city']      = $this->bank_city;
        $data['bank_address']   = $this->bank_address;

        $data['vendor_persional_details'] = $this->user->asJsonVendorPersionalDetails();

        if (! empty($this->businessDocuments)) {
            foreach ($this->businessDocuments as $businessDocumentsData) {
                $data['businessDocuments'][] = $businessDocumentsData->asJson();
            }
        } else {
            $data['businessDocuments'] = [];
        }

        $data['website_link'] = $this->website_link;

        $data['gst_number']  = $this->gst_number;
        $data['msme_number'] = $this->msme_number;

        $data['account_number'] = $this->account_number;

        $data['ifsc_code'] = $this->ifsc_code;

        $data['latitude'] = $this->latitude;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['commission_type'] = $this->commission_type;

        $data['commission'] = $this->commission;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        if (! empty($this->storeTimings)) {
            foreach ($this->storeTimings as $storeTimings) {
                $data['storeTimings'][] = $storeTimings->asJson();
            }
        } else {
            $data['storeTimings'] = [];
        }

        if (! empty($this->businessImages)) {
            foreach ($this->businessImages as $businessImages) {
                $data['businessImages'][] = $businessImages->asJson();
            }
        } else {
            $data['businessImages'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'image_file'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        // if (!empty($this->shopReviews)) {
        //     foreach ($this->shopReviews as $shopReviews) {
        //         $data['shopReviews'] = $shopReviews->asJson();
        //     }
        // } else {
        //     $data['shopReviews'][] = [

        //         'id' => null,
        //         'vendor_details_id' => null,
        //         'user_id' => null,
        //         'user_full_name' => null,
        //         'profile_image' => null,
        //         'order_id' => null,
        //         'comment' => null,
        //         'description' => null,
        //         'rating' => null,
        //         'type_id' => null,
        //         'status' => null,
        //         'created_on' => null,
        //         'updated_on' => null,
        //         'create_user_id' => null,
        //         'update_user_id' => null,
        //     ];
        // }

        $data['isActiveSubscription'] = VendorSubscriptions::isActiveSubscription($this->id);
        if (VendorSubscriptions::isActiveSubscription($this->id) == true) {
            if (! empty($this->vendorSubscriptions)) {
                $data['subscription_details'] = $this->vendorSubscriptions->asJson();
            } else {
                $data['subscription_details'] = '';
            }
        } else {
            $data['subscription_details'] = '';
        }

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        return $data;
    }

    public function storeAddressAsJson()
    {
        $data['business_name'] = $this->business_name;
        $data['logo']          = $this->logo;
        $data['latitude']      = $this->latitude;
        $data['longitude']     = $this->longitude;
        $data['address']       = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        return $data;
    }

    public static function checkiFsc($ifsc)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://ifsc.razorpay.com/' . $ifsc,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function asJsonHomeSearch($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        $data['main_category']    = $this->mainCategory->asJson();

        $data['gst_number'] = $this->gst_number;

        $data['msme_number'] = $this->msme_number;

        $data['account_number'] = $this->account_number;

        $data['ifsc_code'] = $this->ifsc_code;

        $data['latitude'] = $this->latitude;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['commission_type'] = $this->commission_type;

        $data['commission'] = $this->commission;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = $this->description;

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {
            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }

        $coupon         = $this->getActiveCoupon();
        $data['coupon'] = $coupon ? $coupon->asJson() : [];

        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        return $data;
    }

    public function asJsonReview($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        $data['main_category']    = $this->mainCategory->asJson();

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['avg_rating'] = $this->avg_rating;

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        if (! empty($this->businessImages)) {
            foreach ($this->businessImages as $businessImages) {
                $data['businessImages'][] = $businessImages->asJson();
            }
        } else {
            $data['businessImages'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'image_file'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }

        return $data;
    }

    public function asMyFavouritesJson($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;

        $data['user_id'] = $this->user_id;

        $data['business_name'] = $this->business_name;

        $data['main_category_id'] = $this->main_category_id;
        $data['main_category']    = $this->mainCategory->asJson();

        $data['gst_number']  = $this->gst_number;
        $data['msme_number'] = $this->msme_number;

        $data['account_number'] = $this->account_number;

        $data['ifsc_code'] = $this->ifsc_code;

        $data['latitude'] = $this->latitude;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['shop_licence_no'] = $this->shop_licence_no;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['commission_type'] = $this->commission_type;

        $data['commission'] = $this->commission;

        $data['offer_tag'] = $this->offer_tag;

        $data['service_radius'] = $this->service_radius;

        $data['min_service_fee'] = $this->min_service_fee;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        if (! empty($this->businessImages)) {
            foreach ($this->businessImages as $businessImages) {
                $data['businessImages'][] = $businessImages->asJson();
            }
        } else {
            $data['businessImages'][] = [
                'id'                => null,
                'vendor_details_id' => null,
                'image_file'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,

            ];
        }

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message'] = "Service not available this location";

            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }
        if (! empty($this->shopReviews)) {
            foreach ($this->shopReviews as $shopReviews) {
                $data['shopReviews'] = $shopReviews->asJson();
            }
        } else {
            $data['shopReviews'][] = [

                'id'                => null,
                'vendor_details_id' => null,
                'user_id'           => null,
                'user_full_name'    => null,
                'profile_image'     => null,
                'order_id'          => null,
                'comment'           => null,
                'description'       => null,
                'rating'            => null,
                'type_id'           => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,
            ];
        }

        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        return $data;
    }

    public function asJsonStoreProfileUserSide($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;
        $data['user_id']           = $this->user_id;
        $data['business_name']     = $this->business_name;

        if (! empty($this->vendorMainCategoryDatas)) {
            foreach ($this->vendorMainCategoryDatas as $vendorMainCategoryData) {
                $data['vendorMainCategoryData'][] = $vendorMainCategoryData->asJsonUserSide();
            }
        }

        $data['gst_number']  = $this->gst_number;
        $data['msme_number'] = $this->msme_number;
        $data['latitude']    = $this->latitude;
        $data['longitude']   = $this->longitude;
        $data['address']     = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo']                        = $this->logo;
        $data['avg_rating']                  = $this->avg_rating;
        $data['min_order_amount']            = $this->min_order_amount;
        $data['offer_tag']                   = $this->offer_tag;
        $data['service_radius']              = $this->service_radius;
        $data['is_top_shop']                 = $this->is_top_shop;
        $data['is_featured']                 = $this->is_featured;
        $data['service_type_walk_in']        = $this->service_type_walk_in;
        $data['gender_type']                 = $this->gender_type;
        $data['about_store']                 = strip_tags($this->description);
        $data['status']                      = $this->status;
        $data['service_type_home_visit']     = $this->service_type_home_visit;
        $data['qr_scan_discount_percentage'] = $this->qr_scan_discount_percentage;
        if (! empty($this->storeTimings)) {
            foreach ($this->storeTimings as $storeTimings) {
                $data['storeTimings'][] = $storeTimings->asJson();
            }
        } else {
            $data['storeTimings'][] = [
                'store_timings_id'  => null,
                'vendor_details_id' => null,
                'day'               => null,
                'start_time'        => null,
                'close_time'        => null,
                'status'            => null,
                'created_on'        => null,
                'updated_on'        => null,
                'create_user_id'    => null,
                'update_user_id'    => null,
            ];
        }

        $driving_distance = new DrivingDistance();

        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message']              = "Service not available this location";
            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }
        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        $coupon         = $this->getActiveCoupon();
        $data['coupon'] = $coupon ? $coupon->asJson() : [];

        return $data;
    }

    public function asJsonNearByShops($user_id = '', $latitude = '', $longitude = '')
    {
        $data                      = [];
        $data['vendor_details_id'] = $this->id;
        $data['user_id']           = $this->user_id;
        $data['business_name']     = $this->business_name;
        $data['latitude']          = $this->latitude;

        $data['longitude'] = $this->longitude;

        $data['address'] = $this->address;

        $data['location_name']          = $this->address;
        $data['street']                 = $this->street;
        $data['iso_country_code']       = $this->iso_country_code;
        $data['country']                = $this->country;
        $data['postal_code']            = $this->postal_code;
        $data['administrative_area']    = $this->administrative_area;
        $data['subadministrative_area'] = $this->subadministrative_area;
        $data['locality']               = $this->locality;
        $data['sublocality']            = $this->extractNumericSublocality($this->thoroughfare, $this->sublocality);
        $data['thoroughfare']           = $this->thoroughfare;
        $data['subthoroughfare']        = $this->subthoroughfare;

        $data['logo'] = $this->logo;

        $data['avg_rating'] = $this->avg_rating;

        $data['min_order_amount'] = $this->min_order_amount;

        $data['offer_tag'] = $this->offer_tag;

        $data['discount'] = $this->discount;

        $data['is_top_shop'] = $this->is_top_shop;

        $data['is_featured'] = $this->is_featured;

        $data['service_type_walk_in'] = $this->service_type_walk_in;

        $data['gender_type'] = $this->gender_type;

        $data['about_store'] = strip_tags($this->description);

        $data['status'] = $this->status;

        $data['service_type_home_visit'] = $this->service_type_home_visit;

        $driving_distance = new DrivingDistance();
        if (! empty($latitude && $longitude)) {

            $dist = $driving_distance->getDrivingDistance($this->latitude, $this->longitude, $latitude, $longitude);

            if ($dist['meters'] > $this->service_radius) {
                $data['message']              = "Cannot delivery to this location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '0';
            } else {
                $data['message']              = "Great! Shop is far from your location";
                $data['distance']             = $dist;
                $data['is_service_available'] = '1';
            }
        } else {
            $data['message']              = "Service not available this location";
            $data['is_service_available'] = '0';
            $data['distance']             = '0.0';
        }
        $data['contact_details'] = [
            'contact_number' => $this->user->contact_no,
            'email'          => $this->user->email,
            'website'        => $this->website_link,

        ];

        $shop_likes = ShopLikes::find()->where(['user_id' => $user_id])->andWhere(['vendor_details_id' => $this->id])->one();

        if (! empty($shop_likes)) {
            $data['added_favorite'] = true;
        } else {
            $data['added_favorite'] = false;
        }

        $coupon         = $this->getActiveCoupon();
        $data['coupon'] = $coupon ? $coupon->asJson() : [];

        return $data;
    }

}

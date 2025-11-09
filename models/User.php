<?php

namespace app\models;

use app\modules\admin\models\base\Orders;
use app\modules\admin\models\CashbackTransaction;
use app\traits\models\WithStatus;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\base\BankDetails;
use app\modules\admin\models\base\EmailOtpVerifications;
use app\modules\admin\models\base\Wallet;
use app\modules\admin\models\BusinessDocuments;
use app\modules\admin\models\Days;
use app\modules\admin\models\GuestUserDeposits;
use app\modules\admin\models\MainCategory;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\VendorBrands;
use app\modules\admin\models\VendorDetails;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoresUsersMemberships;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_on
 * @property integer $updated_at
 * @property string $password write-only password
 * @property-read string fullName
 * @property-read string shortName 
 */
class User extends ActiveRecord implements IdentityInterface
{


	const OTP_EXPIRY_MINUTES = 10;
	const RATE_LIMIT_ATTEMPTS = 5;
	const RATE_LIMIT_WINDOW = 3600; // 1 hour in seconds

	public $passwordRepeat;

	public $password;

	public $role;

	use WithStatus;

	const STATUS_ACTIVE = 10;
	const STATUS_BLOCKED = 0;

	const ROLE_ADMIN = 'admin';

	const ROLE_USER = 'user';

	const ROLE_GUEST = 'guest';


	const DEVICE_TYPE_ANDROID = 1;
	const DEVICE_TYPE_IOS = 2;
	const DEVICE_TYPE_WEB = 3;

	const ROLE_SUB_ADMIN = 'sub_admin';
	const ROLE_VENDOR = 'vendor';
	const ROLE_HOME_VISITOR = 'home_visitor';
	const ROLE_STAFF = 'staff';
	const ROLE_MANAGER = 'manager';
	const ROLE_ACCOUNT_MANAGER = 'account_manager';


	const ROLE_SUBADMIN = 'subadmin';

	const ROLE_QA = 'qa';
	const ROLE_MARKETING = 'marketing';



	const VENDOR_STORE_TYPE_SINGLE = 1;
	const VENDOR_STORE_TYPE_MULTI = 2;





	const SIGNUP_TYPE_SOCIAL_MEDIA = 1;
	const SIGNUP_TYPE_MOBILE = 2;
	const SIGNUP_TYPE_SITE = 0;
	public $newPassword;
	public $confirm_password;
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{user}}';
	}



	public function relationNames()
	{
		return [
			'auths',
			'authSessions',
			'bankDetails',
			'bannerChargeLogs',
			'bannerLogs',
			'bannerTimings',
			'bookedServices',
			'businessDocuments',
			'businessImages',
			'bypassNumbers',
			'carts',
			'cartItems',
			'cities',
			'comboOrders',
			'comboOrderServicies',
			'comboPackages',
			'comboPackagesCarts',
			'comboServices',
			'coupons',
			'couponVendors',
			'couponsApplieds',
			'days',
			'deliveryAddresses',
			'fcmNotifications',
			'homeVisitorsHasOrders',
			'nextVisitDetails',
			'orderComplaints',
			'orderDetails',
			'orderStatuses',
			'orderTransactionDetails',
			'orders',
			'quizAnswers',
			'quizQuestions',
			'quizUserAnswers',
			'quizzes',
			'reelReports',
			'reelShareCounts',
			'reelTags',
			'reels',
			'reelsLikes',
			'reelsViewCounts',
			'rescheduleOrderLogs',
			'serviceOrderImages',
			'servicePinCodes',
			'serviceTypes',
			'services',
			'shopLikes',
			'shopReviews',
			'staff',
			'storeServiceTypes',
			'storeTimings',
			'storesHasUsers',
			'subCategories',
			'subscriptions',
			'supportTickets',
			'supportTicketsHasFiles',
			'vendorDetails',
			'vendorEarnings',
			'vendorMainCategoryDatas',
			'vendorPayouts',
			'vendorSubscriptions',
			'wallets',
			'storesUsersMemberships'
		];
	}


	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			// Default values
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BLOCKED]],
			['main_vendor', 'default', 'value' => 0],
			['allow_onboarding', 'default', 'value' => 0],
			['vendor_store_type', 'in', 'range' => [self::VENDOR_STORE_TYPE_SINGLE, self::VENDOR_STORE_TYPE_MULTI]],
			[['allow_order_approval'], 'boolean'],

			// Required fields (for vendor creation scenario)
			[['username', 'contact_no', 'first_name', 'email',], 'required', 'on' => 'create_vendor'],

			// String validations
			[[
				'first_name',
				'last_name',
				'username',
				'user_role',
				'oauth_client_user_id',
				'oauth_client',
				'profile_image',
				'access_token',
				'device_token',
				'email'
			], 'string', 'max' => 255],

			// Email format
			['email', 'email', 'message' => 'Enter a valid email address.'],

			// Contact number must be numeric
			['contact_no', 'match', 'pattern' => '/^\d+$/', 'message' => 'Contact number must contain digits only.'],

			// Uniqueness checks (only on create)
			['username', 'unique', 'targetClass' => self::class, 'filter' => ['user_role' => self::ROLE_VENDOR], 'message' => 'This username is already taken.', 'on' => 'create_vendor'],
			['email', 'unique', 'targetClass' => self::class, 'filter' => ['user_role' => self::ROLE_VENDOR], 'message' => 'This email is already registered.', 'on' => 'create_vendor'],
			['contact_no', 'unique', 'targetClass' => self::class, 'filter' => ['user_role' => self::ROLE_VENDOR], 'message' => 'This contact number is already registered.', 'on' => 'create_vendor'],

		];
	}

	public function scenarios()
	{
		$scenarios = parent::scenarios();
		$scenarios['add-user'] = [
			'email',
			'username',
			'first_name',
			'password',
			'referral_id',
			'referral_code',
		];

		$scenarios['facebook-login'] = [
			'email',
			'username',
			'oauth_client_user_id',
			'first_name',
			'oauth_client',
			'profile_image',
			'user_role',
			'status',
			'signup_type'

		];
		$scenarios['phone-login'] = [
			'contact_no',
			'device_token',
			'device_type',
			'oauth_client',
			'oauth_client_user_id'
		];
		$scenarios['rest-user'] = [
			'email',
			'contact_no'
		];

		$scenarios['update-latlong'] = [
			'latitude',
			'longitude',


		];
		$scenarios['create_vendor'] = [
			'username',
			'first_name',
			'contact_no',
			'email',
			'password',
			'status',
			'vendor_store_type',
			'allow_onboarding',
			'main_vendor',

		];





		return $scenarios;
	}
	/**
	 * User full name
	 * (as first/last name)
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return "{$this->first_name} {$this->last_name}";
	}

	/**
	 * User short name
	 * (as first name, last name first letter)
	 *
	 * @return string
	 */
	public function getShortName()
	{
		return trim($this->first_name . ' ' . ($this->last_name ? $this->last_name[0] . '.' : ''));
	}

	/**
	 * List of user status aliases
	 *
	 * @return array
	 */
	public static function getStatusesList()
	{
		return [
			static::STATUS_ACTIVE  => 'Active',
			static::STATUS_BLOCKED => 'Blocked',
		];
	}


	public static function getStatusVendorStoreTypes()
	{
		return [
			static::VENDOR_STORE_TYPE_SINGLE  => 'Single',
			static::VENDOR_STORE_TYPE_MULTI    => 'Multi',
		];
	}
	public function stateBadges()
	{
		$states = $this->getStatusesList();
		if ($this->status == self::STATUS_ACTIVE) {
			return '<span class="badge badge-success">' . $states[self::STATUS_ACTIVE] . '</span>';
		} elseif ($this->status == self::STATUS_BLOCKED) {
			return '<span class="badge badge-default">' . $states[self::STATUS_BLOCKED] . '</span>';
		}
	}



	static public function getRoles()
	{
		return [
			self::ROLE_ADMIN => 'admin',
			self::ROLE_MANAGER => 'manager',
			self::ROLE_USER => 'User',
			self::ROLE_HOME_VISITOR => 'Home Visitor',
			self::ROLE_VENDOR => 'vendor',
			self::ROLE_ACCOUNT_MANAGER => 'Account Manager',
			self::ROLE_QA => 'QA',
			self::ROLE_MARKETING => 'Marketing',

		];
	}


	/**
	 * Assign a role to user
	 *
	 * @param string $role
	 *
	 * @return bool
	 */
	public function assignRole($role)
	{
		if (!Yii::$app->authManager->checkAccess($this->id, $role)) {
			$authRole = Yii::$app->authManager->getRole($role);
			Yii::$app->authManager->assign($authRole, $this->id);

			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		return static::findOne([
			'access_token' => $token
		]);
		//throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}


	    public function getStoresUsersMemberships()
    {
        return $this->hasMany(\app\modules\admin\models\StoresUsersMemberships::className(), ['update_user_id' => 'id']);
    }

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 *
	 * @return static|null
	 */
	public static function findByUsername($username)
	{


		$user = User::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->one();

		return $user;
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 *
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token)
	{
		if (!static::isPasswordResetTokenValid($token)) {
			return null;
		}

		return static::findOne([
			'password_reset_token' => $token,
			'status'               => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 *
	 * @return bool
	 */
	public static function isPasswordResetTokenValid($token)
	{
		if (empty($token)) {
			return false;
		}
		$timestamp = (int)substr($token, strrpos($token, '_') + 1);
		$expire = '3600';

		return $timestamp + $expire >= time();
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey)
	{
		return $this->getAuthKey() === $authKey;
	}





	public function validatePassword($password)
	{

		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey()
	{
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken()
	{
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken()
	{
		$this->password_reset_token = null;
	}
	//Check Auth 

	function GenerateRandString1($len, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		$string = '';
		for ($i = 0; $i < $len; $i++) {
			$pos = rand(0, strlen($chars) - 1);
			$string .= $chars[$pos];
		}
		return $string;
	}


	/**
	 * @return \yii\db\ActiveQuery
	 */
	public static function getVendorParentUser($store_id)
	{
		$vendor_details = VendorDetails::findOne(['id' => $store_id]);
		$user_id = $vendor_details->main_vendor_user_id ?? $vendor_details->user_id;
		return $user_id;
	}

	public static function getStoreHasUserId($guest_user_id, $vendor_details_id)
	{
		$stores_has_users = StoresHasUsers::findOne(['guest_user_id' => $guest_user_id, 'vendor_details_id' => $vendor_details_id]);
		return $stores_has_users ? $stores_has_users->id : null;
	}


	public static function assignUserToStore($userId, $store_id, $membership_id = null)
	{
		$stores_has_users = StoresHasUsers::findOne(['guest_user_id' => $userId, 'vendor_details_id' => $store_id]);
		if (!$stores_has_users) {
			$stores_has_users = new StoresHasUsers();
			$stores_has_users->guest_user_id  = $userId;
			$stores_has_users->vendor_details_id  = $store_id;
			$stores_has_users->vendor_user_id  = self::getVendorParentUser($store_id);
			$stores_has_users->status  = StoresHasUsers::STATUS_ACTIVE;
			$stores_has_users->save(false);
			if ($membership_id) {
				$stores_users_memberships = new StoresUsersMemberships();
				$stores_users_memberships->stores_has_users_id = $stores_has_users->id;
				$stores_users_memberships->membership_id = $membership_id;
				$stores_users_memberships->status = StoresUsersMemberships::STATUS_ACTIVE;
				$stores_users_memberships->save(false);
			}
		}
	}

	public function profileImage($profile_image, $user_name)
	{

		$image = str_replace('data:image/png;base64,', '', $profile_image);
		if (!empty($image)) {
			$ext = 'png';
			$image = str_replace(' ', '+', $image);

			// Decode the Base64 encoded Image
			$data1 = base64_decode($image);

			$image_name = $user_name . '_' . mt_rand() . '.' . $ext;
			$file = 'uploads/' . $image_name;
			// Save Image in the Image Directory
			$success = file_put_contents($file, $data1);
			if ($success === FALSE) {
				$data['profile_image'] = 'Not saved';
			} else {
				return  \Yii::$app->urlManager->createAbsoluteUrl('uploads') . '/' . $image_name;
			}
		}
	}
	public function UserNotification($user_id, $title, $body, $type, $api_key)
	{


		$auth_sess = new \app\modules\admin\models\AuthSession();
		$device_token =  $auth_sess->getDeviceToken($user_id);
		//var_dump($user_id); exit;
		$title = $title;
		$body = $body;
		$type = $type;
		$msg = array(
			'title' =>  $title,
			'body' => $body,
			'vibrate' => 1,
			'sound' => 1,
			'largeIcon' => 'large_icon',
			'smallIcon' => 'small_icon',
			'type' => $type,

		);
		$msg1 = array(
			'title' =>  $title,
			'body' => $body,
			'vibrate' => 1,
			'sound' => 1,
			'largeIcon' => 'large_icon',
			'smallIcon' => 'small_icon',
			// 'request_id' =>  $id,
		);
		$fields = array(
			'to' => $device_token,
			'collapse_key' => 'type_a',
			'data' => $msg,

		);


		$headers = array(
			'Authorization: key=' . $api_key,
			'Content-Type: application/json',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		//var_dump($result); exit;
		curl_close($ch);
		return $result;
	}

	public static function isAdmin()
	{
		if (empty(\Yii::$app->user->identity)) {

			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_ADMIN;
	}
	public static function isSubAdmin()
	{
		if (empty(\Yii::$app->user->identity)) {

			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_SUB_ADMIN;
	}


	public static function isQa()
	{
		if (empty(\Yii::$app->user->identity)) {

			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_QA;
	}


	public static function isACCountManager()
	{
		if (empty(\Yii::$app->user->identity)) {

			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_ACCOUNT_MANAGER;
	}


	public static function isManager()
	{
		if (empty(\Yii::$app->user->identity)) {
			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_MANAGER;
	}

	public static function isUser()
	{
		if (empty(\Yii::$app->user->identity)) {
			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_USER;
	}


	public static function isVendor()
	{
		if (empty(\Yii::$app->user->identity)) {
			return false;
		}
		return \Yii::$app->user->identity->user_role == self::ROLE_VENDOR;
	}


	public static function fullAccessRoles()
	{
		return [self::ROLE_ADMIN, self::ROLE_SUB_ADMIN, self::ROLE_MANAGER, self::ROLE_QA, self::ROLE_VENDOR];
	}

	public static function adminRoles()
	{
		return [self::ROLE_ADMIN, self::ROLE_SUB_ADMIN];
	}

	public static function editRoles()
	{
		return [self::ROLE_ADMIN, self::ROLE_SUB_ADMIN, self::ROLE_MANAGER];
	}


	public static function generateUniqueReferralCode($length = 8)
	{
		do {
			// Generate random alphanumeric code
			$code = strtoupper(Yii::$app->security->generateRandomString($length));
			$code = preg_replace('/[^A-Z0-9]/', '', $code); // Keep only alphanumeric

			// Ensure the referral code is unique in DB
			$exists = self::find()->where(['referral_code' => $code])->exists();
		} while ($exists);

		return $code;
	}



	public function behaviors()
	{
		return [
			'timestamp' => [
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => 'updated_at',
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
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuths()
	{
		return $this->hasMany(\app\modules\admin\models\Auth::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthSessions()
	{
		return $this->hasMany(\app\modules\admin\models\AuthSession::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBankDetails()
	{
		return $this->hasMany(\app\modules\admin\models\BankDetails::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBannerChargeLogs()
	{
		return $this->hasMany(\app\modules\admin\models\BannerChargeLogs::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBannerLogs()
	{
		return $this->hasMany(\app\modules\admin\models\BannerLogs::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBannerTimings()
	{
		return $this->hasMany(\app\modules\admin\models\BannerTimings::className(), ['update_user_id' => 'id']);
	}



	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBusinessDocuments()
	{
		return $this->hasMany(\app\modules\admin\models\BusinessDocuments::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBusinessImages()
	{
		return $this->hasMany(\app\modules\admin\models\BusinessImages::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBypassNumbers()
	{
		return $this->hasMany(\app\modules\admin\models\BypassNumbers::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCarts()
	{
		return $this->hasMany(\app\modules\admin\models\Cart::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCartItems()
	{
		return $this->hasMany(\app\modules\admin\models\CartItems::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCities()
	{
		return $this->hasMany(\app\modules\admin\models\City::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getComboOrders()
	{
		return $this->hasMany(\app\modules\admin\models\ComboOrder::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getComboOrderServicies()
	{
		return $this->hasMany(\app\modules\admin\models\ComboOrderServicies::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getComboPackages()
	{
		return $this->hasMany(\app\modules\admin\models\ComboPackages::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getComboPackagesCarts()
	{
		return $this->hasMany(\app\modules\admin\models\ComboPackagesCart::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getComboServices()
	{
		return $this->hasMany(\app\modules\admin\models\ComboServices::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCoupons()
	{
		return $this->hasMany(\app\modules\admin\models\Coupon::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCouponVendors()
	{
		return $this->hasMany(\app\modules\admin\models\CouponVendor::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCouponsApplieds()
	{
		return $this->hasMany(\app\modules\admin\models\CouponsApplied::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDays()
	{
		return $this->hasMany(\app\modules\admin\models\Days::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDeliveryAddresses()
	{
		return $this->hasMany(\app\modules\admin\models\DeliveryAddress::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getFcmNotifications()
	{
		return $this->hasMany(\app\modules\admin\models\FcmNotification::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getHomeVisitorsHasOrders()
	{
		return $this->hasMany(\app\modules\admin\models\HomeVisitorsHasOrders::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getNextVisitDetails()
	{
		return $this->hasMany(\app\modules\admin\models\NextVisitDetails::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderComplaints()
	{
		return $this->hasMany(\app\modules\admin\models\OrderComplaints::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderDetails()
	{
		return $this->hasMany(\app\modules\admin\models\OrderDetails::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderStatuses()
	{
		return $this->hasMany(\app\modules\admin\models\OrderStatus::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderTransactionDetails()
	{
		return $this->hasMany(\app\modules\admin\models\OrderTransactionDetails::className(), ['update_user_id' => 'id']);
	}



	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getQuizAnswers()
	{
		return $this->hasMany(\app\modules\admin\models\QuizAnswers::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getQuizQuestions()
	{
		return $this->hasMany(\app\modules\admin\models\QuizQuestions::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getQuizUserAnswers()
	{
		return $this->hasMany(\app\modules\admin\models\QuizUserAnswers::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getQuizzes()
	{
		return $this->hasMany(\app\modules\admin\models\Quizzes::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReelReports()
	{
		return $this->hasMany(\app\modules\admin\models\ReelReports::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReelShareCounts()
	{
		return $this->hasMany(\app\modules\admin\models\ReelShareCounts::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReelTags()
	{
		return $this->hasMany(\app\modules\admin\models\ReelTags::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReels()
	{
		return $this->hasMany(\app\modules\admin\models\Reels::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReelsLikes()
	{
		return $this->hasMany(\app\modules\admin\models\ReelsLikes::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReelsViewCounts()
	{
		return $this->hasMany(\app\modules\admin\models\ReelsViewCounts::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getRescheduleOrderLogs()
	{
		return $this->hasMany(\app\modules\admin\models\RescheduleOrderLogs::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServiceOrderImages()
	{
		return $this->hasMany(\app\modules\admin\models\ServiceOrderImages::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServicePinCodes()
	{
		return $this->hasMany(\app\modules\admin\models\ServicePinCode::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServiceTypes()
	{
		return $this->hasMany(\app\modules\admin\models\ServiceType::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServices()
	{
		return $this->hasMany(\app\modules\admin\models\Services::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShopLikes()
	{
		return $this->hasMany(\app\modules\admin\models\ShopLikes::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getShopReviews()
	{
		return $this->hasMany(\app\modules\admin\models\ShopReview::className(), ['update_user_id' => 'id']);
	}



	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getStoreServiceTypes()
	{
		return $this->hasMany(\app\modules\admin\models\StoreServiceTypes::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getStoreTimings()
	{
		return $this->hasMany(\app\modules\admin\models\StoreTimings::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getStoresHasUsers()
	{
		return $this->hasOne(\app\modules\admin\models\StoresHasUsers::className(), ['vendor_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSubCategories()
	{
		return $this->hasMany(\app\modules\admin\models\SubCategory::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSubscriptions()
	{
		return $this->hasMany(\app\modules\admin\models\Subscriptions::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSupportTickets()
	{
		return $this->hasMany(\app\modules\admin\models\SupportTickets::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSupportTicketsHasFiles()
	{
		return $this->hasMany(\app\modules\admin\models\SupportTicketsHasFiles::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorDetails()
	{
		return $this->hasMany(\app\modules\admin\models\VendorDetails::className(), ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorEarnings()
	{
		return $this->hasMany(\app\modules\admin\models\VendorEarnings::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorMainCategoryDatas()
	{
		return $this->hasMany(\app\modules\admin\models\VendorMainCategoryData::className(), ['create_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorPayouts()
	{
		return $this->hasMany(\app\modules\admin\models\VendorPayout::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorSubscriptions()
	{
		return $this->hasMany(\app\modules\admin\models\VendorSubscriptions::className(), ['update_user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallets()
	{
		return $this->hasMany(\app\modules\admin\models\Wallet::className(), ['updated_user_id' => 'id']);
	}








	public static function sendWhatsAppWelcomeMessage($to, $full_name)
	{
		$settings = new WebSetting();
		$whatsapp_token = $settings->getSettingBykey('whatsapp_token');
		$payload = [
			"messaging_product" => "whatsapp",
			"to" => $to,
			"type" => "template",
			"template" => [
				"name" => "welcome_user",
				"language" => [
					"code" => "en"
				],
				"components" => [
					[
						"type" => "header",
						"parameters" => [
							[
								"type" => "image",
								"image" => [
									"link" => "https://ik.imagekit.io/x2nh9ntpo/img_687dc3aa42c03_uE7bpI-E3"
								]
							]
						]
					],
					[
						"type" => "body",
						"parameters" => [
							[
								"type" => "text",
								"text" => $full_name
							]
						]
					]
				]
			]
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://graph.facebook.com/v19.0/734023276451663/messages',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $whatsapp_token,
				'Content-Type: application/json'
			],
		]);

		$response = curl_exec($curl);
		curl_close($curl);
	}

	public static function getUserAddress($id)
	{
		if (!empty($id)) {
		}
	}
	function getReferredUserscount($id)
	{
		$referreduserscount = User::find()->where([
			'referral_id' => $id
		])->count();
		return  $referreduserscount;
	}

	public function checkBUsinessDetails($user_id)
	{
		$vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();
		if (!empty($vendor_details)) {
			return true;
		} else {
			return false;
		}
	}



	public static function generateOtp()
	{
		return sprintf("%06d", random_int(100000, 999999));
	}


	public static function cleanupExpiredOtps($email)
	{
		EmailOtpVerifications::deleteAll([
			'and',
			['email' => $email],
			['status' => 'active'],
			['<', 'created_on', date('Y-m-d H:i:s', time() - self::OTP_EXPIRY_MINUTES * 60)]
		]);
	}


	public function checkBUsinessDetailsLocation($user_id)
	{
		$vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();
		if (empty($vendor_details->latitude) || empty($vendor_details->longitude) || empty($vendor_details->address)) {
			return false;
		} else {
			return true;
		}
	}

	public function checkBUsinessDetailsDocuments($user_id)
	{
		$vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();
		if (!empty($vendor_details)) {
			$main_category = MainCategory::find()->where(['id' => $vendor_details->main_category_id])->one();
			if ($main_category->is_required_documents == 1) {
				$business_documents = BusinessDocuments::find()->where(['vendor_details_id' => $vendor_details->id])->one();
				if (!empty($business_documents)) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		return false;
	}


	public function checkStoreStatus($user_id)
	{
		$vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();
		if (!empty($vendor_details) && $vendor_details->status == VendorDetails::STATUS_ACTIVE) {
			return true;
		} else {
			return false;
		}
	}



	public static function verifyOtpEmail($email, $otp)
	{

		$model = EmailOtpVerifications::find()
			->where(['email' => $email, 'status' => 1])
			->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', time() - self::OTP_EXPIRY_MINUTES * 60)])
			->orderBy(['created_on' => SORT_DESC])
			->one();




		if (!$model) {
			return false;
		}

		if (!password_verify($otp, $model->otp)) {
			return false;
		}

		$model->is_verified = 1;
		$model->updated_on = date('Y-m-d H:i:s');
		$model->update_user_id = Yii::$app->user->id ?? null;
		return $model->save(false);
	}


	public static function generateUniqueSlug($title, $vendor_details_id = '')
	{
		// Slugify the title
		$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

		if (!empty($vendor_details_id)) {
			// Append the vendor_details_id to make it unique
			return $slug . '-' . $vendor_details_id;
		} else {
			return $slug;
		}
	}

	public static function getVendorIdByUser()
	{
		$vendorDetails  =  VendorDetails::findOne(['user_id' => Yii::$app->user->identity->id]);
		if (!empty($vendorDetails)) {
			return $vendorDetails->id;
		}
		return null;
	}

	public static function getVendorIdByUserId($user_id)
	{
		$vendorDetails  =  VendorDetails::findOne(['user_id' => $user_id]);
		if (!empty($vendorDetails)) {
			return $vendorDetails->id;
		}
		return null;
	}

	public static function generateStoreTimings($vendor_details_id)
	{
		// Fetch all days
		$days = Days::find()->all();

		if (!empty($days)) {
			foreach ($days as $day_data) {
				// Check if store timings already exist for this vendor and day
				$store_timings = StoreTimings::find()
					->where(['vendor_details_id' => $vendor_details_id])
					->andWhere(['day_id' => $day_data->id])
					->one();

				// If no store timings exist, create new entry
				if (!$store_timings) {
					$store_timings = new StoreTimings();
					$store_timings->vendor_details_id = $vendor_details_id;
					$store_timings->day_id = $day_data->id;

					// Use strtotime to convert the time strings into timestamps
					$store_timings->start_time = date('10:00:00');
					$store_timings->close_time = date('18:00:00');

					// Set status as active
					$store_timings->status = StoreTimings::STATUS_ACTIVE;

					// Save the new store timings, handle errors gracefully
					if (!$store_timings->save(false)) {
						Yii::error("Failed to save store timings for vendor_id: {$vendor_details_id} and day_id: {$day_data->id}", __METHOD__);
					}
				}
			}
		} else {
			Yii::warning("No days found to generate store timings.", __METHOD__);
		}
	}


	public static function isRateLimited($email)
	{
		$count = EmailOtpVerifications::find()
			->where(['email' => $email])
			->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', time() - self::RATE_LIMIT_WINDOW)])
			->count();

		return $count >= self::RATE_LIMIT_ATTEMPTS;
	}




	public static function addReferralBonusOnFirstPaidOrder($user_id)
	{
		$user = self::findOne($user_id);

		if (!$user || empty($user->referral_id)) {
			return false;
		}

		$paidOrder = Orders::find()
			->where(['user_id' => $user_id, 'payment_status' => Orders::PAYMENT_DONE])
			->orderBy(['id' => SORT_ASC])
			->one();

		if (!$paidOrder) {
			return false;
		}

		// Ensure it's their FIRST paid order
		$paidOrderCount = Orders::find()
			->where(['user_id' => $user_id, 'payment_status' => Orders::PAYMENT_DONE])
			->count();

		if ($paidOrderCount > 1) {
			return false; // Only credit on first paid order
		}

		$referrer = self::findOne($user->referral_id);
		if (!$referrer) {
			return false;
		}

		$settings = new WebSetting();
		$REFERRAL_MAX_AMOUNT = $settings->getSettingBykey('REFERRAL_MAX_AMOUNT');

		// ✅ Log credit transaction in wallet
		$wallet = new Wallet(); // adjust namespace as per your app
		$wallet->order_id = $paidOrder->id;
		$wallet->user_id = $referrer->id;
		$wallet->amount = $REFERRAL_MAX_AMOUNT;
		$wallet->payment_type = Wallet::STATUS_CREDITED;
		$wallet->status = Wallet::STATUS_COMPLETED;
		$wallet->method_reason = "Referral bonus";
		$wallet->description = Yii::t('app', "Referral bonus for referring user ID: #{id}", ['id' => $user->id]);
		$wallet->save(false);
		return true;
	}




	public static function generateUniqueUserId($prefixChar = 'U')
	{
		$date = date('dmY');
		$basePattern = "EN-{$prefixChar}-{$date}-";

		// Find max user ID with today's date and prefix
		$latestUser = User::find()
			->where(['like', 'unique_user_id', $basePattern])
			->orderBy(['id' => SORT_DESC])
			->one();

		if ($latestUser && preg_match('/(\d{4})$/', $latestUser->id, $matches)) {
			$lastNumber = (int)$matches[1];
			$newNumber = $lastNumber + 1;
		} else {
			$newNumber = 1;
		}

		$paddedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
		return "EN-{$prefixChar}-{$date}-{$paddedNumber}";
	}

	// etst

	public function asJsonUser()
	{
		$data = [];
		$data['user_id'] = $this->id;
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['profile_image'] = $this->profile_image;
		$data['lat'] = $this->lat;
		$data['lng'] = $this->lng;
		$data['referral_code'] = $this->referral_code;


		$data['show_referral_tab'] = $this->show_referral_tab;



		$data['user_role'] = $this->user_role;

		if (empty($this->first_name) || empty($this->email) || empty($this->contact_no) || empty($this->gender)) {
			$data['basic_details_complete'] = false;
		} else {
			$data['basic_details_complete'] = true;
		}

		$settings = new WebSetting();
		$razorpay_username = $settings->getSettingBykey('razorpay_username');
		$data['razorpay_username']  = $razorpay_username;


		$orders = Orders::find()
			->where(['user_id' => $this->id])
			->andWhere(['status' => Orders::STATUS_SERVICE_COMPLETED])
			->orderBy(['id' => SORT_DESC])
			->one();
		if (!empty($orders)) {
			$data['last_order'] = $orders->asJsonMyOrdersUserInModel();
		} else {
			$data['last_order'] = '';
		}

		return $data;
	}




	public function asJsonUserClient($vendor_details_id = '')
	{
		$data = [];
		$stores_has_users = StoresHasUsers::find()->where(['guest_user_id' => $this->id])->andWhere(['vendor_details_id' => $vendor_details_id])->one();
		$stores_has_users_id  = $stores_has_users ? $stores_has_users->id : '';
		$stores_users_memberships = StoresUsersMemberships::find()->where(['stores_has_users_id' => $stores_has_users_id])->one();

		$data['client_id'] = $this->id; 
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['last_name'] = $this->last_name;
		$data['address'] = $this->address;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['profile_image'] = $this->profile_image;
		$data['user_role'] = $this->user_role;
		$data['status'] = $this->status;
		$data['total_spent_amount'] = '';
		$data['total_visit_count'] ='';
		$data['last_visit'] = '';
		$data['member_ship_validity'] = $stores_users_memberships ? $stores_users_memberships->asJson() : '';
		$data['clint_status'] = $stores_has_users ? $stores_has_users->status : '';
		$data['is_membership_active'] = $stores_users_memberships ? true : false;
		$data['deposit_amount'] = GuestUserDeposits::getAvailableDepositBalance($this->id,$stores_has_users_id);
		return $data;
	}



		public function asJsonUserClientView($vendor_details_id = '')
	{
		$data = [];
		$stores_has_users = StoresHasUsers::find()->where(['guest_user_id' => $this->id])->andWhere(['vendor_details_id' => $vendor_details_id])->one();
		$stores_has_users_id  = $stores_has_users ? $stores_has_users->id : '';
		$stores_users_memberships = StoresUsersMemberships::find()->where(['stores_has_users_id' => $stores_has_users_id])->one();

		$data['client_id'] = $this->id; 
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['last_name'] = $this->last_name;
		$data['address'] = $this->address;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['profile_image'] = $this->profile_image;
		$data['user_role'] = $this->user_role;
		$data['status'] = $this->status;
		$data['total_spent_amount'] = '';
		$data['total_visit_count'] ='';
		$data['last_visit'] = '';
		$data['clint'] = $stores_has_users ? $stores_has_users->asJsonView() : '';
		$data['is_membership_active'] = $stores_users_memberships ? true : false;
		$data['deposit_amount'] = GuestUserDeposits::getAvailableDepositBalance($this->id,$stores_has_users_id);
		return $data;
	}




	public function asJsonUserForOrder()
	{
		$data = [];
		$data['user_id'] = $this->id;
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['profile_image'] = $this->profile_image;
		$data['lat'] = $this->lat;
		$data['lng'] = $this->lng;

		$data['user_role'] = $this->user_role;

		if (empty($this->first_name) || empty($this->email) || empty($this->contact_no) || empty($this->gender)) {
			$data['basic_details_complete'] = false;
		} else {
			$data['basic_details_complete'] = true;
		}

		$settings = new WebSetting();
		$advance_pay = $settings->getSettingBykey('advance_pay');
		$data['advance_pay']  = (int)$advance_pay;



		return $data;
	}



	public function asJsonVendor()
	{
		$data = [];
		$data['user_id'] = $this->id;
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['email'] = $this->email;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['user_role'] = $this->user_role;
		$data['location'] = $this->location;
		$vendor_brands = VendorBrands::find()->where(['user_id' => $this->id])->one();
		if (empty($vendor_brands)) {
			$vendor_brands = VendorBrands::find()->where(['user_id' => $this->create_user_id])->one();
		}

		$data['brand_name'] = $vendor_brands ? $vendor_brands->name : '';
		$data['brand_logo'] = $vendor_brands ? $vendor_brands->brand_logo : '';


		$data['vendor_store_type'] = $this->vendor_store_type;
		$data['email_is_verified'] = $this->email_is_verified;
		if (empty($this->first_name) || empty($this->email) || empty($this->contact_no) || empty($this->gender)) {
			$data['basic_details_complete'] = false;
		} else {
			$data['basic_details_complete'] = true;
		}

		$data['business_details'] = $this->checkBUsinessDetails($this->id);


		$vendor_details_for_bank = VendorDetails::find()->where(['user_id' => $this->id])->one();

		if (!empty($vendor_details_for_bank->account_number)) {
			$data['bank_details'] = true;
			$data['business_name'] = $vendor_details_for_bank->business_name;
			$data['logo'] = $vendor_details_for_bank->logo;
		} else {
			$data['bank_details'] = false;
			$data['business_name'] = '';
			$data['logo'] = '';
		}

		$data['allow_onboarding'] = $this->allow_onboarding;

		$data['business_details_location'] = $this->checkBUsinessDetailsLocation($this->id);
		$vendor_details = VendorDetails::find()->where(['user_id' => $this->id])->one();
		if (!empty($vendor_details)) {
			$business_documents = BusinessDocuments::find()->where(['vendor_details_id' => $vendor_details->id])->one();
			$store_timings = StoreTimings::find()
				->where(['vendor_details_id' => $vendor_details->id])
				->exists();

			$data['store_timings'] = $store_timings ? true : false;



			if (!empty($business_documents)) {
				$data['business_documents'] = true;
			} else {
				$data['business_documents'] = false;
			}
		} else {
			$data['business_documents'] = false;
			$data['store_timings'] = false;
		}
		$main_category_id = [];

		if (!empty($vendor_details->vendorMainCategoryDatas)) {
			foreach ($vendor_details->vendorMainCategoryDatas as $main_category_data) {
				$main_category_id[] = $main_category_data->main_category_id;
			}
		}

		$main_category = MainCategory::find()->where(['id' => $main_category_id])->all();
		if (!empty($main_category)) {

			foreach ($main_category as $category) {
				$data['main_category'][] = [
					'id' => $category->id,
					'title' => $category->title,
					'image' => $category->image,
					'is_scheduled_next_visit' => $category->is_scheduled_next_visit,
				];
			}
		} else {

			$data['main_category'] = [];
		}




		// $data['business_documents'] = $this->checkBUsinessDetailsDocuments($this->id);
		$data['store_status'] = $this->checkStoreStatus($this->id);


		return $data;
	}













	public function asJsonVendorPersionalDetails()
	{
		$data = [];
		$data['user_id'] = $this->id;
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->gender;
		$data['user_role'] = $this->user_role;






		return $data;
	}





	public function getStaff()
	{
		return $this->hasOne(\app\modules\admin\models\Staff::className(), ['user_id' => 'id']);
	}

	public function asJsonHomeVisitor()
	{
		$data = [];
		$data['user_id'] = $this->id;
		$data['username'] = $this->username;
		$data['first_name'] = $this->first_name;
		$data['email'] = $this->email;
		$data['email_is_verified'] = $this->email_is_verified;
		$data['date_of_birth'] = $this->date_of_birth;
		$data['contact_no'] = $this->contact_no;
		$data['gender'] = $this->staff->gender ?? null;
		$data['lat'] = $this->lat;
		$data['lng'] = $this->lng;
		$data['user_role'] = $this->user_role;
		$data['profile_image'] = $this->staff->profile_image ?? null;

		return $data;
	}
	public function getOrders()
	{
		return $this->hasMany(\app\modules\admin\models\ComboOrder::class, ['order_id' => 'id']);
	}


	public function getWalletTransactions()
	{
		return $this->hasMany(Wallet::class, ['user_id' => 'id'])->orderBy(['id' => SORT_DESC]);
	}
	/**
	 * Validates associated VendorDetails data
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateVendorDetails($attribute, $params)
	{
		$vendorDetailsData = Yii::$app->request->post('VendorDetails', []);
		if (empty($vendorDetailsData)) {
			$this->addError($attribute, 'At least one store must be provided.');
			return;
		}

		if (!is_array($vendorDetailsData)) {
			$vendorDetailsData = [$vendorDetailsData];
		}

		foreach ($vendorDetailsData as $index => $data) {
			$vendorDetails = new VendorDetails();
			$vendorDetails->load($data, '');
			if (!$vendorDetails->validate()) {
				foreach ($vendorDetails->getErrors() as $field => $errors) {
					$this->addError($attribute, "Store #{$index} {$field}: " . implode(', ', $errors));
				}
			}
		}
	}

	/**
	 * Updates vendor_store_type based on the number of stores
	 * @return bool
	 */
	public function updateVendorStoreType()
	{
		$storeCount = VendorDetails::find()->where(['user_id' => $this->id])->count();
		$this->vendor_store_type = ($storeCount > 1) ? self::VENDOR_STORE_TYPE_MULTI : self::VENDOR_STORE_TYPE_SINGLE;
		return $this->save(false);
	}
}

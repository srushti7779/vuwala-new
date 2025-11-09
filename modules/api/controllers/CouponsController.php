<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\modules\admin\models\Coupon;
use app\modules\admin\models\CouponHasDays;
use app\modules\admin\models\CouponHasTimeSlots;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\Days;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\ServiceHasCoupons;
use app\modules\admin\models\Services;
use app\modules\admin\models\StoreTimings;
use Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class CouponsController extends BKController
{

    public $enableCsrfValidation = false;
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [



            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [

                    'class' => AccessRule::className()
                ],

                'rules' => [
                    [
                        'actions' => [

                            'get-time-slots-by-day',
                            'add-or-update-coupon',
                            'coupon-list',
                            'view-coupon-by-id',
                            'delete-coupon',
                            'services-list',
                            'coupon-change-status',
                            'delete-coupon'




                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [




                            'get-time-slots-by-day',
                            'add-or-update-coupon',
                            'coupon-list',
                            'view-coupon-by-id',
                            'delete-coupon',
                            'services-list',
                            'coupon-change-status',
                            'delete-coupon'










                        ],

                        'allow' => true,
                        'roles' => [

                            '?',
                            '*',

                        ]
                    ]
                ]
            ]

        ]);
    }





    public function actionGetTimeSlotsByDay()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Validate user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
            }

            // Validate required parameter 'day'
            if (empty($post['day'])) {
                throw new BadRequestHttpException(Yii::t("app", "day is required."));
            }

            // Fetch vendor
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE]);
            if (!$vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }
            $vendor_details_id = $vendorDetails->id;
            $day               = trim($post['day']);

            // Find day meta (Days table)
            $days = Days::findOne(['title' => $day]);
            if (empty($days)) {
                throw new NotFoundHttpException(Yii::t("app", "No matching day found in the system."));
            }
            $day_id = $days->id;

            // Find store timings for the specific day
            $storeTimings = StoreTimings::find()
                ->where([
                    'vendor_details_id' => $vendor_details_id,
                    'day_id'            => $day_id,
                    'status'            => StoreTimings::STATUS_ACTIVE
                ])
                ->one();

            if (empty($storeTimings)) {
                throw new NotFoundHttpException(Yii::t("app", "No store timings found for the selected day."));
            }

            // Always show full day's slots (no current-time restriction)
            $slots = VendorDetails::getServiceScheduleSlots(30, 0, $storeTimings->start_time, $storeTimings->close_time);

            // Return result
            if (!empty($slots)) {
                $data['status']  = self::API_OK;
                $data['details'] = $slots;
            } else {
                $data['status']  = self::API_NOK;
                $data['details'] = Yii::t("app", "No slots available for the selected date.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }




 


    public function actionAddOrUpdateCoupon()
{
    $data = [];
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);
    $post = json_decode(Yii::$app->request->getRawBody(), true);

    try {
        // Authentication
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
        }

        // Fetch vendor
        $vendorDetails = VendorDetails::findOne(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE]);
        if (!$vendorDetails) {
            throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
        }
        $vendor_details_id = $vendorDetails->id;

        // Required fields
        $requiredFields = ['name', 'code', 'discount', 'start_date'];
        foreach ($requiredFields as $field) {
            if (empty($post[$field])) {
                throw new BadRequestHttpException(Yii::t("app", "Required field missing: $field."));
            }
        }

        

        // Validate coupon code
        if (!preg_match('/^[A-Za-z0-9_]+$/', $post['code'])) {
            throw new BadRequestHttpException(Yii::t("app", "Coupon code must only contain alphanumeric characters and underscores, no spaces or special characters."));
        }




        // Parse dates
        $start_datetime = strtotime($post['start_date'] . ' ' . ($post['start_time'] ?? '00:00:00'));
        $end_datetime = !empty($post['end_date']) ? strtotime($post['end_date'] . ' ' . ($post['end_time'] ?? '23:59:59')) : null;
        $set_end_date = !empty($post['set_end_date']) ? $post['set_end_date'] : 0;
        if ($set_end_date && !$end_datetime) {
            throw new BadRequestHttpException(Yii::t("app", "End date is required when set_end_date is true."));
        }
        if ($set_end_date && $start_datetime >= $end_datetime) {
            throw new BadRequestHttpException(Yii::t("app", "End date must be greater than start date."));
        }

        // Enforce required fields for HAPPY_HOUR coupon
        $coupon_type = ($post['coupon_type'] ?? Coupon::COUPON_TYPE_NORMAL);
        $coupon_has_days = $post['coupon_has_days'] ?? [];

        if ($coupon_type === Coupon::COUPON_TYPE_HAPPY_HOUR) {
            if (empty($coupon_has_days) || !is_array($coupon_has_days)) {
                throw new BadRequestHttpException(Yii::t("app", "coupon_has_days is required for Happy Hour coupons."));
            }
            foreach ($coupon_has_days as $day) {
                if (empty($day['coupon_has_time_slots']) || !is_array($day['coupon_has_time_slots'])) {
                    throw new BadRequestHttpException(Yii::t("app", "coupon_has_time_slots is required for each day in Happy Hour coupons."));
                }
            }
        }

        // Existing coupon check
        $existingCoupon = Coupon::find()
            ->alias('c')
            ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
            ->where(['c.code' => $post['code'], 'cv.vendor_details_id' => $vendor_details_id])
            ->one();

        $isUpdate = $existingCoupon ? true : false;
        $coupon = $isUpdate ? $existingCoupon : new Coupon();

        // Set coupon fields
        $coupon->name = $post['name'];
        $coupon->description = $post['description'] ?? '';
        $coupon->code = $post['code'];
        $coupon->discount_type = $post['discount_type'] ?? null;
        $coupon->discount = $post['discount'];
        $coupon->max_discount = $post['max_discount'] ?? null;
        $coupon->min_cart = $post['min_order_amount'] ?? null;
        $coupon->max_use_of_coupon = $post['usage_limit_per_customer'] ?? null;
        $coupon->start_date = date('Y-m-d H:i:s', $start_datetime);
        $coupon->end_date = $set_end_date ? date('Y-m-d H:i:s', $end_datetime) : null;
        $coupon->is_global = Coupon::IS_GLOBAL_NO;
        $coupon->status = $post['status'] ?? Coupon::STATUS_ACTIVE;
        $coupon->offer_type = $post['offer_type'] ?? Coupon::OFFER_TYPE_ALL_SERVICES;
        $coupon->daily_redeem_limit = $post['daily_redeem_limit'] ?? null;
        $coupon->is_new_customer_offer = $post['is_new_customer_offer'] ?? 0;
        $coupon->is_auto_apply_offer = $post['is_auto_apply_offer'] ?? 0;
        $coupon->coupon_type = $coupon_type;

        if (!$coupon->save(false)) {
            throw new ServerErrorHttpException(Yii::t("app", "Failed to save coupon."));
        }

        // Associate vendor if new
        if (!$isUpdate) {
            $coupon_vendor = new CouponVendor();
            $coupon_vendor->coupon_id = $coupon->id;
            $coupon_vendor->vendor_details_id = $vendor_details_id;
            $coupon_vendor->status = CouponVendor::STATUS_ACTIVE;
            if (!$coupon_vendor->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to associate coupon with vendor."));
            }
        }

        // Offer type specific services logic
        if ((int)$coupon->offer_type === Coupon::OFFER_TYPE_SPECIFIC_SERVICES) {
            if (empty($post['service_ids']) || !is_array($post['service_ids'])) {
                throw new BadRequestHttpException(Yii::t("app", "Service IDs are required for specific service offers."));
            }
            // Mark all previous associations inactive
            ServiceHasCoupons::updateAll(
                [
                    'status' => ServiceHasCoupons::STATUS_INACTIVE,
                    'updated_on' => date('Y-m-d H:i:s'),
                    'update_user_id' => $user_id
                ],
                ['coupon_id' => $coupon->id]
            );
            // For each provided service: activate or create association
            foreach ($post['service_ids'] as $service) {
                $service_id = $service['service_id'] ?? null;
                if (!$service_id) {
                    throw new BadRequestHttpException(Yii::t("app", "Invalid service_id in service_ids array."));
                }
                $serviceHasCoupon = ServiceHasCoupons::findOne([
                    'coupon_id' => $coupon->id,
                    'service_id' => $service_id
                ]);
                if ($serviceHasCoupon) {
                    $serviceHasCoupon->status = ServiceHasCoupons::STATUS_ACTIVE;
                } else {
                    $serviceHasCoupon = new ServiceHasCoupons();
                    $serviceHasCoupon->service_id = $service_id;
                    $serviceHasCoupon->coupon_id = $coupon->id;
                    $serviceHasCoupon->status = ServiceHasCoupons::STATUS_ACTIVE;
                }
                if (!$serviceHasCoupon->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to associate coupon with service ID: $service_id."));
                }
            }
        }

        // Coupon days (handle inactivate before insert/update)
        if ($coupon_type == Coupon::COUPON_TYPE_HAPPY_HOUR) {
            // Inactivate all previous days and time slots for this coupon
            CouponHasDays::updateAll(
                ['status' => CouponHasDays::STATUS_INACTIVE],
                ['coupon_id' => $coupon->id]
            );
            CouponHasTimeSlots::updateAll(
                ['status' => CouponHasTimeSlots::STATUS_INACTIVE],
                ['coupon_id' => $coupon->id]
            );


            foreach ($coupon_has_days as $day) {
                $dayName = $day['day'] ?? null;
                if (!$dayName || !in_array($dayName, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])) {
                    throw new BadRequestHttpException(Yii::t("app", "Invalid day name: {$dayName}."));
                }

                // Create or update coupon day
                $couponDay = CouponHasDays::findOne(['coupon_id' => $coupon->id, 'day' => $dayName]);
                if ($couponDay) {
                    $couponDay->status = CouponHasDays::STATUS_ACTIVE;
                } else {
                    $couponDay = new CouponHasDays();
                    $couponDay->coupon_id = $coupon->id;
                    $couponDay->day = $dayName;
                    $couponDay->status = CouponHasDays::STATUS_ACTIVE;
                }
                if (!$couponDay->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to associate coupon with day: {$dayName}."));
                }


                
                // Handle time slots for this day
                $timeSlots = $day['coupon_has_time_slots'] ?? [];
                foreach ($timeSlots as $timeSlot) {
                    $start_time = $timeSlot['start_time'] ?? null;
                    $end_time = $timeSlot['end_time'] ?? null;
                    if (!$start_time || !$end_time) {
                        throw new BadRequestHttpException(Yii::t("app", "Start time and end time are required for time slots."));
                    }
                    // Validate time format
                    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $start_time) ||
                        !preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $end_time)) {
                        throw new BadRequestHttpException(Yii::t("app", "Invalid time format for time slot: {$start_time} - {$end_time}."));
                    }
                    if (strtotime($start_time) >= strtotime($end_time)) {
                        throw new BadRequestHttpException(Yii::t("app", "End time must be greater than start time for time slot: {$start_time} - {$end_time}."));
                    }

                    $couponTimeSlot = CouponHasTimeSlots::findOne([
                        'coupon_id' => $coupon->id,
                        'coupon_has_day_id' => $couponDay->id,
                        'start_time' => $start_time,
                        'end_time' => $end_time
                    ]);
                    if ($couponTimeSlot) {
                        $couponTimeSlot->status = CouponHasTimeSlots::STATUS_ACTIVE;
                    } else {
                        $couponTimeSlot = new CouponHasTimeSlots();
                        $couponTimeSlot->coupon_id = $coupon->id;
                        $couponTimeSlot->coupon_has_day_id = $couponDay->id;
                        $couponTimeSlot->start_time = $start_time;
                        $couponTimeSlot->end_time = $end_time;
                        $couponTimeSlot->status = CouponHasTimeSlots::STATUS_ACTIVE;
                    }
                    if (!$couponTimeSlot->save(false)) {
                        throw new ServerErrorHttpException(Yii::t("app", "Failed to associate coupon with time slot: {$start_time} - {$end_time}."));
                    }
                }
            }
        }

        // Success response
        $data['status'] = self::API_OK;
        $data['message'] = $isUpdate
            ? Yii::t("app", "Coupon updated successfully.")
            : Yii::t("app", "Coupon added successfully.");
        $data['details'] = $coupon->asJsonView();
    } catch (\Throwable $e) {
        // Log the error for debugging
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $post,
        ], __METHOD__);
        $data['status'] = self::API_NOK;
        $data['error'] = $e instanceof \yii\web\HttpException
            ? $e->getMessage()
            : (isset($e->statusCode) ? $e->statusCode : 500) . ': ' . $e->getMessage();
    }

    return $this->sendJsonResponse($data);
}



    public function actionCouponList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Set up pagination with ActiveDataProvider
            $pageSize = ! empty($post['page_size']) ? (int) $post['page_size'] : 12;
            $page     = ! empty($post['page']) ? (int) $post['page'] - 1 : 0;
            $coupon_type     = ! empty($post['coupon_type']) ? $post['coupon_type']  : '';


            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => Coupon::find()
                    ->alias('c')
                    ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                    ->where(['cv.vendor_details_id' => $vendor_details_id])
                    ->andFilterWhere(['c.coupon_type' => $coupon_type])
                    ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE]),
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page,
                ],
                'sort'       => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);

            $coupons    = $dataProvider->getModels();
            $totalCount = $dataProvider->getTotalCount();



            // Prepare the coupon data using asJson()
            $couponData = [];
            foreach ($coupons as $coupon) {
                $couponData[] = $coupon->asJson();
            }

            // Prepare pagination data
            $pagination = [
                'total_count' => $totalCount,
                'page'        => $dataProvider->pagination->page + 1, // Add 1 to adjust for 0-based index
                'page_size'   => $pageSize,
                'total_pages' => ceil($totalCount / $pageSize),
            ];

            // Prepare successful response
            $data['status']     = self::API_OK;
            $data['message']    = Yii::t("app", "Coupons retrieved successfully.");
            $data['coupons']    = $couponData;
            $data['pagination'] = $pagination;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }




    public function actionViewCouponById()
    {
        $data      = [];
        $post      = Yii::$app->request->post();
        $coupon_id = $post['coupon_id'] ?? null; // Ensure coupon_id is provided
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        try {
            // Validate if coupon_id is provided
            if (empty($coupon_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Coupon ID is required."));
            }

            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Fetch the coupon by ID and vendor details
            $coupon = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.id' => $coupon_id, 'cv.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE])
                ->one();

            if ($coupon) {
                $data['status']  = self::API_OK;
                $data['details'] = $coupon->asJsonView();
                $data['message'] = Yii::t("app", "Coupon details retrieved successfully.");
            } else {
                throw new NotFoundHttpException(Yii::t("app", "Coupon not found."));
            }
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteCoupon()
    {
        $data      = [];
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);
        $post      = Yii::$app->request->post();
        $coupon_id = $post['coupon_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate coupon_id
            if (empty($coupon_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Coupon ID is required."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Fetch the coupon by ID and vendor details
            $coupon = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.id' => $coupon_id, 'cv.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE])
                ->one();

            $coupon_vendor = CouponVendor::find()
                ->where(['coupon_id' => $coupon_id, 'vendor_details_id' => $vendor_details_id])
                ->one();
            if ($coupon_vendor) {
                $coupon_vendor->status = CouponVendor::STATUS_DELETE;
                if (! $coupon_vendor->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to delete the coupon vendor association."));
                }
            }

            if (! $coupon) {
                throw new NotFoundHttpException(Yii::t("app", "Coupon not found or already deleted."));
            }

            // Mark the coupon as deleted
            $coupon->status = Coupon::STATUS_DELETE;

            if (! $coupon->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to delete the coupon."));
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Coupon deleted successfully.");
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionServicesList()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }




            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and subcategory
            $query = Services::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE]])
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ]);


            $services = $query->all();
            // Check if services exist
            if (empty($services)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No services found for the selected subcategory.');
            } else {
                // Format service data
                $list = [];
                foreach ($services as $service) {
                    $list[] = $service->asJsonForCouponList();
                }

                // Prepare success response
                $data['status']   = self::API_OK;
                $data['message']  = Yii::t('app', 'Services retrieved successfully.');
                $data['services'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


    public function actionCouponChangeStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }
            $status    = $post['status'] ?? null;
            $coupon_id = $post['coupon_id'] ?? null;

            // Validate status
            if (! in_array($status, [Coupon::STATUS_ACTIVE, Coupon::STATUS_INACTIVE])) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Invalid status value.'));
            }
            if (empty($coupon_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Coupon ID is required.'));
            }

            $coupon = Coupon::findOne(['id' => $coupon_id]);
            if (! $coupon) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Coupon not found.'));
            }

            $coupon->status = $status;
            if (! $coupon->save(false)) {
                throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update coupon status.'));
            }

            // Update all vendor links for this coupon
            $coupon_vendors = CouponVendor::find()->where(['coupon_id' => $coupon_id])->all();
            if (empty($coupon_vendors)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Coupon vendor(s) not found.'));
            }
            foreach ($coupon_vendors as $coupon_vendor) {
                $coupon_vendor->status = $status;
                if (! $coupon_vendor->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update coupon vendor status.'));
                }
            }

            $data = [
                'status'     => self::API_OK,
                'message'    => Yii::t('app', 'Coupon status updated successfully.'),
                'coupon_id'  => $coupon_id,
                'new_status' => $status,
            ];
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }
}

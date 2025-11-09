<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\models\User;
use app\modules\api\controllers\BKController;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\modules\admin\models\Banner;
use app\modules\admin\models\BannerChargeLogs;
use app\modules\admin\models\BannerTimings;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorMainCategoryData;
use DateTime;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class BannerController extends BKController

{

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

                            'create-or-update-banner',
                            'banners-list',
                            'view-banner-by-id',
                            'change-banner-status',
                            'banners-dashboard',
                            'instagram-auth',
                            'instagram-callback',
                            'vendor-selected-main-category',
                            'performance-over-time',
                            'best-performance-time-slots'

                        ],

                        'allow' => true,

                        'roles' => [

                            '@'

                        ]

                    ],

                    [

                        'actions' => [



                            'create-or-update-banner',
                            'banners-list',
                            'view-banner-by-id',
                            'change-banner-status',
                            'banners-dashboard',
                            'instagram-auth',
                            'instagram-callback',
                            'vendor-selected-main-category',
                            'performance-over-time',
                            'best-performance-time-slots'
                        ],

                        'allow' => true,

                        'roles' => [

                            '?',

                            '*',

                            //'@' 

                        ]

                    ]

                ]

            ]

        ]);
    }






    public function actionInstagramAuth()
    {
        $appId = "639918695477890";
        $redirectUri = Url::base(true) . "/api/banner/instagram-callback";

        $scopes = "instagram_basic,instagram_content_publish,pages_show_list,pages_read_engagement";

        $authUrl = "https://www.facebook.com/v20.0/dialog/oauth?"
            . "client_id={$appId}"
            . "&redirect_uri={$redirectUri}"
            . "&scope={$scopes}"
            . "&response_type=code";

        return $this->redirect($authUrl);
    }


    public function actionInstagramCallback()
    {
        $code = Yii::$app->request->get('code');
        $appId = "639918695477890";
        $appSecret = "1f8a4eca84abc0d38321fe2add56b752";
        $redirectUri = Url::base(true) . "/api/banner/instagram-callback";

        try {
            // Step 1: Exchange code → short-lived token
            $url = "https://graph.facebook.com/v20.0/oauth/access_token"
                . "?client_id={$appId}&redirect_uri={$redirectUri}&client_secret={$appSecret}&code={$code}";

            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (empty($data['access_token'])) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Failed to get short-lived token',
                    'details' => $data
                ]);
            }

            $shortLivedToken = $data['access_token'];

            // Step 2: Exchange short-lived → long-lived token (60 days)
            $exchangeUrl = "https://graph.facebook.com/v20.0/oauth/access_token"
                . "?grant_type=fb_exchange_token&client_id={$appId}&client_secret={$appSecret}&fb_exchange_token={$shortLivedToken}";

            $longResponse = file_get_contents($exchangeUrl);
            $longData = json_decode($longResponse, true);

            if (empty($longData['access_token'])) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Failed to exchange long-lived token',
                    'details' => $longData
                ]);
            }

            $longLivedToken = $longData['access_token'];

            // Step 3: Get user’s pages (to map IG account)
            $pagesUrl = "https://graph.facebook.com/v20.0/me/accounts?access_token={$longLivedToken}";
            $pagesResponse = file_get_contents($pagesUrl);
            $pagesData = json_decode($pagesResponse, true);

            if (empty($pagesData['data'][0]['id'])) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'No connected Facebook Page found',
                    'details' => $pagesData
                ]);
            }

            $pageId = $pagesData['data'][0]['id'];

            // Step 4: Get Instagram Business Account ID linked to the Page
            $igUrl = "https://graph.facebook.com/v20.0/{$pageId}?fields=instagram_business_account&access_token={$longLivedToken}";
            $igResponse = file_get_contents($igUrl);
            $igData = json_decode($igResponse, true);

            $igUserId = $igData['instagram_business_account']['id'] ?? null;

            if (!$igUserId) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Instagram business account not found',
                    'details' => $igData
                ]);
            }

            // Step 5: Save token + ig_user_id in DB
            // $vendorId = Yii::$app->user->id; // or pass vendor_id in request
            // Yii::$app->db->createCommand()->insert('instagram_tokens', [
            //     'vendor_id'    => $vendorId,
            //     'ig_user_id'   => $igUserId,
            //     'access_token' => $longLivedToken,
            //     'token_type'   => 'long-lived',
            //     'expires_in'   => $longData['expires_in'] ?? null,
            //     'created_at'   => date('Y-m-d H:i:s'),
            //     'updated_at'   => date('Y-m-d H:i:s'),
            // ])->execute();

            return $this->sendJsonResponse([
                'status' => self::API_OK,
                'message' => 'Instagram connected successfully',
                'ig_user_id' => $igUserId,
                'token' => $longLivedToken
            ]);
        } catch (\Exception $e) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => $e->getMessage()
            ]);
        }
    }



    public function actionPublishReel()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userAccessToken = "VENDOR_SAVED_TOKEN"; // should come from DB (vendor)
        $igUserId        = "getjobshub"; // vendor’s IG business id
        $videoUrl        = "https://drsrikanthponnada.com/uploads/video/video_1756021721.mp4";
        $caption         = "My first reel from API";

        try {
            // Step 1: Create video container
            $createContainerUrl = "https://graph.facebook.com/v20.0/$igUserId/media";

            $postFields = [
                'media_type'   => 'VIDEO',
                'video_url'    => $videoUrl,
                'caption'      => $caption,
                'access_token' => $userAccessToken,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $createContainerUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $containerResponse = json_decode($response, true);

            if (empty($containerResponse['id'])) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => $containerResponse['error']['message'] ?? 'Failed to create reel container'
                ]);
            }

            // Step 2: Publish video
            $publishUrl = "https://graph.facebook.com/v20.0/$igUserId/media_publish";
            $publishFields = [
                'creation_id'  => $containerResponse['id'],
                'access_token' => $userAccessToken,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $publishUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $publishFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $publishResult = curl_exec($ch);
            curl_close($ch);

            $publishResponse = json_decode($publishResult, true);

            return $this->sendJsonResponse([
                'status' => self::API_OK,
                'result' => $publishResponse
            ]);
        } catch (\Exception $e) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => $e->getMessage()
            ]);
        }
    }






    public function actionCreateOrUpdateBanner()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = [];
        $validationErrors = [];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // Fix: Parse JSON from raw body
            $rawBody = Yii::$app->request->getRawBody();
            $postData = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \yii\web\BadRequestHttpException('Invalid JSON format.');
            }

            $isUpdate = !empty($postData['id']);

            if ($isUpdate) {
                $model = Banner::findOne($postData['id']);
                if (!$model) {
                    throw new \yii\web\NotFoundHttpException('Banner not found for update.');
                }
            } else {
                $model = new Banner();
                $model->status = Banner::STATUS_ACTIVE;
            }

            // --- REQUIRED FIELDS VALIDATION ---
            $requiredFields = [
                'main_category_id' => 'Main Category ID',
                'position' => 'Position',
                'type_id' => 'Type ID',
                'sort_order' => 'Sort Order',
                'title' => 'Title',
                'description' => 'Description',
                'image' => 'Image',
                'start_date' => 'Start Date',
                'end_date' => 'End Date'
            ];

            foreach ($requiredFields as $field => $fieldName) {
                if (empty($postData[$field])) {
                    $validationErrors[] = "$fieldName is required.";
                }
            }

            // If required fields are missing, return early
            if (!empty($validationErrors)) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'Required fields missing';
                $data['errors'] = $validationErrors;
                return $this->sendJsonResponse($data);
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);

            // Assign values
            $model->main_category_id = $postData['main_category_id'];
            $model->vendor_details_id = $vendor_details_id;
            $model->position = $postData['position'];
            $model->type_id = $postData['type_id'];
            $model->sort_order = $postData['sort_order'];
            $model->views_count = $postData['views_count'] ?? $model->views_count ?? 0;
            $model->title = $postData['title'];
            $model->description = $postData['description'];
            $model->image = $postData['image'];
            if (empty($model->start_date)) {
                $model->start_date = $postData['start_date'];
            }
            $model->end_date = $postData['end_date'];
            $model->is_top_banner = $postData['is_top_banner'] ?? $model->is_top_banner ?? '0';
            $model->is_pop_up_banner = $postData['is_pop_up_banner'] ?? $model->is_pop_up_banner ?? '0';

            // --- BUSINESS LOGIC VALIDATION ---
            // 1. Date format validation
            if (!$this->validateDateFormat($model->start_date)) {
                $validationErrors[] = 'Start date must be in YYYY-MM-DD format.';
            }
            if (!$this->validateDateFormat($model->end_date)) {
                $validationErrors[] = 'End date must be in YYYY-MM-DD format.';
            }

            // 2. Banner date validation
            if (isset($model->start_date, $model->end_date)) {
                if (strtotime($model->start_date) > strtotime($model->end_date)) {
                    $validationErrors[] = 'Banner start_date must be less than end_date.';
                }

                // --- CHANGED: Only enforce "start date not in past" for CREATE (not for UPDATE)
                // This allows editing existing banners whose start_date is already in the past.
                if (!$isUpdate) {
                    $currentDate = date('Y-m-d');
                    if (strtotime($model->start_date) < strtotime($currentDate)) {
                        $validationErrors[] = 'Start date cannot be in the past.';
                    }
                }
            }

            // 3. Numeric field validation
            if (!is_numeric($model->main_category_id) || $model->main_category_id <= 0) {
                $validationErrors[] = 'Main Category ID must be a positive number.';
            }
            if (!is_numeric($model->type_id) || $model->type_id <= 0) {
                $validationErrors[] = 'Type ID must be a positive number.';
            }
            if (!is_numeric($model->position) || $model->position < 0) {
                $validationErrors[] = 'Position must be a non-negative number.';
            }
            if (!is_numeric($model->sort_order) || $model->sort_order < 0) {
                $validationErrors[] = 'Sort order must be a non-negative number.';
            }

            // 4. String length validation
            if (strlen($model->title) > 255) {
                $validationErrors[] = 'Title cannot exceed 255 characters.';
            }
            if (strlen($model->description) > 1000) {
                $validationErrors[] = 'Description cannot exceed 1000 characters.';
            }

            // 5. banner_timings validation
            if (isset($postData['banner_timings']) && is_array($postData['banner_timings'])) {
                if (empty($postData['banner_timings'])) {
                    $validationErrors[] = 'At least one banner timing is required.';
                } else {
                    foreach ($postData['banner_timings'] as $idx => $timing) {
                        if (empty($timing['start_time']) || empty($timing['end_time'])) {
                            $validationErrors[] = "Timing row #" . ($idx + 1) . ": Start time and End time are required.";
                            continue;
                        }

                        $start_time = self::normalizeTime($timing['start_time']);
                        $end_time = self::normalizeTime($timing['end_time']);

                        if (!$start_time || !$end_time) {
                            $validationErrors[] = "Timing row #" . ($idx + 1) . ": Invalid time format. Use HH:MM format.";
                            continue;
                        }

                        if (strtotime($start_time) >= strtotime($end_time)) {
                            $validationErrors[] = "Timing row #" . ($idx + 1) . ": Start time ($start_time) must be less than End time ($end_time).";
                        }
                    }
                }
            }

            // If any validation error, return response and do not save
            if (!empty($validationErrors)) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'Validation failed';
                $data['errors'] = $validationErrors;
                return $this->sendJsonResponse($data);
            }

            // --- SAVING ---
            if ($model->save(false)) {
                // Save banner timings
                if (isset($postData['banner_timings']) && is_array($postData['banner_timings'])) {
                    // On update, remove previous timings for this banner
                    if ($isUpdate) {
                        BannerTimings::deleteAll(['banner_id' => $model->id]);
                    }
                    foreach ($postData['banner_timings'] as $timing) {
                        $start_time = self::normalizeTime($timing['start_time']);
                        $end_time = self::normalizeTime($timing['end_time']);

                        $bannerTiming = new BannerTimings();
                        $bannerTiming->banner_id = $model->id;
                        $bannerTiming->start_time = $start_time;
                        $bannerTiming->end_time = $end_time;
                        $bannerTiming->status = 1;

                        if ($isUpdate) {
                            $bannerTiming->updated_on = date('Y-m-d H:i:s');
                            $bannerTiming->update_user_id = $user_id;
                        }

                        $bannerTiming->save(false);
                    }
                }

                $data['status'] = self::API_OK;
                $data['message'] = $isUpdate ? 'Banner updated successfully.' : 'Banner created successfully.';
                $data['banner_id'] = $model->id;
            } else {
                $data['status'] = self::API_NOK;
                $data['error'] = $isUpdate ? 'Failed to update banner.' : 'Failed to create banner.';
                $data['errors'] = $model->getErrors();
            }
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Not Found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::error('Bad Request: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('Error in banner create/update: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = 'An unexpected error occurred. Please try again.';
        }

        return $this->sendJsonResponse($data);
    }


    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function validateDateFormat($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Normalize time string to MySQL TIME format (HH:MM:SS)
     */
    protected static function normalizeTime($timeStr)
    {
        if (empty($timeStr)) return null;

        // If it's already HH:MM, add ":00"
        if (preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) {
            return $timeStr . ':00';
        }

        // Use strtotime for formats like '9 AM', '11 PM'
        $timestamp = strtotime($timeStr);
        if ($timestamp !== false) {
            return date('H:i:s', $timestamp);
        }

        return null;
    }




    public function actionBannersList()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            $post    = Yii::$app->request->post();

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details = VendorDetails::findOne(['user_id' => $user_id]);
            if (!$vendor_details) {
                $data['status']  = self::API_NOK;
                $data['error']   = Yii::t('app', 'Vendor details not found for the logged-in user.');
                return $this->sendJsonResponse($data);
            }

            $vendor_details_id = $vendor_details->id;
            $search            = trim($post['search'] ?? '');
            $status            = $post['status'] ?? null;

            // pagination inputs
            $page    = isset($post['page']) ? (int)$post['page'] : 1;
            $perPage = isset($post['per_page']) ? (int)$post['per_page'] : 10;
            $page = $page < 1 ? 1 : $page;
            $perPage = $perPage < 1 ? 10 : $perPage;
            $maxPerPage = 100;
            $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

            // Build query with filters
            $query = Banner::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['in', 'status', [Banner::STATUS_ACTIVE, Banner::STATUS_INACTIVE, Banner::STATUS_PAUSED]]);

            // Filter by status if provided
            if ($status !== null && $status !== '') {
                $query->andWhere(['status' => $status]);
            }

            // Search by title or description (case-insensitive)
            if ($search !== '') {
                $query->andWhere([
                    'or',
                    ['like', 'title', $search],
                    ['like', 'description', $search],
                ]);
            }

            // total count (before offset/limit)
            $total = (int) (clone $query)->count();

            // ordering & pagination
            $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 0;
            $offset = ($page - 1) * $perPage;

            // default ordering - newest first (change if you prefer)
            $banners = $query->orderBy(['id' => SORT_DESC])->offset($offset)->limit($perPage)->all();

            $list = [];
            if (!empty($banners)) {
                foreach ($banners as $banner) {
                    $list[] = method_exists($banner, 'asJsonVendor') ? $banner->asJsonVendor() : $banner->toArray();
                }
            }

            $data['status'] = self::API_OK;
            $data['details'] = $list;
            $data['pagination'] = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ];

            if (empty($list)) {
                // keep response OK but give friendly note
                $data['message'] = Yii::t('app', 'No banners found for the specified criteria.');
            }
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);

            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred while fetching banners.'),
                'error_code' => 500,
            ];
        }

        return $this->sendJsonResponse($data);
    }











    public function actionViewBannerById()
    {
        $data = [];

        try {

            $headers    = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth       = new AuthSettings();
            $user_id    = $auth->getAuthSession($headers);
            $id         = Yii::$app->request->get('id');
            $post       = Yii::$app->request->post();
            $start_date = $post['start_date'] ?? null;
            $end_date   = $post['end_date'] ?? null;

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details    = VendorDetails::findOne(['user_id' => $user_id]);
            $vendor_details_id = $vendor_details->id ?? null;

            if (empty($vendor_details_id)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $banner = Banner::findOne([
                'id'                => $id,
                'vendor_details_id' => $vendor_details_id,
            ]);

            if ($banner === null) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Banner not found.'));
            }

            $data['status']  = self::API_OK;
            $data['details'] = $banner->asJsonVendorView($post);
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



    public function actionChangeBannerStatus()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $banner_id = Yii::$app->request->post('banner_id') ?? Yii::$app->request->getBodyParam('banner_id');

            $status = Yii::$app->request->post('status') ?? Yii::$app->request->getBodyParam('status');

            if (empty($banner_id) || ! isset($status)) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => 'Missing banner_id or status.',
                ]);
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $banner = Banner::findOne([
                'id'                => $banner_id,
                'vendor_details_id' => $vendor->id,
            ]);

            if (! $banner) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => 'Banner not found or access denied.',
                ]);
            }

            $banner->status = $status;
            if ($banner->save(false)) {
                return $this->asJson([
                    'status'  => self::API_OK,
                    'message' => 'Banner status updated successfully.',
                    'details' => $banner->asJsonVendor(),
                ]);
            } else {
                return $this->asJson([
                    'status'            => self::API_NOK,
                    'error'             => 'Failed to update banner status.',
                    'validation_errors' => $banner->getErrors(),
                ]);
            }
        } catch (\yii\web\HttpException $e) {
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Yii::error("Error in actionChangeBannerStatus: " . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => 'An error occurred while updating banner status.',
            ]);
        }
    }



    public function actionBannersDashboard()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details    = VendorDetails::findOne(['user_id' => $user_id]);
            $vendor_details_id = $vendor_details->id ?? null;

            if (! $vendor_details_id) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No vendor details found for this user.'));
            }

            // Banner status constants (change as per your model)
            $statusActive  = Banner::STATUS_ACTIVE ?? 1;
            $statusPending = Banner::STATUS_PENDING ?? 0;

            // 1. Banner counts
            $activeBanners  = Banner::find()->where(['vendor_details_id' => $vendor_details_id, 'status' => $statusActive])->count();
            $pendingBanners = Banner::find()->where(['vendor_details_id' => $vendor_details_id, 'status' => $statusPending])->count();
            $totalBanners   = Banner::find()->where(['vendor_details_id' => $vendor_details_id])->count();

            // 2. Banner IDs
            $bannerIds = Banner::find()->select('id')->where(['vendor_details_id' => $vendor_details_id])->column();

            // 3. Totals from banner_charge_logs
            $totalViews  = 0;
            $totalClicks = 0;

            if ($bannerIds) {
                $totalViews = (int) BannerChargeLogs::find()
                    ->where(['banner_id' => $bannerIds, 'action' => 'view'])
                    ->count();

                $totalClicks = (int) BannerChargeLogs::find()
                    ->where(['banner_id' => $bannerIds, 'action' => 'click'])
                    ->count();
            }

            // 4. CTR calculation
            $ctr = ($totalViews > 0) ? round(($totalClicks / $totalViews) * 100, 2) : 0.00;

            $data['status']    = self::API_OK;
            $data['dashboard'] = [
                'active_banners'  => (int) $activeBanners,
                'pending_banners' => (int) $pendingBanners,
                'total_banners'   => (int) $totalBanners,
                'total_views'     => (int) $totalViews,
                'total_clicks'    => (int) $totalClicks,
                'ctr_percent'     => $ctr, // e.g., 12.34 means 12.34%
            ];
            $data['message'] = Yii::t('app', 'Banners dashboard stats loaded.');
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


    public function actionVendorSelectedMainCategory()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            $vendor_main_category_data = VendorMainCategoryData::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['status' => VendorMainCategoryData::STATUS_ACTIVE])
                ->all();

            $list = [];
            if (! empty($vendor_main_category_data)) {
                foreach ($vendor_main_category_data as $index => $item) {
                    $jsonItem          = $item->asJson();
                    $jsonItem['index'] = $index;
                    $list[]            = $jsonItem;
                }
            }

            if (empty($list)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No main category found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionPerformanceOverTime()
    {
        $data = [];
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (!$vendor) {
                throw new NotFoundHttpException('Vendor details not found.');
            }

            $post = Yii::$app->request->post();
            $banner_id = $post['banner_id'] ?? null;
            $start_date = !empty($post['start_date']) ? $post['start_date'] : date('Y-m-d', strtotime('-30 days'));
            $end_date = !empty($post['end_date']) ? $post['end_date'] : date('Y-m-d');

            if ($banner_id) {
                $banner = Banner::findOne(['id' => $banner_id, 'vendor_details_id' => $vendor->id]);
                if (!$banner) {
                    throw new NotFoundHttpException('Banner not found.');
                }
                $banner_ids = [$banner_id];
            } else {
                $banner_ids = Banner::find()->select('id')->where(['vendor_details_id' => $vendor->id])->column();
            }

            if (empty($banner_ids)) {
                $data['status'] = self::API_OK;
                $data['message'] = 'No banners found.';
                $data['data'] = ['views' => [], 'clicks' => []];
                return $this->sendJsonResponse($data);
            }

            if (!$this->validateDateFormat($start_date) || !$this->validateDateFormat($end_date)) {
                throw new BadRequestHttpException('Invalid date format. Use YYYY-MM-DD.');
            }

            if (strtotime($start_date) > strtotime($end_date)) {
                throw new BadRequestHttpException('Start date must be before end date.');
            }

            // Fetch views data
            $views_data = BannerChargeLogs::find()
                ->select(["DATE(performed_at) AS period", 'COUNT(*) AS views'])
                ->where(['banner_id' => $banner_ids])
                ->andWhere(['action' => 'view'])
                ->andWhere(['between', 'performed_at', $start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                ->groupBy('period')
                ->indexBy('period')
                ->asArray()
                ->all();

            // Fetch clicks data
            $clicks_data = BannerChargeLogs::find()
                ->select(["DATE(performed_at) AS period", 'COUNT(*) AS clicks'])
                ->where(['banner_id' => $banner_ids])
                ->andWhere(['action' => 'click'])
                ->andWhere(['between', 'performed_at', $start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                ->groupBy('period')
                ->indexBy('period')
                ->asArray()
                ->all();

            $periods = array_unique(array_merge(array_keys($views_data), array_keys($clicks_data)));
            sort($periods);

            $result = [
                'views' => [],
                'clicks' => [],
            ];

            foreach ($periods as $period) {
                $views = $views_data[$period]['views'] ?? 0;
                $clicks = $clicks_data[$period]['clicks'] ?? 0;
                $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
                $day_of_week = date('l', strtotime($period)); // e.g., 'Monday'

                // Views data
                $result['views'][] = [
                    'date' => $period, // e.g., '2025-09-01'
                    'day' => $day_of_week, // e.g., 'Monday'
                    'label' => "$period ($day_of_week)", // e.g., '2025-09-01 (Monday)'
                    'count' => (int)$views,
                    'ctr' => $ctr,
                ];

                // Clicks data
                $result['clicks'][] = [
                    'date' => $period, // e.g., '2025-09-01'
                    'day' => $day_of_week, // e.g., 'Monday'
                    'label' => "$period ($day_of_week)", // e.g., '2025-09-01 (Monday)'
                    'count' => (int)$clicks,
                    'ctr' => $ctr,
                ];
            }

            $data['status'] = self::API_OK;
            $data['data'] = $result;
            $data['params'] = [
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }






    public function actionBestPerformanceTimeSlots()
    {
        $data = [];
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (!$vendor) {
                throw new NotFoundHttpException('Vendor details not found.');
            }

            $post = Yii::$app->request->post();
            $banner_id = $post['banner_id'] ?? null;
            $start_date = !empty($post['start_date']) ? $post['start_date'] : date('Y-m-d', strtotime('-30 days'));
            $end_date = !empty($post['end_date']) ? $post['end_date'] : date('Y-m-d');
            $metric = strtolower($post['metric'] ?? 'views');

            if (!in_array($metric, ['views', 'clicks', 'ctr'])) {
                $metric = 'views';
            }

            if ($banner_id) {
                $banner = Banner::findOne(['id' => $banner_id, 'vendor_details_id' => $vendor->id]);
                if (!$banner) {
                    throw new NotFoundHttpException('Banner not found.');
                }
                $banner_ids = [$banner_id];
            } else {
                $banner_ids = Banner::find()->select('id')->where(['vendor_details_id' => $vendor->id])->column();
            }

            if (empty($banner_ids)) {
                $data['status'] = self::API_OK;
                $data['message'] = 'No banners found.';
                $data['data'] = [];
                return $this->sendJsonResponse($data);
            }

            if (!$this->validateDateFormat($start_date) || !$this->validateDateFormat($end_date)) {
                throw new BadRequestHttpException('Invalid date format. Use YYYY-MM-DD.');
            }

            if (strtotime($start_date) > strtotime($end_date)) {
                throw new BadRequestHttpException('Start date must be before end date.');
            }

            // Fetch views and clicks data by hour
            $hour_expr = "HOUR(performed_at)";

            $views = BannerChargeLogs::find()
                ->select([$hour_expr . ' AS hour', 'COUNT(*) AS views'])
                ->where(['banner_id' => $banner_ids])
                ->andWhere(['action' => 'view'])
                ->andWhere(['between', 'performed_at', $start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                ->groupBy('hour')
                ->indexBy('hour')
                ->asArray()
                ->all();

            $clicks = BannerChargeLogs::find()
                ->select([$hour_expr . ' AS hour', 'COUNT(*) AS clicks'])
                ->where(['banner_id' => $banner_ids])
                ->andWhere(['action' => 'click'])
                ->andWhere(['between', 'performed_at', $start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                ->groupBy('hour')
                ->indexBy('hour')
                ->asArray()
                ->all();

            // Aggregate data by hour
            $hourly_data = [];
            for ($h = 0; $h < 24; $h++) {
                $hourly_data[$h] = [
                    'start_time' => sprintf("%02d:00:00", $h), // e.g., "09:00:00"
                    'end_time' => sprintf("%02d:00:00", ($h + 1) % 24), // e.g., "10:00:00"
                    'views' => 0,
                    'clicks' => 0,
                    'ctr' => 0,
                ];
            }

            // Process views
            foreach ($views as $hour => $view) {
                $hourly_data[$hour]['views'] = (int)$view['views'];
            }

            // Process clicks
            foreach ($clicks as $hour => $click) {
                $hourly_data[$hour]['clicks'] = (int)$click['clicks'];
            }

            // Calculate CTR and format result
            $result = [];
            foreach ($hourly_data as $hour => $data) {
                $ctr = $data['views'] > 0 ? round(($data['clicks'] / $data['views']) * 100, 2) : 0;
                $result[] = [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'views' => (int)$data['views'],
                    'clicks' => (int)$data['clicks'],
                    'ctr' => $ctr,
                ];
            }

            // Sort by the selected metric
            usort($result, function ($a, $b) use ($metric) {
                return $b[$metric] <=> $a[$metric];
            });

            $data['status'] = self::API_OK;
            $data['data'] = $result;
            $data['params'] = [
                'metric' => $metric,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }
}

<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\admin\models\base\Reels;
use app\modules\admin\models\base\ReelTags;
use app\modules\admin\models\VendorDetails;
use app\modules\api\controllers\BKController;
use Exception;
use yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class ReelsController extends BKController
{

    public $enableCsrfValidation = false;
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [

                    'class' => AccessRule::className(),
                ],

                'rules'      => [
                    [
                        'actions' => [

                            'add-reels',
                            'reels-list',
                            'update-reels',
                            'reels-dashboard',


                        ],

                        'allow'   => true,
                        'roles'   => [
                            '@',
                        ],
                    ],
                    [

                        'actions' => [

                            'add-reels',
                            'reels-list',
                            'update-reels',
                            'reels-dashboard',

                        ],

                        'allow'   => true,
                        'roles'   => [

                            '?',
                            '*',

                        ],
                    ],
                ],
            ],

        ]);
    }
    public function actionAddReels()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Validate input data
            if (empty($post['video']) || empty($post['thumbnail']) || empty($post['title']) || empty($post['description'])) {
                throw new \yii\base\InvalidParamException(Yii::t("app", "All fields (video, thumbnail, title, description) are required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $reel_tags                = ! empty($post['reel_tags']) ? $post['reel_tags'] : '';
            $vendor_details_id        = $VendorDetails->id;
            $reels                    = new Reels();
            $reels->vendor_details_id = $vendor_details_id;
            $reels->video             = $post['video'];
            $reels->thumbnail         = $post['thumbnail'];
            $reels->title             = $post['title'];
            $reels->description       = $post['description'];
            $reels->status            = Reels::STATUS_ACTIVE;

            if (! $reels->save()) {
                throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to save reel. Please try again later."));
            }

            if (! empty($reel_tags)) {
                $reel_tags_arr = explode(',', $reel_tags);
                if (! empty($reel_tags_arr)) {
                    foreach ($reel_tags_arr as $reel_tag) {
                        $reel_tags          = new ReelTags();
                        $reel_tags->reel_id = $reels->id;
                        $reel_tags->tag     = $reel_tag;
                        $reel_tags->status  = ReelTags::STATUS_ACTIVE;
                        $reel_tags->save(false);
                    }
                }
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel added successfully.");
            $data['details'] = $reels->asJson($user_id);
        } catch (\yii\base\InvalidParamException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }
    public function actionUpdateReels()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Validate user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate input data
            if (empty($post['video']) || empty($post['thumbnail']) || empty($post['title']) || empty($post['description'])) {
                throw new BadRequestHttpException(Yii::t("app", "All fields (video, thumbnail, title, description) are required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $vendor_details_id = $VendorDetails->id;

            // Validate reel ID
            $id = $post['id'] ?? null;
            if (empty($id)) {
                throw new BadRequestHttpException(Yii::t("app", "Reel ID is required."));
            }

            // Find the reel by ID
            $reels = Reels::findOne(['id' => $id]);
            if (empty($reels)) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found."));
            }

            // Update reel details
            $reels->vendor_details_id = $vendor_details_id;
            $reels->video             = $post['video'];
            $reels->thumbnail         = $post['thumbnail'];
            $reels->title             = $post['title'];
            $reels->description       = $post['description'];
            $reels->status            = Reels::STATUS_ACTIVE;

            if (! $reels->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save reel. Please try again later."));
            }

            // Handle reel tags if provided
            $reel_tags = $post['reel_tags'] ?? '';
            if (! empty($reel_tags)) {
                // Delete existing tags
                ReelTags::deleteAll(['reel_id' => $reels->id]);
                
                $reel_tags_arr = explode(',', $reel_tags);
                if (! empty($reel_tags_arr)) {
                    foreach ($reel_tags_arr as $reel_tag) {
                        $reel_tag_model          = new ReelTags();
                        $reel_tag_model->reel_id = $reels->id;
                        $reel_tag_model->tag     = $reel_tag;
                        $reel_tag_model->status  = ReelTags::STATUS_ACTIVE;
                        if (! $reel_tag_model->save(false)) {
                            throw new ServerErrorHttpException(Yii::t("app", "Failed to save reel tags. Please try again later."));
                        }
                    }
                }
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel updated successfully.");
            $data['details'] = $reels->asJson();
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }
    public function actionReelsList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();
        $page    = ! empty($post['page']) ? (int) $post['page'] : 1;
        $search  = ! empty($post['search']) ? trim($post['search']) : '';
        $status  = ! empty($post['status']) ? trim($post['status']) : '';

        $pageSize = 10; // Set the number of items per page

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            $query = Reels::find()
                ->where(['vendor_details_id' => $vendor_details_id]);
            if (! empty($status)) {
                $query->andWhere(['status' => $status]);
            } else {
                $query->andWhere(['in', 'status', [Reels::STATUS_ACTIVE, Reels::STATUS_INACTIVE]]);
            }
            if (! empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'LOWER(title)', strtolower($search)],
                    ['like', 'LOWER(description)', strtolower($search)],
                ]);
            }

            // Create ActiveDataProvider for reels
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page - 1,
                ],
                'sort'       => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);

            // Fetch and format the reels
            $list = [];
            foreach ($dataProvider->getModels() as $reel) {
                $list[] = $reel->asJson();
            }

            // Check if there are reels to return
            if (! empty($list)) {
                $data['status']     = self::API_OK;
                $data['details']    = $list;
                $data['pagination'] = [
                    'total_count'  => $dataProvider->getTotalCount(),
                    'page_count'   => $dataProvider->getPagination()->getPageCount(),
                    'current_page' => $dataProvider->getPagination()->getPage() + 1,
                    'per_page'     => $dataProvider->getPagination()->pageSize,
                ];
            } else {
                throw new NotFoundHttpException(Yii::t("app", "No reels found for the given criteria."));
            }
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
    public function actionReelsDashboard()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Get vendor details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $vendor_details_id = $VendorDetails->id;

            // Aggregate stats for ACTIVE reels
            $activeStats = (new \yii\db\Query())
                ->from('reels')
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => Reels::STATUS_ACTIVE])
                ->select([
                    'total_views'  => 'SUM(view_count)',
                    'total_likes'  => 'SUM(like_count)',
                    'total_shares' => 'SUM(share_count)',
                    'active_reels' => 'COUNT(*)',
                ])
                ->one();

            // Optional: total reels regardless of status
            $totalReels = (new \yii\db\Query())
                ->from('reels')
                ->where(['vendor_details_id' => $vendor_details_id])
                ->count();

            $data['status']    = self::API_OK;
            $data['dashboard'] = [
                'total_views'  => (int) ($activeStats['total_views'] ?? 0),
                'total_likes'  => (int) ($activeStats['total_likes'] ?? 0),
                'total_shares' => (int) ($activeStats['total_shares'] ?? 0),
                'active_reels' => (int) ($activeStats['active_reels'] ?? 0),
                'total_reels'  => (int) $totalReels,
            ];
            $data['message'] = Yii::t("app", "Reels dashboard stats loaded.");
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
}

<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;

use app\modules\admin\models\Orders;
use app\modules\admin\models\Staff;

use Exception;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class StaffController extends BKController
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
                            'check',
                            'past-orders',
                         







                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'check',
                            'past-orders',
                        















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


    public function actionIndex()
    {

        $data['details'] =  ['hi'];
        return $this->sendJsonResponse($data);
    }

  
    public function actionPastOrders(){

    $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $page = isset($post['page']) ? max(0, ((int)$post['page'] - 1)) : 0;
        $status = $post['status'] ?? '';
        $start_date = $post['start_date'] ?? null;
        $end_date = $post['end_date'] ?? null;
        $staff_id = $post['staff_id'] ?? null;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $home_visitor = Staff::findOne(['id' => $staff_id]);
            if (empty($home_visitor)) {
                throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found. Please log in."));
            }

            $home_visitor_id = $home_visitor->id;

            $query = Orders::find()->alias('o')->innerJoinWith(['homeVisitorsHasOrders ho'])
                ->where(['ho.home_visitor_id' => $home_visitor_id]);

            if (!empty($status)) {
                if ($status == Orders::STATUS_CANCELLED) {
                    $cancelStatuses = [
                        Orders::STATUS_CANCELLED_BY_OWNER,
                        Orders::STATUS_CANCELLED_BY_USER,
                        Orders::STATUS_CANCELLED_BY_ADMIN,
                        Orders::STATUS_CANCELLED_BY_SERVICE_BOY,
                    ];
                    $query->andWhere(['in', 'o.status', $cancelStatuses]);
                } else {
                    $query->andWhere(['o.status' => $status]);
                }
            }

            // Date range filtering
            if (!empty($start_date) && !empty($end_date)) {
                $query->andWhere(['between', 'DATE(o.schedule_date)', $start_date, $end_date]);
            } elseif (!empty($start_date)) {
                $query->andWhere(['DATE(o.schedule_date)' => $start_date]);
            }
            

            // Clone query for aggregation
            $cloneQuery = clone $query;
            $totalOrders = $cloneQuery->count();
            $totalRevenue = $cloneQuery->sum('o.total_w_tax');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]], // ✅ fixed here
                'pagination' => [
                    'pageSize' => 20,
                    'page' => $page,
                ],
            ]);

            $ordersList = [];
            foreach ($dataProvider->models as $order) {
                $ordersList[] = $order->asJson();
            }

            if (!empty($ordersList)) {
                $data['status'] = self::API_OK;
                $data['message'] = Yii::t("app", "Orders retrieved successfully.");
                $data['details'] = $ordersList;
                $data['total_bookings'] = (int)$totalOrders;
                $data['total_revenue'] = (float)$totalRevenue;

                $pagination = $dataProvider->pagination;
                $data['pagination'] = [
                    'total_pages' => $pagination->getPageCount(),
                    'total_items' => $pagination->totalCount,
                    'current_page' => $pagination->getPage() + 1,
                    'page_size' => $pagination->getPageSize(),
                ];
            } else {
                throw new NotFoundHttpException(Yii::t("app", "No orders found for the given criteria."));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Authorization error: {message}", ['message' => $e->getMessage()]);
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "{message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred. Please try again later.");
        }

        return $this->sendJsonResponse($data);

    }









}

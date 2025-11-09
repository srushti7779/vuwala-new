<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\modules\admin\models\Menus;
use app\modules\admin\models\Orders;
use app\modules\admin\models\Staff;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class MenusController extends BKController
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
                            'menu-list'
                         







                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'menu-list'

                        















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




  
  public function actionMenuList()
{
    $data = [];
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    try {
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
        }

        // Fetch only accessible menus for the user
        $menu_all = (new \yii\db\Query())
            ->select('m.*')
            ->from('user_roles ur')
            ->innerJoin('roles r', 'ur.role_id = r.id AND r.status = 1')
            ->innerJoin('role_menu_permissions rmp', 'rmp.role_id = r.id AND rmp.status = 1')
            ->innerJoin('menu_permissions mp', 'mp.id = rmp.menu_permission_id AND mp.status = 1')
            ->innerJoin('menus m', 'm.id = mp.menu_id AND m.status = 1')
            ->where(['ur.user_id' => $user_id, 'ur.status' => 1])
            ->groupBy('m.id')
            ->all();

        if (!empty($menu_all)) {
            $list = [];
            foreach ($menu_all as $menu) {
                $menuModel = new Menus();
                $menuModel->setAttributes($menu, false);
                $list[] = $menuModel->asJson();
            }

            $data['status'] = self::API_OK;
            $data['details'] = $list;
        } else {
            throw new NotFoundHttpException(Yii::t("app", "No menus found for the given user."));
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

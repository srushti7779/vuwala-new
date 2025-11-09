<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\forms\LoginForm;
use app\models\User;
use app\modules\admin\forms\UserForm;
use app\modules\admin\models\Auth;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\base\MainCategory;
use app\modules\admin\models\base\SubCategory;
use Exception;


class CommonController extends BKController
{

    public static function allowedDomains()
    {
        return [
            'http://localhost:5731',
            'http://localhost:5732',
            'http://localhost:5733',
            'http://localhost:5734',
            'http://localhost:5173',
            'https://react.esteticanow.com',
            'https://test.esteticanow.com',
            'https://esteticanow.com'
        ];
    }

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

                            'index',
                            'logout'





                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'index',
                            'logout'












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

        $data['details'] =  ['Hi'];
        return $this->sendJsonResponse($data);
    }






    public function actionLogout()
    {
        $data = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error' => ["User Not Found"]
            ]);
        }

        // Delete session directly with condition
        $deleted = AuthSession::deleteAll(['create_user_id' => $user_id]);

        // No need to call logout() if you're doing stateless API tokens
        $data['status'] = $deleted ? self::API_OK : self::API_NOK;
        $data['details'] = $deleted ? ["Successfully Logged Out"] : ["Session not found or already logged out."];

        return $this->sendJsonResponse($data);
    }
}

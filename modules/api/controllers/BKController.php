<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\web\Response;

abstract class BKController extends Controller
{
    const API_OK = 'OK';
    const API_NOK = 'NOK';

    public static function allowedDomains()
    {
        return [
            'http://localhost:5731',
            'http://localhost:5732',
            'http://localhost:5733',
            'http://localhost:5734',
            'http://localhost:5173',
            'http://localhost:5174',
            'https://react.esteticanow.com',
            'https://test.esteticanow.com',
            'https://www.esteticanow.com',
            'http://localhost:8000',
            'https://esteticanow.com',
            'https://business-partner.esteticanow.com',
            'https://d12tk4hgkb0ybd.cloudfront.net',
        ];
    }

    public function behaviors()
    {
        return [
            // CORS filter (controller/module-level)
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    // exact origins (don't use '*' when using credentials)
                    'Origin' => self::allowedDomains(),

                    // allowed methods for preflight
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

                    // allowed request headers (explicit list is more reliable)
                    'Access-Control-Request-Headers' => [
                        'Content-Type',
                        'Authorization',
                        'X-Requested-With',
                        'auth_code',
                        'X-CSRF-Token'
                    ],

                    // whether to support cookies/credentials
                    'Access-Control-Allow-Credentials' => true,

                    // how long the results of a preflight request can be cached
                    'Access-Control-Max-Age' => 3600,

                    // headers that browser can access from response (if needed)
                    'Access-Control-Expose-Headers' => ['Content-Length', 'Content-Type'],
                ],
            ],

            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className()
                ],
                'rules' => [
                    [
                        // allow OPTIONS for everyone (preflight)
                        'actions' => ['options'],
                        'allow' => true,
                        'roles' => ['?','@'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?','@']
                    ]
                ]
            ],

            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'delete' => ['post','delete']
                ]
            ]
        ];
    }

    // disable CSRF for API controllers (ensure you use token-based auth)
    public $enableCsrfValidation = false;

    private $resp = [
        'status' => self::API_NOK
    ];
    private $user_id;

    /**
     * beforeAction: short-circuit OPTIONS requests as a fallback.
     * Yii's Cors filter should respond to OPTIONS, but sometimes other behaviors/filters
     * interfere — this ensures the preflight gets a correct 200 + headers.
     */
    public function beforeAction($action)
    {
        // Always set URL in response
        $this->resp['url'] = \yii::$app->request->pathInfo;

        // If this is an OPTIONS preflight, send appropriate headers and end
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            $origin = Yii::$app->getRequest()->getHeaders()->get('Origin');

            // If origin not in allowed list, simply return 403 or no CORS headers
            if ($origin && in_array($origin, self::allowedDomains(), true)) {
                $headers = Yii::$app->getResponse()->getHeaders();
                $headers->set('Access-Control-Allow-Origin', $origin);
                $headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, auth_code, X-CSRF-Token');
                $headers->set('Access-Control-Allow-Credentials', 'true');
                $headers->set('Access-Control-Max-Age', '3600');
            }

            // Return 200 OK for preflight
            Yii::$app->getResponse()->setStatusCode(200);
            Yii::$app->end();
            // no further action
        }

        return parent::beforeAction($action);
    }

    public function sendJsonResponse($data = null)
    {
        if ($data != null) {
            $this->resp = ArrayHelper::merge($this->resp, $data);
        }

        return $this->resp;
    }
}

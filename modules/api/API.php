<?php

namespace app\modules\api;

use app\modules\admin\models\AuthSession;
use Yii;
use yii\web\Response;

/**
 * Api module definition class
 */
class Api extends \yii\base\Module
{
	/**
	 * @inheritdoc
	 */
	public $controllerNamespace = 'app\modules\api\controllers';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		// custom initialization code goes here
		Yii::$app->response->format = Response::FORMAT_JSON;
	}
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {

			AuthSession::authenticateSession();

			return true;
		} else
			return false;
	}
}

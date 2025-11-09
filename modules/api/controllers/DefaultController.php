<?php

namespace app\modules\api\controllers;

use Yii;
use Yii\web\Response;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\VController;
use app\modules\admin\models\ClientsList;


class DefaultController extends BKController
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'only' => [
					'index',
				],
				'rules' => [
					[
						'actions' => [
							'index',

						],
						'allow' => true,
					]
				]

			]
		];
	}
	public function actionIndex() {}
}

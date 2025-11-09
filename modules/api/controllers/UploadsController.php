<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\api\controllers\BKController;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\modules\admin\models\Banner;
use app\modules\admin\models\Uploads;
use app\modules\admin\models\VendorDetails;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadsController extends BKController

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
                            'create-catlog'

							
							

						],

						'allow' => true,

						'roles' => [

							'@'

						]

					],

					[

						'actions' => [



							'banner',

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



	public function actionIndex()

	{

		echo 'hi';

		return $this->render('index');
	}
public function actionCreateCatlog()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $data = [];

    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    try {
        if (empty($user_id)) {
            throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
        }

        $model = new VendorDetails();
        $model->user_id = $user_id;

        // Load any other data (optional, from JSON or form-data)
        $model->load(Yii::$app->request->post(), '');

        // Get uploaded file (optional)
        $uploadImage = UploadedFile::getInstance($model, 'catalog_file');

        // Validate only user_id field
        if (!$model->validate(['user_id'])) {
            throw new \Exception('Validation failed for user_id.');
        }

        if ($uploadImage) {
            // Upload the image
            $image = Yii::$app->notification->imageKitUpload($uploadImage);
            if (!empty($image['catalog_file'])) {
                $model->file_url = $image['catalog_file'];
            } else {
                throw new \Exception('Failed to upload file to ImageKit.');
            }
        }

        // Save model without re-validation (already validated `user_id`)
        if ($model->save(false)) {
            $data['status'] = self::API_OK;
            $data['message'] = 'File uploaded successfully.';
            $data['upload_id'] = $model->id;
        } else {
            $data['status'] = self::API_NOK;
            $data['error'] = 'Failed to save upload to the database.';
            $data['errors'] = $model->getErrors();
        }

    } catch (\yii\web\UnauthorizedHttpException $e) {
        Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
    } catch (\Exception $e) {
        Yii::error('Error uploading file: ' . $e->getMessage(), __METHOD__);
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
    }

    return $this->sendJsonResponse($data);
}

}






	




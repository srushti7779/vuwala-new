<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\search\WebSettingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * WebSettingController implements the CRUD actions for WebSetting model.
 */
class WebSettingController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'pdf',
                        'cms',
                        'save-cms',
                        'dispatch',
                        'logo',
                        'dispatch',
                        'favicon' ],
                        'roles' => ['@']
                    ],
                    [
                        'allow' => false
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all WebSetting models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WebSettingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WebSetting model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WebSetting model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WebSetting();

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->setting_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing WebSetting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->setting_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing WebSetting model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->deleteWithRelated();

        return $this->redirect(['index']);
    }
    
    /**
     * 
     * Export WebSetting information into PDF format.
     * @param integer $id
     * @return mixed
     */
    public function actionPdf($id) {
        $model = $this->findModel($id);

        $content = $this->renderAjax('_pdf', [
            'model' => $model,
        ]);

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_CORE,
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => \Yii::$app->name],
            'methods' => [
                'SetHeader' => [\Yii::$app->name],
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }

    
    /**
     * Finds the WebSetting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WebSetting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WebSetting::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    public function actionCms() {
		$model = new WebSetting ();
		$web_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::WEB_SETTING
		])->andWhere([
			'!=','setting_key','website_logo'
		])->andWhere([
			'!=','setting_key','website_favicon'
		])->all();

		$webimage_setting = WebSetting::find()->Where([
			'type_id' => WebSetting::WEB_SETTING,
			'setting_key' => 'website_logo'
		])->one();

		$smtp_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::SMPT_SETTING
		])->all ();
		$url_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::URL_SETTING
		])->all ();
		$email_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::EMAIL_SETTING 
		])->all ();
		$enable_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::ENABLE_SERVICES
		])->all ();
		$amount_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::AMOUNT_SETTING
		])->all ();
		$secret_id = WebSetting::find()->Where([
			'type_id' => WebSetting::SECRET_ID
        ])->all ();
        
        $firebase_setting = WebSetting::find()->Where([
			'type_id' => WebSetting::FIREBASE_SETTING
		])->all ();
		$content_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::CONTENT_SETTING
		])->all ();
		$fax_settings = WebSetting::find()->Where([
			'type_id' => WebSetting::FAX_SETTING
		])->all ();
		$paypal_setting = WebSetting::find()->where([
			'type_id' => WebSetting::PAYPAL_SETTING])->all();
		$notification = WebSetting::find()->where([
		'type_id' => WebSetting::NOTIFICATION])->all();
		$payment_gateway = WebSetting::find()->where([
            'type_id' => WebSetting::PAYMENT_GATEWAY])->all();
      
		return $this->render( 'cms', [ 
				'model' => $model,
				'web_settings' => $web_settings,
				'smtp_settings'=>$smtp_settings,
				'url_settings'=>$url_settings,
				'email_settings'=>$email_settings,
				'enable_settings'=>$enable_settings,
				'amount_settings'=>$amount_settings,
				'secret_id'=>$secret_id,
				'content_settings'=>$content_settings,
				'webimage_setting'=>$webimage_setting,
				'fax_setting'=>$fax_settings,
				'paypal_setting'=>$paypal_setting,
				'notification' => $notification,
                'payment_gateway' => $payment_gateway,
                'firebase_setting' =>$firebase_setting,
               

		] );
	}
	public function actionSaveCms() {
		$data = \Yii::$app->request->get();   
		if (!empty($data['id'] )){
			
			$model = WebSetting::find()->where ( [ 
					'setting_id' => (int)$data['id'] 
			] )->one();
			$model->value = $data['value'];
			$model->save(false);
		}
	}
	public function actionSaveCmsImage() {
		if (empty($_FILES['images'])) {
			print json_encode(['error'=>'No files found for upload.']); 
			return; 
		}
    }
    public function actionFavicon(){
		$getkey = new WebSetting();
        $key = $getkey->getSettingBykey('website_favicon');
       // $id = $key->id;
        //var_dump($id); exit;
        $model = WebSetting::find()->where(['setting_key' => 'website_favicon'])->one();
		//$model = WebSetting::findOne($id);
		if ($model->load(Yii::$app->request->post())){
			$image = UploadedFile::getInstance($model,'value');
			if (!empty($image)){
				$fileName = $image->baseName . '-' . time () . '.' . $image->extension;
				$image->saveAs('uploads/'.$fileName );
				$model->setting_id = $model->setting_id;
                $model->value=$fileName;
                $model->status = '1';
			}
			$model->save();
		}
		return $this->render ('favicon', [ 
			'model' => $model 
		]);
	}
    public function actionLogo(){
		$getkey = new WebSetting();
        $key = $getkey->getSettingBykey('logo');
       // $id = $key->id;
        //var_dump($id); exit;
        $model = WebSetting::find()->where(['setting_key' => 'logo'])->one();
		//$model = WebSetting::findOne($id);
		if ($model->load(Yii::$app->request->post())){
			$image = UploadedFile::getInstance($model,'value');
			if (!empty($image)){
				$fileName = $image->baseName . '-' . time () . '.' . $image->extension;
				$image->saveAs('uploads/'.$fileName );
				$model->setting_id = $model->setting_id;
                $model->value=$fileName;
                $model->status = '1';
			}
			$model->save();
		}
		return $this->render ( 'logo', [ 
			'model' => $model 
		]);
	}

    public function actionDispatch(){

        $others =  WebSetting::find()->Where([
			'type_id' => WebSetting::OTHERS
        ])->all ();
        if(!empty($others)){
          
            return $this->render('dispatch',
            ['others' => $others]);
        }

      
    }
}

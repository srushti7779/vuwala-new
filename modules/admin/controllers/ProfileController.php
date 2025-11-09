<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\modules\admin\models\Orders;
use app\modules\admin\models\VendorDetails;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'store-orders'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

public function actionIndex($id = null)
{
    // If id is passed (vendor profile), use that. Else use logged-in user id.
    $userId = $id ?? Yii::$app->user->id;

    $model = User::findOne($userId);
    if (!$model) {
        throw new NotFoundHttpException('User not found.');
    }

    $vd = new VendorDetails();
    $createdCol = $vd->hasAttribute('created_on') ? 'created_on' : ($vd->hasAttribute('created_at') ? 'created_at' : 'id');

    if ($model->main_vendor) {
        $vendorQuery = VendorDetails::find()
            ->where(['main_vendor_user_id' => $userId])
            ->orderBy([$createdCol => SORT_DESC, 'id' => SORT_DESC]);
    } else {
        $vendorQuery = VendorDetails::find()
            ->where(['user_id' => $userId])
            ->orderBy([$createdCol => SORT_DESC, 'id' => SORT_DESC]);
    }

    $storeDataProvider = new ActiveDataProvider([
        'query' => $vendorQuery,
        'pagination' => ['pageSize' => 10],
        'sort' => false,
    ]);

    $vendorStores = $vendorQuery->all();
    $storeCount = (int) $vendorQuery->count();

    $storeIds = $this->getStoreIdsByUser($userId);
    if (empty($storeIds) || !is_array($storeIds)) {
        $storeIds = [];
    }

    $orderCreatedCol = (new Orders())->hasAttribute('created_on') ? 'created_on' : 'created_at';
    $orderQuery = Orders::find()
        ->where(['vendor_details_id' => $storeIds])
        ->with(['user', 'vendorDetails'])
        ->orderBy([$orderCreatedCol => SORT_DESC, 'id' => SORT_DESC]);

    $orderDataProvider = new ActiveDataProvider([
        'query' => $orderQuery,
        'pagination' => ['pageSize' => 10],
    ]);

    return $this->render('index', [
        'model' => $model,
        'vendorStores' => $vendorStores,
        'vendorQuery' => $vendorQuery,
        'storeCount' => $storeCount,
        'storeDataProvider' => $storeDataProvider,
        'orderDataProvider' => $orderDataProvider,
    ]);
}


    public function actionStoreOrders($id)
    {
        // Find the specific order by its ID
        $orderModel = Orders::findOne($id);

        if (!$orderModel) {
            throw new NotFoundHttpException("Order not found.");
        }

        // Create a data provider for the single order
        $orderDataProvider = new ActiveDataProvider([
            'query' => Orders::find()->where(['id' => $id]),
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['created_on' => SORT_DESC]],
        ]);

        return $this->render('store-orders', [
            'orderDataProvider' => $orderDataProvider,
            'orderModel' => $orderModel,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = VendorDetails::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested vendor details do not exist.');
    }

    private function getStoreIdsByUser($userId)
    {
        // Handle both main vendor and regular vendor cases
        if (Yii::$app->user->identity->main_vendor) {
            return VendorDetails::find()
                ->select('id')
                ->where(['main_vendor_user_id' => $userId])
                ->column();
        } else {
            return VendorDetails::find()
                ->select('id')
                ->where(['user_id' => $userId])
                ->column();
        }
    }
}
<?php
namespace app\modules\admin\controllers;

use app\modules\admin\models\MenuPermissions;
use app\modules\admin\models\search\VendorHasMenusSearch;
use app\modules\admin\models\VendorHasMenuPermissions;
use app\modules\admin\models\VendorHasMenus;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * VendorHasMenusController implements the CRUD actions for VendorHasMenus model.
 */
class VendorHasMenusController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index', 'view', 'create', 'get-vendor-menus', 'get-permissions', 'update', 'delete'],
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all VendorHasMenus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new VendorHasMenusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single VendorHasMenus model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = VendorHasMenus::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new VendorHasMenus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new VendorHasMenus();

        if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isPost) {
            $menuIds     = Yii::$app->request->post('menu_ids', []);
            $permissions = Yii::$app->request->post('permissions', []);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Delete existing menu assignments for this vendor
                VendorHasMenus::deleteAll(['vendor_id' => $model->vendor_id]);

                // Save new menu assignments and store their IDs
                $menuRelations = [];
                foreach ($menuIds as $menuId) {
                    $relation                 = new VendorHasMenus();
                    $relation->vendor_id      = $model->vendor_id;
                    $relation->menu_id        = $menuId;
                    $relation->status         = $model->status ?? 1;
                    $relation->created_on     = date('Y-m-d H:i:s');
                    $relation->updated_on     = date('Y-m-d H:i:s');
                    $relation->create_user_id = Yii::$app->user->id;
                    $relation->update_user_id = Yii::$app->user->id;
                    $relation->save(false);
                    $menuRelations[$menuId] = $relation->id; // Store the generated ID
                }

                // Set $model->id to the first menu relation ID if records exist
                $model->id = ! empty($menuRelations) ? reset($menuRelations) : null;
                if ($model->id === null && ! empty($menuIds)) {
                    $model->save(false);
                    $model->id = $menuRelations[array_key_first($menuRelations)];
                }

                if (! empty($menuRelations)) {
                    $existingPermissions = VendorHasMenuPermissions::find()
                        ->where(['vendor_has_menu_id' => array_values($menuRelations)])
                        ->count();

                    if ($existingPermissions > 0) {
                        throw new \Exception('Permissions already exist for this vendor’s menus. Cannot overwrite.');
                    }
                }

                // Save new permissions
                foreach ($permissions as $menuId => $permissionIds) {
                    if (isset($menuRelations[$menuId])) {
                        foreach ($permissionIds as $permissionId) {
                            $permissionRelation                      = new VendorHasMenuPermissions();
                            $permissionRelation->vendor_has_menu_id  = $menuRelations[$menuId]; // Correct foreign key
                            $permissionRelation->menu_permissions_id = $permissionId;
                            $permissionRelation->status              = 1;
                            $permissionRelation->created_on          = date('Y-m-d H:i:s');
                            $permissionRelation->updated_on          = date('Y-m-d H:i:s');
                            $permissionRelation->create_user_id      = Yii::$app->user->id;
                            $permissionRelation->update_user_id      = Yii::$app->user->id;
                            $permissionRelation->save(false);
                        }
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Vendor menus and permissions Created successfully.');
                // Debug the redirect ID
                Yii::info('Redirecting to view with ID: ' . $model->id, 'debug');
                if ($model->id) {
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    return $this->redirect(['index']); // Fallback if no ID
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::info('Error saving data: ' . $e->getMessage(), 'debug');
                Yii::$app->session->setFlash('error', 'Error saving data: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionGetPermissions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $menuId                     = Yii::$app->request->get('menu_id');
        $permissions                = MenuPermissions::find()
            ->where(['menu_id' => $menuId, 'status' => 1])
            ->select(['id', 'small_description', 'permission_name'])
            ->asArray()
            ->all();

        return $permissions;
    }

    public function actionGetVendorMenus($vendorId)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    // 1. Get all menus assigned to this vendor
    $vendorMenus = VendorHasMenus::find()
        ->select('id, menu_id')
        ->where(['vendor_id' => $vendorId])
        ->asArray()
        ->all();

    $menuIds = array_column($vendorMenus, 'menu_id');
    $menuMap = \yii\helpers\ArrayHelper::map($vendorMenus, 'menu_id', 'id'); // menu_id => vendor_has_menu_id

    // 2. Get permissions already assigned for these menus
    $permissions = [];
    if (!empty($menuMap)) {
        $vendorPermissions = VendorHasMenuPermissions::find()
            ->where(['vendor_has_menu_id' => array_values($menuMap)])
            ->asArray()
            ->all();

        foreach ($vendorPermissions as $vp) {
            $menuId = array_search($vp['vendor_has_menu_id'], $menuMap);
            if ($menuId) {
                $permissions[$menuId][] = $vp['menu_permissions_id'];
            }
        }
    }

    return [
        'menus' => $menuIds,       // Already checked menus
        'permissions' => $permissions, // Already checked permissions grouped by menu
    ];
}


    /**
     * Updates an existing VendorHasMenus model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
public function actionUpdate($id)
{
    $model = $this->findModel($id);

    if (Yii::$app->request->isPost) {
        $menuIds = Yii::$app->request->post('menu_ids', []);
        $permissions = Yii::$app->request->post('permissions', []);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                // 🔹 Delete existing menus & permissions for this vendor
                $vendorMenus = \app\modules\admin\models\VendorHasMenus::find()
                    ->where(['vendor_id' => $model->id])
                    ->all();

                foreach ($vendorMenus as $vm) {
                    \app\modules\admin\models\VendorHasMenuPermissions::deleteAll(['vendor_has_menu_id' => $vm->id]);
                    $vm->delete();
                }

                // 🔹 Insert new menus & permissions
                foreach ($menuIds as $menuId) {
                    $vendorMenu = new \app\modules\admin\models\VendorHasMenus();
                    $vendorMenu->vendor_id = $model->id;
                    $vendorMenu->menu_id = $menuId;
                    $vendorMenu->save(false);

                    if (!empty($permissions[$menuId])) {
                        foreach ($permissions[$menuId] as $permId) {
                            $permModel = new \app\modules\admin\models\VendorHasMenuPermissions();
                            $permModel->vendor_has_menu_id = $vendorMenu->id;
                            $permModel->menu_permissions_id = $permId;
                            $permModel->save(false);
                        }
                    }
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Vendor updated successfully');
            return $this->redirect(['view', 'id' => $model->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // 🔹 Preload existing menus & permissions to pre-check checkboxes in form
    $existingMenus = \yii\helpers\ArrayHelper::getColumn(
        \app\modules\admin\models\VendorHasMenus::find()->where(['vendor_id' => $model->id])->all(),
        'menu_id'
    );

    $existingPermissions = [];
    $vendorMenus = \app\modules\admin\models\VendorHasMenus::find()
        ->where(['vendor_id' => $model->id])
        ->with('menuPermissions')
        ->all();

    foreach ($vendorMenus as $vm) {
        $existingPermissions[$vm->menu_id] = \yii\helpers\ArrayHelper::getColumn($vm->menuPermissions, 'menu_permissions_id');
    }

    return $this->render('update', [
        'model' => $model,
        'existingMenus' => $existingMenus,
        'existingPermissions' => $existingPermissions,
    ]);
}


    /**
     * Deletes an existing VendorHasMenus model.
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
     * Finds the VendorHasMenus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VendorHasMenus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorHasMenus::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\Menus;
use app\modules\admin\models\MenuPermissions;
use app\modules\admin\models\search\MenusSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MenusController implements the CRUD actions for Menus model.
 */
class MenusController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'add-menu-permissions', 'scan-all'],
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
     * Lists all Menus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Menus model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerMenuPermissions = new \yii\data\ArrayDataProvider([
            'allModels' => $model->menuPermissions,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerMenuPermissions' => $providerMenuPermissions,
        ]);
    }

    /**
     * Creates a new Menus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Menus();

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Menus model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Menus model.
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
     * Finds the Menus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menus::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for MenuPermissions
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddMenuPermissions()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('MenuPermissions');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formMenuPermissions', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Scans all controllers and actions in the admin module and creates menu entries
     * @return mixed
     */
    public function actionScanAll()
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $controllersPath = Yii::getAlias('@app/modules/admin/controllers');
            $scannedData = [];
            $createdMenus = 0;
            $createdPermissions = 0;
            
            // Get all controller files
            $controllerFiles = glob($controllersPath . '/*Controller.php');
      
            foreach ($controllerFiles as $file) {
                $fileName = basename($file, '.php');
                $controllerName = str_replace('Controller', '', $fileName);
                
                // Skip base Controller class
                if ($controllerName === 'Controller') {
                    continue;
                }
                
                // Create controller class name
                $className = "app\\modules\\admin\\controllers\\{$fileName}";

             
                
                // Check if file exists
                if (!file_exists($file)) {
                    continue;
                }
                
                // Check if class exists (using autoloader)
                if (!class_exists($className)) {
                    continue;
                }
                
                // Get controller methods (actions)
                try {
                    $reflection = new \ReflectionClass($className);
                    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                } catch (\Exception $e) {
                    continue;
                }
                
                $actions = [];
                foreach ($methods as $method) {
                    $methodName = $method->getName();
                    if (strpos($methodName, 'action') === 0 && $methodName !== 'actions') {
                        $actionName = lcfirst(substr($methodName, 6)); // Remove 'action' prefix
                        if (!empty($actionName)) {
                            $actions[] = $actionName;
                        }
                    }
                }


                if (!empty($actions)) {
                    // Create or update controller menu
                    $controllerRoute = "/{$this->camelCaseToKebab($controllerName)}";
                    $controllerMenu = $this->createOrUpdateMenu($controllerName, $controllerRoute, 'fa fa-list');
                    
                    if ($controllerMenu) {
                        $createdMenus++;
                        
                        // Create permissions for each action
                        foreach ($actions as $action) {
                            $actionRoute = "{$controllerRoute}/{$this->camelCaseToKebab($action)}";
                            $permissionName = strtolower("{$controllerName}_{$action}");
                            $description = "Access to {$controllerName} controller {$action} action";
                            
                            if ($this->createOrUpdateMenuPermission($controllerMenu->id, $permissionName, $description)) {
                                $createdPermissions++;
                            }
                        }
                    }
                    
                    $scannedData[$controllerName] = $actions;
                }
            }
            
            $transaction->commit();
            
            Yii::$app->session->setFlash('success', 
                "Scan completed successfully! Created/Updated {$createdMenus} menus and {$createdPermissions} permissions."
            );
            
        } catch (\Exception $e) {
            $transaction->rollback();
            Yii::$app->session->setFlash('error', 
                "Error occurred during scan: " . $e->getMessage()
            );
        }
        
        return $this->redirect(['index']);
    }
    
    /**
     * Creates or updates a menu entry
     * @param string $label
     * @param string $route
     * @param string $icon
     * @return Menus|null
     */
    private function createOrUpdateMenu($label, $route, $icon = 'fa fa-list')
    {
        // Check if menu already exists
        $menu = Menus::findOne(['route' => $route]);
        
        if (!$menu) {
            $menu = new Menus();
            $menu->label = $label;
            $menu->route = $route;
            $menu->icon = $icon;
            $menu->parent_id = null;
            $menu->sort_order = 0;
            $menu->status = 1;
            $menu->create_user_id = Yii::$app->user->id;
            $menu->created_on = date('Y-m-d H:i:s');
        }
        
        $menu->update_user_id = Yii::$app->user->id;
        $menu->updated_on = date('Y-m-d H:i:s');
        
        if ($menu->save(false)) {
            return $menu;
        }
        
        return null;
    }
    
    /**
     * Creates or updates a menu permission entry
     * @param int $menuId
     * @param string $permissionName
     * @param string $description
     * @return bool
     */
    private function createOrUpdateMenuPermission($menuId, $permissionName, $description)
    {
        // Check if permission already exists
        $permission = MenuPermissions::findOne([
            'menu_id' => $menuId,
            'permission_name' => $permissionName
        ]);
        
        if (!$permission) {
            $permission = new MenuPermissions();
            $permission->menu_id = $menuId;
            $permission->permission_name = $permissionName;
            $permission->small_description = $description;
            $permission->status = 1;
            $permission->create_user_id = Yii::$app->user->id;
            $permission->created_on = date('Y-m-d H:i:s');
        }
        
        $permission->update_user_id = Yii::$app->user->id;
        $permission->updated_on = date('Y-m-d H:i:s');

        return $permission->save(false);
    }
    
    /**
     * Converts CamelCase to kebab-case
     * @param string $input
     * @return string
     */
    private function camelCaseToKebab($input)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $input));
    }
}

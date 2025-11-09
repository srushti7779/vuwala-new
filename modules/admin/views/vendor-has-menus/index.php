<?php

use app\modules\admin\models\VendorHasMenus;
use kartik\export\ExportMenu;
    use kartik\grid\GridView;
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $searchModel app\modules\admin\models\search\VendorHasMenusSearch */
    /* @var $dataProvider yii\data\ActiveDataProvider */

    $this->title                   = Yii::t('app', 'Vendor Has Menus');
    $this->params['breadcrumbs'][] = $this->title;

    // toggle search
    $searchJs = <<<JS
$('.search-button').click(function(){
    $('.search-form').toggle(1000);
    return false;
});
JS;
    $this->registerJs($searchJs);
?>

<div class="vendor-has-menus-index">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <p>
        <?php echo Html::a(Yii::t('app', 'Create Vendor Has Menus'), ['create'], ['class' => 'btn btn-success']) ?>
<?php echo Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>
    </p>

    <div class="search-form" style="display:none">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <?php
        $gridColumns = [
            ['class' => 'yii\grid\SerialColumn'],

            // Vendor business + store
            [
                'attribute'           => 'vendor_id',
                'label'               => Yii::t('app', 'Vendor'),
                'value'               => function ($model) {
                    return $model->vendor
                    ? $model->vendor->business_name .
                    (! empty($model->vendor->store_name) ? " ({$model->vendor->store_name})" : '')
                    : null;
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => \yii\helpers\ArrayHelper::map(
                    \app\modules\admin\models\VendorDetails::find()->asArray()->all(),
                    'id',
                    'business_name'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => [
                    'placeholder' => 'Select Vendor',
                    'id'          => 'grid-vendor-has-menus-search-vendor_id',
                ],
            ],
            [
                'attribute'           => 'vendor_id',
                'label'               => Yii::t('app', 'Business Name'),
                'value'               => function ($model) {
                    return $model->vendor ? strip_tags($model->vendor->description) : null;
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => \yii\helpers\ArrayHelper::map(
                    \app\modules\admin\models\VendorDetails::find()->asArray()->all(),
                    'id',
                    'description'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => [
                    'placeholder' => 'Select Vendor Description',
                    'id'          => 'grid-vendor-has-menus-search-vendor_id',
                ],
            ],

            [
                'attribute'           => 'location_name',
                'label'               => Yii::t('app', 'Location Name'),
                'value'               => function ($model) {
                    return $model->vendor ? strip_tags($model->vendor->location_name) : null;
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => \yii\helpers\ArrayHelper::map(
                    \app\modules\admin\models\VendorDetails::find()->asArray()->all(),
                    'id',
                    'location_name'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => [
                    'placeholder' => 'Select Location',
                    'id'          => 'grid-vendor-has-menus-search-location_name',
                ],
            ],
       [
            'attribute' => 'vendor_has_menu_id',
            'label'     => Yii::t('app', 'Vendor Menu ID'),
            'value'     => function ($model) {
                return $model->id;   
            },
        ],

        [
            'attribute' => 'menu_permissions_id',
            'label'     => Yii::t('app', 'Menu Permission IDs'),
            'format'    => 'raw',
            'value'     => function ($model) {
                // Collect all related permission IDs
                if (!empty($model->menuPermissions)) {
                    return implode(', ', \yii\helpers\ArrayHelper::getColumn($model->menuPermissions, 'menu_permissions_id'));
                }
                return '(not set)';
            },
        ],

    

        


            // [
            //     'label'  => 'Menus & Permissions',
            //     'format' => 'raw',
            //     'value'  => function ($model) {
            //         if (empty($model->vendorHasMenus)) {
            //             return 'No menus';
            //         }

            //         $output = '<ul>';
            //         foreach ($model->vendorHasMenus as $vendorMenu) {
            //             // Menu name
            //             $menuName = $vendorMenu->menu ? $vendorMenu->menu->name : 'Menu ID: ' . $vendorMenu->menu_id;

            //             // Permissions
            //             $permissions = [];
            //             if (! empty($vendorMenu->vendorHasMenuPermissions)) {
            //                 foreach ($vendorMenu->vendorHasMenuPermissions as $perm) {
            //                     $permissions[] = $perm->menuPermission ? $perm->menuPermission->name : $perm->menu_permissions_id;
            //                 }
            //             }

            //             $output .= '<li><strong>' . \yii\helpers\Html::encode($menuName) . '</strong>';
            //             $output .= ' → ' . (! empty($permissions) ? implode(', ', $permissions) : 'No Permissions');
            //             $output .= '</li>';
            //         }
            //         $output .= '</ul>';

            //         return $output;
            //     }
            // ],

            'status',

            ['class' => 'yii\grid\ActionColumn'],
        ];
    ?>

    <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns'      => $gridColumns,
            'pjax'         => true,
            'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-vendor-has-menus']],
            'panel'        => [
                'type'    => GridView::TYPE_PRIMARY,
                'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode($this->title),
            ],
            'export'       => false,
            'toolbar'      => [
                '{export}',
                ExportMenu::widget([
                    'dataProvider'    => $dataProvider,
                    'columns'         => $gridColumns,
                    'target'          => ExportMenu::TARGET_BLANK,
                    'fontAwesome'     => true,
                    'dropdownOptions' => [
                        'label'       => 'Full',
                        'class'       => 'btn btn-default',
                        'itemsBefore' => [
                            '<li class="dropdown-header">Export All Data</li>',
                        ],
                    ],
                    'exportConfig'    => [
                        ExportMenu::FORMAT_PDF => false,
                    ],
                ]),
            ],
    ]); ?>

</div>

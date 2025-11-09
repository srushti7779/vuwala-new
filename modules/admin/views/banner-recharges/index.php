<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\BannerRechargesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\modules\admin\models\User;
use app\modules\admin\models\BannerRecharges;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\export\ExportMenu;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Banner Recharges');
$this->params['breadcrumbs'][] = $this->title;

$search = "$('.search-button').click(function(){
    $('.search-form').toggle(1000);
    return false;
});";
 ?>

<div class="banner-recharges-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Banner Recharges'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>
    </p>
    <div class="search-form" style="display:none">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <?php 
  $gridColumn = [
    ['class' => 'yii\grid\SerialColumn'],
    ['attribute' => 'id', 'visible' => false],

    [
        'attribute' => 'vendor_id',
        'label' => Yii::t('app', 'Vendor ID'),
        'value' => function ($model) {
            return $model->vendor_id;
        },
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => \yii\helpers\ArrayHelper::map(
            BannerRecharges::find()->select(['vendor_id'])->distinct()->asArray()->all(),
            'vendor_id',
            'vendor_id'
        ),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'filterInputOptions' => [
            'placeholder' => 'Select Vendor',
            'id' => 'grid-search-vendor-id'
        ],
    ],

    [
        'attribute' => 'banner_id',
        'label' => Yii::t('app', 'Banner ID'),
        'value' => function ($model) {
            return $model->banner_id;
        },
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => \yii\helpers\ArrayHelper::map(
            BannerRecharges::find()->select(['banner_id'])->distinct()->asArray()->all(),
            'banner_id',
            'banner_id'
        ),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'filterInputOptions' => [
            'placeholder' => 'Select Banner',
            'id' => 'grid-search-banner-id'
        ],
    ],

    'amount',
    'status',

    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update} {delete}',
        'buttons' => [
            'view' => function ($url, $model) {
                if (in_array(\Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_MANAGER])) {
                    return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                }
            },
            'update' => function ($url, $model) {
                if (in_array(\Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_MANAGER])) {
                    return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                }
            },
            'delete' => function ($url, $model) {
                if (in_array(\Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN])) {
                    return Html::a('<span class="fas fa-trash-alt" aria-hidden="true"></span>', $url, [
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'Are you sure?',
                        ],
                    ]);
                }
            },
        ],
    ],
];

    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumn,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-banner-recharges']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
        ],
        'export' => false,
        'toolbar' => [
            '{export}',
            ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumn,
                'target' => ExportMenu::TARGET_BLANK,
                'fontAwesome' => true,
                'dropdownOptions' => [
                    'label' => 'Full',
                    'class' => 'btn btn-default',
                    'itemsBefore' => [
                        '<li class="dropdown-header">Export All Data</li>',
                    ],
                ],
                'exportConfig' => [
                    ExportMenu::FORMAT_PDF => false
                ]
            ]),
        ],
    ]); ?>

</div>

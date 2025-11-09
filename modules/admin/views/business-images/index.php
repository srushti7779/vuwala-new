<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\BusinessImagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Business Images');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);

?>
<div class="business-images-index">
    <div class="card">
        <div class="card-body">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>

            <p>
                <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN) { ?>
                    <?= Html::a(Yii::t('app', 'Create Business Images'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php } ?>
                <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>
            </p>
            <div class="search-form" style="display:none">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <?php
            $gridColumn = [
                ['class' => 'yii\grid\SerialColumn'],

                ['attribute' => 'id', 'visible' => false],

                // [
                //     'attribute' => 'vendor_details_id',
                //     'label' => Yii::t('app', 'Vendor Details'), 
                //     'value' => function ($model) {
                //         return $model->vendorDetails->id;
                //     },
                //     'filterType' => GridView::FILTER_SELECT2,
                //     'filter' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->asArray()->all(), 'id', 'id'),
                //     'filterWidgetOptions' => [
                //         'pluginOptions' => ['allowClear' => true],
                //     ],
                //     'filterInputOptions' => ['placeholder' => 'Vendor details', 'id' => 'grid-business-images-search-vendor_details_id']
                // ],

                [
                    'attribute' => 'vendor_details_id',
                    'label' => Yii::t('app', 'Vendor Username'),
                    'value' => function ($model) {
                        return $model->vendorDetails && $model->vendorDetails->user
                            ? $model->vendorDetails->user->username
                            : null; // Handles nulls
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\modules\admin\models\User::find()
                            ->where(['user_role' => User::ROLE_VENDOR]) // Use array syntax for conditions
                            ->asArray()
                            ->all(),
                        'id',
                        'username' // Fetch usernames for the filter
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => [
                        'placeholder' => 'Vendor username',
                        'id' => 'grid-staff-search-vendor_details_id',
                    ],
                ],

 
                [
                    'attribute' => 'vendor_details_id',
                    'label' => Yii::t('app', 'Buisness Name'),
                    'value' => function ($model) {
                        return $model->vendorDetails->business_name;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->asArray()->all(), 'id', 'business_name'),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Vendor details', 'id' => 'grid-business-images-search-vendor_details_id']
                ],

                [
                    'attribute' => 'image_file',
                    'format' => 'html',
                    'value' => function ($model) {
                        return Html::img($model->image_file, ['style' => 'width:100px; height:100px;']);
                    },
                ],

                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())){

                                return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                            }
                        },
                        'update' => function ($url, $model) {
                            if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())){
                                return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                            }
                        },
                        'delete' => function ($url, $model) {
                            if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())){
                                return Html::a('<span class="fas fa-trash-alt" aria-hidden="true"></span>', $url, [
                                    'data' => [
                                        'method' => 'post',
                                        // use it if you want to confirm the action
                                        'confirm' => 'Are you sure?',
                                    ],
                                ]);
                            }
                        },
                    ]
                ],
            ];
            ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumn,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-business-images']],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
                ],
                'export' => false,
                // your toolbar can include the additional full export menu
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
    </div>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    $(document).on('change', 'select[id^=status_list_]', function() {
        var id = $(this).attr('data-id');
        var val = $(this).val();

        $.ajax({
            type: "POST",
            url: "/esthetica_backend/gii/default/status-change",
            data: {
                id: id,
                val: val
            },
            success: function(data) {
                swal("Good job!", "Status Successfully Changed!", "success");
            }
        });
    });
</script>
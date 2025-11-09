<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\SubCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use app\modules\admin\models\base\SubCategory;
use app\modules\admin\models\base\VendorDetails;

use kartik\grid\GridView;

$this->title = Yii::t('app', 'Sub Categories');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);


?>
<div class="sub-category-index">
    <div class="card">
        <div class="card-body">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>

            <p>
                <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
                   <?= Html::a(Yii::t('app', 'Create Sub Category'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php } ?>
                <!-- <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>    -->
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

                [
                    'attribute' => 'main_category_id',
                    'label' => Yii::t('app', 'Main Category'),
                    'value' => function ($model) {
                        return $model->mainCategory->title;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->asArray()->all(), 'id', 'title'),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Main category', 'id' => 'grid-sub-category-search-main_category_id']
                ], 

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
                    'label' => Yii::t('app', 'Business Name'),
                    'value' => function ($model) {
                        return $model->vendorDetails ? $model->vendorDetails->business_name : 'N/A';
                    },

                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(VendorDetails::find()->asArray()->all(), 'id', 'business_name'),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'User', 'id' => 'grid-staff-search-user_id']
                ],

                [
                    'attribute' => 'title',
                    'label' => Yii::t('app', 'Title'),
                    'filter' => \yii\helpers\ArrayHelper::map(SubCategory::find()->select('title')->distinct()->asArray()->all(), 'title', 'title'),
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Title', 'id' => 'grid-title-search']
                ], 

                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        // Construct the URL by replacing the old path with the new path
                        // $baseUrl = \yii\helpers\Url::base(true);
                        $imagePath = $model->image;
                        $imageUrl = $imagePath;
                        return Html::img($imageUrl, ['alt' => 'Image', 'style' => 'width: 100px;height:100px;']); // Adjust 'max-width' as needed
                    },
                ],


                [
                    'attribute' => 'is_featured',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getFeatureOptionsBadges();
                    },


                ],

                // [
                //     'attribute' => 'is_premium',
                //     'format' => 'raw',
                //     'value' => function ($model) {
                //         return $model->getOptionsBadgesPremium();
                //     },


                // ],

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
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                                return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                            }
                        },
                        'update' => function ($url, $model) {
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                                return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                            }
                        },
                        'delete' => function ($url, $model) {
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) {
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
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-sub-category']],
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
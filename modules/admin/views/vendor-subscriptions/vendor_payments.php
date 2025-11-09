<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\VendorSubscriptionsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use app\modules\admin\models\base\VendorDetails;

use kartik\grid\GridView;

$this->title = Yii::t('app', 'Vendor Subscriptions');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);


?>
<div class="vendor-subscriptions-index">
    <div class="card">
        <div class="card-body">

          

            <p>
                <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN ) { ?>
                    <!-- <?= Html::a(Yii::t('app', 'Create Vendor Subscriptions'), ['create'], ['class' => 'btn btn-success']) ?> -->
                <?php  } ?>
                <!-- <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?> -->
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
                    'attribute' => 'business_name',
                    'label' => Yii::t('app', 'Business Name'),
                    'value' => function ($model) {
                        return $model->vendorDetails->business_name ?? null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\modules\admin\models\VendorDetails::find()->select(['business_name'])->distinct()->asArray()->all(),
                        'business_name',
                        'business_name'
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Business Nmae'],
                ],




                [
                    'attribute' => 'main_category_id',
                    'label' => Yii::t('app', 'Main Category'),
                    'value' => function ($model) {
                        return $model->vendorDetails->mainCategory->title ?? null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\modules\admin\models\VendorDetails::find()->select(['main_category_id'])->distinct()->asArray()->all(),
                        'main_category_id',
                        'main_category_id'
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Main Category'],
                ],



                [
                    'attribute' => 'vendor_details_id',
                    'label' => Yii::t('app', 'Vendor email'),
                    'value' => function ($model) {
                        return $model->vendorDetails && $model->vendorDetails->user
                            ? $model->vendorDetails->user->email
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
                        'placeholder' => 'Vendor email',
                        'id' => 'grid-staff-search-vendor_details_id',
                    ],
                ],


                [
                    'attribute' => 'contact_no',
                    'label' => Yii::t('app', 'Contact No'),
                    'value' => function ($model) {
                        return $model->vendorDetails->user->contact_no ?? null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\models\User::find()->select(['contact_no'])->distinct()->asArray()->all(),
                        'contact_no',
                        'contact_no'
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Contact No'],
                ],
                

                [
                    'attribute' => 'is_verified',
                    'label' => Yii::t('app', 'Is Verified'),
                    'value' => function ($model) {
                        return $model->vendorDetails->is_verified == 1 ? 'Yes' : 'No';
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                  'filter' => [
                        1 => 'Yes',
                        0 => 'No',
                    ],

                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => [
                        'placeholder' => 'Verified?',
                        'id' => 'grid-vendor-details-search-is_verified',
                    ],
                ],
                
           
                [
                    'attribute' => 'address',
                    'label' => Yii::t('app', 'Address'),
                    'value' => function ($model) {
                        return $model->vendorDetails->address ?? null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\modules\admin\models\VendorDetails::find()->select(['address'])->distinct()->asArray()->all(),
                        'address',
                        'address'
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Address'],
                ],
                



       


                [
                    'attribute' => 'gst_number',
                    'label' => Yii::t('app', 'GST Number'),
                    'value' => function ($model) {
                        return $model->vendorDetails->gst_number ?? null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\modules\admin\models\VendorDetails::find()->select(['gst_number'])->distinct()->asArray()->all(),
                        'gst_number',
                        'gst_number'
                    ),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'GST Number'],
                ],
                


                [
                    'attribute' => 'subscription_id',
                    'label' => Yii::t('app', 'Subscription'),
                    'value' => function ($model) {
                        return $model->subscription->title;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Subscriptions::find()->asArray()->all(), 'id', 'title'),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Subscriptions', 'id' => 'grid-vendor-subscriptions-search-subscription_id']
                ],


                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', 'Regrestred on'),
                    'value' => function ($model) {
                        return $model->subscription->vendorSubscriptions->vendorDetails->user->created_at??'';
                    }
                ],

                [
                    'attribute' => 'duration',
                    'label' => Yii::t('app', 'Duration (in days)'),
                    'value' => function ($model) {
                        return $model->duration;
                    },

                ], 


            
                [
                    'attribute' => 'amount',
                    'label' => Yii::t('app', 'Amount'),  
                    'value' => function ($model) {
                        return $model->amount;
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
                    'template' => '{view} {update}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER || \Yii::$app->user->identity->user_role == User::ROLE_ACCOUNT_MANAGER) {
                                return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                            }
                        },
                        'update' => function ($url, $model) {
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN  || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER || \Yii::$app->user->identity->user_role == User::ROLE_ACCOUNT_MANAGER) {
                                return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                            }
                        },
                        'delete' => function ($url, $model) {
                            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN) {
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
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-vendor-subscriptions']],
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
<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use app\modules\admin\models\base\VendorDetails;


use kartik\grid\GridView;

$this->title = Yii::t('app', 'Staff');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);


?>
<div class="staff-index">
    <div class="card">
        <div class="card-body">

            <?php // echo $this->render('_search', ['model' => $searchModel]); 
            ?>

          <p>
    <?php
    $userRole = \Yii::$app->user->identity->user_role;

    // Show "Create Staff" for Admin/Subadmin
    if ($userRole == User::ROLE_ADMIN || $userRole == User::ROLE_SUBADMIN) {
        echo Html::a(Yii::t('app', 'Create Staff'), ['create'], ['class' => 'btn btn-success']);
    }

    // Show "Create Staff" for Vendor (uses different action)
    if ($userRole == User::ROLE_VENDOR) {
        echo Html::a(Yii::t('app', 'Create Staff'), ['create-store'], ['class' => 'btn btn-success']);
    }

    // Common button for all roles
    echo Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']);
    ?>
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
                    'attribute' => 'user_id',
                    'label' => Yii::t('app', 'User'),
                    'value' => function ($model) {
                        return $model->user->username;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => \yii\helpers\ArrayHelper::map(
                        \app\models\User::find()
                            ->where(['in', 'user_role', [User::ROLE_STAFF, User::ROLE_HOME_VISITOR]]) // Use model constants for roles
                            ->asArray()
                            ->all(),
                        'id',
                        'username'
                    ),
                    'filterWidgetOptions' => [ 
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'User', 'id' => 'grid-staff-search-user_id']
                ],

                // [
                //     'attribute' => 'vendor_details_id', 
                //     'label' => Yii::t('app', 'Business Name'),
                //     'value' => function ($model) {
                //         return isset($model->vendorDetails) ? $model->vendorDetails->business_name : 'N/A';
                //     },
                //     'filter' => Html::activeTextInput($searchModel, 'vendor_details_id', [
                //         'class' => 'form-control',
                //         'placeholder' => 'Enter Business Name...'
                //     ]),
                // ],
                
                
                


                // [
                //     'attribute' => 'vendor_details_id',
                //     'label' => Yii::t('app', 'Vendor Details'),
                //     'value' => function($model){                   
                //         return $model->vendorDetails && $model->vendorDetails->user ? $model->vendorDetails->user->username : ''; // Assuming 'user' relation is set correctly
                //     },
                //     'filterType' => GridView::FILTER_SELECT2,
                //     'filter' => \yii\helpers\ArrayHelper::map(
                //         \app\modules\admin\models\VendorDetails::find()
                //             ->joinWith('user') // Join the User model
                //             ->where(['user.user_role' => 'vendor']) // Add condition for user role 'vendor'
                //             ->asArray()
                //             ->all(), 'id', 'user.username'
                //     ),
                //     'filterWidgetOptions' => [
                //         'pluginOptions' => ['allowClear' => true],
                //     ],
                //     'filterInputOptions' => ['placeholder' => 'Vendor details', 'id' => 'grid-services-search-vendor_details_id']
                // ],

 
              [
                'attribute' => 'vendor_details_id',
                'label' => Yii::t('app', 'Business Name'),
                'value' => function ($model) {
                    return $model->vendorDetails ? $model->vendorDetails->business_name : 'N/A';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => \yii\helpers\ArrayHelper::map(
                    VendorDetails::find()->asArray()->all(), 'id', 'business_name'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Business Name', 'id' => 'grid-staff-search-vendor_details_id']
            ],

                [
                    'attribute' => 'profile_image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $imageUrl = $model->profile_image; // The absolute URL stored in the database

                        if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                            // If the image URL is valid, display the image
                            return \yii\helpers\Html::img($imageUrl, [
                                'alt' => 'Image',
                                'style' => 'width: 100px; height: 100px;',
                            ]);
                        } else {
                            // Return "N/A" if the URL is empty or invalid
                            return 'N/A';
                        }
                    },
                ],




                'mobile_no',

                'full_name',

                // 'email:email',

                // 'gender',



                // 'experience',

                // 'specialization',

                'role',

                // 'current_status',


                [
                    'attribute' => 'current_status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getCurrentStatusBadges();
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
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-staff']],
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
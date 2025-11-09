<?php

use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\Orders;
use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use kartik\detail\DetailView;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'MyProfile'), 'url' => ['index']];

$this->registerCss(<<<CSS
/* General Layout */
.profile-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 30px;
    background: linear-gradient(180deg, #f8fafc, #e2e8f0);
    min-height: 100vh;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

/* Cover Photo */
.cover-photo {
    height: 400px;
    background: linear-gradient(135deg, rgba(0, 50, 100, 0.7), rgba(0, 150, 200, 0.4)),
                url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1280&h=400&q=85') no-repeat center center;
    background-size: cover;
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.5s ease;
}
.cover-photo:hover {
    transform: scale(1.02);
}

/* Profile Picture */
.profile-picture {
    position: absolute;
    bottom: -80px;
    left: 50px;
    border: 8px solid #ffffff;
    border-radius: 50%;
    width: 160px;
    height: 160px;
    object-fit: cover;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.profile-picture:hover {
    transform: scale(1.1) rotate(3deg);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
}

/* Profile Header */
.profile-header {
    background: #ffffff;
    padding: 40px;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    position: relative;
    margin-bottom: 40px;
    backdrop-filter: blur(10px);
}
.profile-info {
    margin-right: 100%;
    padding-top: 20px;
}
.profile-info h2 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: -0.5px;
}
.profile-info .username {
    color: #64748b;
    font-size: 1.2rem;
    font-weight: 600;
    margin-top: 5px;
}
.profile-info .contact-info {
    color: #475569;
    font-size: 1rem;
    margin-top: 8px;
    font-style: italic;
}
.profile-actions {
    position: absolute;
    top: 40px;
    right: 40px;
    display: flex;
    gap: 15px;
}

/* Navigation Bar */
.profile-nav {
    background: rgba(255, 255, 255, 0.95);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 20px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(8px);
}
.profile-nav .nav-link {
    color: #1e293b;
    font-weight: 700;
    padding: 12px 30px;
    border-radius: 30px;
    transition: all 0.3s ease;
    position: relative;
}
.profile-nav .nav-link:hover, .profile-nav .nav-link.active {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}
.profile-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 3px;
    background: #3b82f6;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}
.profile-nav .nav-link:hover::after, .profile-nav .nav-link.active::after {
    width: 50%;
}

/* Cards */
.card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    background: #ffffff;
}
.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}
.card-header {
    background: linear-gradient(135deg, #1b024dff, #13044fff);
    color: #ffffff;
    border-bottom: none;
    padding: 20px 25px;
    font-weight: 700;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    border-radius: 20px 20px 0 0;
}
.card-header i {
    margin-right: 15px;
    font-size: 1.4rem;
}
.card-body {
    padding: 30px;
    background: #ffffff;
    border-radius: 0 0 20px 20px;
}

/* Tables */
.table {
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}
.table thead th {
    background: #f1f5f9;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 700;
    color: #1e293b;
    padding: 15px;
}
.table-hover tbody tr {
    transition: background-color 0.3s ease;
}
.table-hover tbody tr:hover {
    background-color: #e0f2fe;
}

/* Buttons */
.btn-outline-primary, .btn-outline-danger, .btn-primary {
    border-radius: 30px;
    font-size: 1rem;
    padding: 12px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    border: none;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}
.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    transform: translateY(-2px);
}
.btn-outline-danger {
    border-color: #ef4444;
    color: #ef4444;
}
.btn-outline-danger:hover {
    background: #ef4444;
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

/* Badges */
.badge {
    padding: 10px 15px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 25px;
    transition: transform 0.3s ease;
}
.badge:hover {
    transform: scale(1.05);
}

/* Alerts */
.alert {
    border-radius: 12px;
    padding: 20px;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
}
.alert i {
    margin-right: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .cover-photo {
        height: 300px;
    }
    .profile-picture {
        width: 120px;
        height: 120px;
        bottom: -60px;
        left: 30px;
    }
    .profile-info {
        margin-left: 160px;
        padding-top: 15px;
    }
    .profile-info h2 {
        font-size: 1.8rem;
    }
    .profile-actions {
        position: static;
        margin-top: 20px;
        text-align: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }
    .profile-nav .nav-link {
        padding: 10px 20px;
        font-size: 0.95rem;
    }
    .card-header {
        font-size: 1.1rem;
        padding: 15px 20px;
    }
    .card-body {
        padding: 20px;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.card, .profile-header, .profile-nav {
    animation: fadeIn 0.6s ease-out;
}
CSS);
?>
<!--
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> -->

<div class="profile-container-fluid">
    <!-- Cover Photo and Profile Picture -->
    <div class="cover-photo">
        <?php if (! empty($model->profile_image)): ?>
            <?php $profileImage = Yii::getAlias('@web') . '/' . ltrim($model->profile_image, '/'); ?>
            <img src="<?php echo Html::encode($profileImage) ?>" class="profile-picture" alt="Profile Picture">
        <?php endif; ?>
    </div>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-info" style="color:darkorchid">
            <h2><?php echo Html::encode($model->first_name) ?> <span class="username">(@<?php echo Html::encode($model->username) ?>)</span></h2>
            <div class="contact-info"><?php echo Html::encode($model->email) ?> | <?php echo Html::encode($model->contact_no) ?></div>
        </div>
        <div class="profile-actions">
            <?php
            if ($model->vendor_store_type == User::VENDOR_STORE_TYPE_MULTI): ?>
                <?php echo Html::a('<i class="fas fa-store"></i> Create Store', [
                    '/admin/vendor-details/create-vendor',
                    'id' => $model->id,
                ], [
                    'class' => 'btn btn-primary',
                    'title' => 'Create a new store for this vendor',
                ]) ?>

                <?php echo Html::a(
                    'Upload  ExcelStore data',
                    ['vendor-details/import'],
                    ['class' => 'btn btn-danger']
                ) ?>
            <?php endif; ?>

            <?php echo Html::a('<i class="fas fa-edit"></i> Edit', ['/admin/users/update', 'id' => $model->id], ['class' => 'btn btn-outline-primary me-2']) ?>

            <?php echo Html::a('<i class="fas fa-trash-alt"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data'  => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method'  => 'post',
                ],
            ]) ?>
        </div>


        <!-- Navigation Bar -->
        <nav class="profile-nav">
            <ul class="nav nav-pills justify-content-center">
                <li class="nav-item"><a class="nav-link active" href="#user-info">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#order-history">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="#my-stores">Stores</a></li>
            </ul>
        </nav>

        <!-- Vendor Details -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user me-2"></i> Vendor Details
            </div>
            <div class="card-body">
                <?php echo DetailView::widget([
                    'model'      => $model,
                    'condensed'  => true,
                    'hover'      => true,
                    'mode'       => DetailView::MODE_VIEW,
                    'panel'      => false,
                    'buttons1'   => '',
                    'attributes' => [
                        'username',
                        'email',
                        'first_name',
                        'last_name',
                        'contact_no',
                        [
                            'attribute' => 'date_of_birth',
                            'value'     => $model->date_of_birth ?? 'N/A',
                        ],
                        [
                            'attribute' => 'gender',
                            'value'     => ucfirst($model->gender ?? 'N/A'),
                        ],
                        [
                            'attribute' => 'address',
                            'format'    => 'ntext',
                            'value'     => $model->address ?? 'N/A',
                        ],
                        'location',
                        [
                            'attribute' => 'profile_image',
                            'format'    => 'raw',
                            'value'     => Html::img(
                                ! empty($model->profile_image) ? $model->profile_image : 'https://via.placeholder.com/80',
                                ['class' => 'img-thumbnail', 'style' => 'max-width: 80px;']
                            ),
                        ],
                        [
                            'attribute' => 'user_role',
                            'value'     => ucfirst($model->user_role ?? 'N/A'),
                        ],
                        'business_name',
                        'gst_number',
                        [
                            'attribute'       => 'status',
                            'value'           => $model->status ? 'Active' : 'Inactive',
                            'valueColOptions' => ['style' => 'color:' . ($model->status ? 'green' : 'red')],
                        ],
                        [
                            'attribute' => 'created_at',
                            'format'    => ['datetime', 'php:d M Y, h:i A'],
                        ],
                        [
                            'attribute' => 'updated_at',
                            'format'    => ['datetime', 'php:d M Y, h:i A'],
                        ],
                    ],
                ]) ?>
            </div>
        </div>

        <!-- Order History -->
        <!-- Order History -->
        <div id="order-history" class="card">
            <div class="card-header"><i class="fas fa-box-open"></i> Order History</div>
            <div class="card-body">
                <?php
                $orderIdFromUrl = Yii::$app->request->get('id');
                $orderModel     = Orders::findOne($orderIdFromUrl);
                $comboQuery     = ComboOrder::find()
                    ->where(['order_id' => $orderIdFromUrl])
                    ->orderBy(['id' => SORT_DESC]);
                $orderDataProvider = new ActiveDataProvider([
                    'query'      => $comboQuery,
                    'pagination' => ['pageSize' => 10],
                ]);

                if ($orderDataProvider->getTotalCount() > 0) {
                    echo GridView::widget([
                        'dataProvider'   => $orderDataProvider,
                        'hover'          => true,
                        'condensed'      => true,
                        'responsiveWrap' => false,
                        'bordered'       => false,
                        'striped'        => true,
                        'layout'         => '{items}{pager}',
                        'tableOptions'   => ['class' => 'table table-sm table-hover mb-0'],
                        'columns'        => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'id',
                            'order_id',
                            'vendor_details_id',
                            'combo_package_id',
                            'status',
                            [
                                'attribute' => 'amount',
                                'format'    => ['decimal', 2],
                            ],
                            [
                                'class'          => 'yii\grid\ActionColumn',
                                'template'       => '{view}',
                                'header'         => 'Actions',
                                'buttons'        => [
                                    'view' => function ($url, $model) {
                                        return Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            Url::to(['/admin/orders/view', 'id' => $model->id]),
                                            [
                                                'title' => 'View Order',
                                                'class' => 'btn btn-sm btn-outline-primary',
                                            ]
                                        );
                                    },
                                ],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                        ],
                    ]);
                } else {
                    echo '<div class="alert d-flex align-items-center"
                     style="background-color:#6f42c1; color:white; font-weight:600; border:4px solid #d3cfdbff; border-radius:8px;">
                     <i class="fas fa-info-circle me-2" style="font-size:20px;"></i>
                     <span>No combo orders found for this Order ID.</span>
                   </div>';
                }
                ?>
            </div>
        </div>

        <?php

        // Fetch vendor stores related to this user
        $vendorQuery = VendorDetails::find()
            ->where([
                'or',
                ['user_id' => $model->id],
                ['main_vendor_user_id' => $model->id],
            ])
            ->andWhere(['!=', 'status', VendorDetails::STATUS_DELETE]);

        // Count of stores
        $storeCount = $vendorQuery->count();

        // Use the value from DB (set when creating/updating vendor)
        $vendorStoreType = $model->vendor_store_type;
        ?>
        <div id="my-stores" class="card">
            <div class="card-header">
                <i class="fas fa-store"></i> My Stores
                <span class="badge bg-light text-dark ms-2"><?php echo $storeCount ?></span>
                <span class="badge ms-2 <?php echo $vendorStoreType == 2 ? 'bg-success' : 'bg-primary' ?>">
                    <?php echo $vendorStoreType == 2 ? 'Multi-Store Vendor' : 'Single Store Vendor' ?>
                </span>
            </div>

            <div class="card-body">
                <?php if ($vendorStoreType == 1): ?>
                    <!-- Single store -->
                    <?php if ($storeCount > 0): ?>
                        <?php $singleStore = $vendorQuery->one(); ?>
                        <div class="single-store-view">
                            <?php echo DetailView::widget([
                                'model'      => $singleStore,
                                'options'    => ['class' => 'table table-sm table-bordered'],
                                'attributes' => [
                                    'business_name',
                                    'gst_number',
                                    [
                                        'attribute' => 'main_category_id',
                                        'format'    => 'raw',
                                        'value'     => function ($model) {
                                            return $model->main_category_id;
                                        },
                                    ],
                                    'address',
                                    [
                                        'attribute' => 'status',
                                        'format'    => 'raw',
                                        'value'     => $singleStore->getStatusLabel(), // works
                                    ],

                                    'created_on:datetime',
                                ],
                            ]) ?>
                        </div>
                        <?php echo Html::a(
                            '<i class="fas fa-eye"></i> View Store Details',
                            ['/admin/vendor-details/view', 'id' => $singleStore->id],
                            ['class' => 'btn btn-primary mt-2']
                        ) ?>
                    <?php else: ?>
                        <div class="alert d-flex align-items-center"
                            style="background-color:#6f42c1; color:white; font-weight:600; border:4px solid #d3cfdbff; border-radius:8px;">
                            <i class="fas fa-info-circle me-2" style="font-size:20px;"></i>
                            <span>No store found for this vendor.</span>
                        </div>
                    <?php endif; ?>

                <?php elseif ($vendorStoreType == 2): ?>
                    <!-- Multiple stores -->
                    <?php if ($storeCount === 0): ?>
                        <div class="alert d-flex align-items-center"
                            style="background-color:#6f42c1; color:white; font-weight:600; border:4px solid #d3cfdb; border-radius:8px;">
                            <i class="fas fa-info-circle me-2" style="font-size:20px;"></i>
                            <span>No Store found for this vendor.</span>
                        </div>
                    <?php else: ?>
                        <div class="alert d-flex align-items-center"
                            style="background-color:#6f42c1; color:white; font-weight:600; border:4px solid #d3cfdb; border-radius:8px;">
                            <i class="fas fa-info-circle me-2" style="font-size:20px;"></i>
                            <span>This vendor has <?php echo $storeCount ?> stores.</span>
                        </div>

                        <?php
                        $vendorDataProvider = new ActiveDataProvider([
                            'query'      => $vendorQuery->orderBy(['id' => SORT_DESC]),
                            'pagination' => ['pageSize' => 10],
                        ]);

                        echo GridView::widget([
                            'dataProvider'   => $vendorDataProvider,
                            'hover'          => true,
                            'condensed'      => true,
                            'responsiveWrap' => false,
                            'bordered'       => false,
                            'striped'        => true,
                            'layout'         => '{items}{pager}',
                            'tableOptions'   => ['class' => 'table table-sm table-hover mb-0'],
                            'columns'        => [
                                ['class' => 'yii\grid\SerialColumn'],
                                'business_name',
                                'gst_number',
                                [
                                    'attribute' => 'main_category_id',
                                    'format'    => 'raw',
                                    'value'     => function ($model) {
                                        return $model->main_category_id;
                                    },
                                ],
                                [
                                    'attribute' => 'Address',
                                    'format'    => 'raw',
                                    'value'     => function ($model) {
                                        return $model->address;
                                    },
                                ],
                                [
                                    'attribute' => 'status',
                                    'format'    => 'raw',
                                    'value'     => function ($store) {
                                        return $store->getStateOptionsBadges();
                                    },
                                ],
                                //      [
                                //     'attribute' => 'status',
                                //     'format'    => 'raw',
                                //     'value'     => function ($model) {
                                //         switch ($model->status) {
                                //             case VendorDetails::STATUS_ACTIVE:
                                //                 return '<span class="badge bg-success">Active</span>';
                                //             case VendorDetails::STATUS_INACTIVE:
                                //                 return '<span class="badge bg-danger">Inactive</span>';
                                //             case VendorDetails::STATUS_DELETE:
                                //                 return '<span class="badge bg-secondary">Deleted</span>';
                                //             // default:
                                //             //     return '<span class="badge bg-warning">Unknown</span>';
                                //         }
                                //     },
                                // ],

                                [
                                    'class'    => 'yii\grid\ActionColumn',
                                    'template' => '{view}',
                                    'buttons'  => [
                                        'view' => function ($url, $store) {
                                            return Html::a(
                                                '<i class="fas fa-eye"></i>',
                                                ['/admin/vendor-details/view', 'id' => $store->id],
                                                ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'View Store']
                                            );
                                        },
                                    ],
                                ],
                            ],
                        ]);
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
        <!-- Add this in layouts/main.php -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorPayout */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Payouts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f1f1f1 !important;
    color: #333;
}
.table td img {
    max-width: 100px;
    height: auto;
}
.grid-view {
    overflow-x: auto;
}
.card-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
}
.card-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
}
.card {
    border-radius: 1rem;
    box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
}
.card-body {
    background-color: #fff;
    padding: 1.25rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 8px 20px;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    color: #fff;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.beautiful-btn i {
    margin-right: 6px;
}
.beautiful-btn.update {
    background: linear-gradient(to right, #36d1dc, #5b86e5);
}
.beautiful-btn.delete {
    background: linear-gradient(to right, #f85032, #e73827);
}
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
CSS);
?>


<!-- Vendor payout Action Buttons -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header">
        <h5>
            <i class="fas fa-image me-2"></i>
            <?= Html::encode($model->id) ?>
            <?= Html::encode($model->vendorDetails->business_name ?? 'N/A') ?>
        </h5>
    </div>
    <div class="card-body text-center">
        <div class="d-flex flex-wrap justify-content-center gap-4 py-2">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
                'class' => 'btn beautiful-btn update'
            ]) ?>
            <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn beautiful-btn delete',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Vendor Earnings GridView -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock me-2"></i> Vendor Payout</h5>
    </div>
    <div class="card-body">
        <?php
        $dataProvider = new ArrayDataProvider([
            'allModels' => [$model],
            'pagination' => false,
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-bordered table-hover table-sm'],
            'columns' => [
                [
                    'label' => Yii::t('app', 'ID'),
                    'value' => fn($model) => $model->id ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'vendor_details_id '),
                    'value' => fn($model) => $model->vendor_details_id  ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Amount'),
                    'value' => fn($model) => $model->amount ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Payment Type'),
                    'value' => fn($model) => $model->payment_type ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Method Reason'),
                    'value' => fn($model) => $model->method_reason ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Type ID'),
                    'value' => fn($model) => $model->type_id ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'value' => fn($model) => $model->getStateOptionsBadges(),
                ],
            ],
        ]);
        ?>
    </div>
</div>
<!-- VendorDetails DetailView -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header bg-success text-white rounded-top-4">
        <h4 class="mb-0">
            <i class="fas fa-store me-2"></i>
            Vendor Details <?= Html::encode($this->title) ?>
        </h4>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>VendorDetails<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnVendorDetails = [
        ['attribute' => 'id', 'visible' => false],
        'user_id',
        'city_id',
        'business_name',
        'description',
        'main_category_id',
        'website_link',
        'gst_number',
        'latitude',
        'longitude',
        'coordinates',
        'address',
        'logo',
        'shop_licence_no',
        'avg_rating',
        'min_order_amount',
        'commission_type',
        'commission',
        'offer_tag',
        'service_radius',
        'min_service_fee',
        'discount',
        'is_top_shop',
        'gender_type',
        'status',
        'service_type_home_visit',
        'service_type_walk_in',
    ];
    echo DetailView::widget([
        'model' => $model->vendorDetails,
        'attributes' => $gridColumnVendorDetails    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>User<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUser = [
        ['attribute' => 'id', 'visible' => false],
        'username',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'first_name',
        'last_name',
        'lat',
        'lng',
        'contact_no',
        'alternative_contact',
        'date_of_birth',
        'gender',
        'description',
        'address',
        'location',
        'profile_image',
        'user_role',
        'oauth_client_user_id',
        'oauth_client',
        'access_token',
        'device_token',
        'device_type',
        'status',
        'online_status',
        'account_type',
        'referral_code',
        'referal_id',
        'signup_type',
        'business_name',
        'gst_number',
        'is_tiffin_box',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
    ];
    echo DetailView::widget([
        'model' => $model->createUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>User<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUser = [
        ['attribute' => 'id', 'visible' => false],
        'username',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'first_name',
        'last_name',
        'lat',
        'lng',
        'contact_no',
        'alternative_contact',
        'date_of_birth',
        'gender',
        'description',
        'address',
        'location',
        'profile_image',
        'user_role',
        'oauth_client_user_id',
        'oauth_client',
        'access_token',
        'device_token',
        'device_type',
        'status',
        'online_status',
        'account_type',
        'referral_code',
        'referal_id',
        'signup_type',
        'business_name',
        'gst_number',
        'is_tiffin_box',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
    ];
    echo DetailView::widget([
        'model' => $model->updateUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
</div>


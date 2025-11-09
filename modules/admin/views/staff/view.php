<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Staff'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Custom CSS
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
<!-- Staff Action Buttons Card -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-sliders-h me-2"></i>
            <?= Html::encode($model->id) ?>
        </h5>
    </div>
    <div class="card-body text-center">
        <div class="d-flex flex-wrap justify-content-center gap-4 py-3">
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

<!-- Staff Info Grid -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-tools me-2"></i> Staff Information</h5>
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
            'tableOptions' => ['class' => 'table table-sm table-bordered table-hover mb-3'],
            'columns' => [
                [
                    'label' => Yii::t('app', 'Vendor ID'),
                    'value' => function ($model) {
                        return $model->vendorDetails->id ?? '-';
                    }
                ],
                [
                    'label' => Yii::t('app', 'Profile Image'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->profile_image, ['alt' => 'Image', 'style' => 'height: 60px;']);
                    }
                ],
                'mobile_no',
                'full_name',
                'email:email',
                [
                    'attribute' => 'gender',
                    'format' => 'raw',
                    'value' => fn($model) => $model->getGenderOptionsBadges()
                ],
                'dob',
                'role',
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => fn($model) => $model->getStateOptionsBadges()
                ],
            ],
        ]);
        ?>
    </div>
</div>

<!-- Vendor Details Section -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user me-2"></i>
            Vendor Details - <?= Html::encode($model->vendorDetails->business_name ?? 'N/A') ?>
        </h5>
    </div>
    <div class="card-body">
        <?php
        $gridColumnVendorDetails = [
            ['attribute' => 'id', 'visible' => false],
            'user_id',
            'city_id',
            'business_name',
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function ($model){

                    return $model->description;

                }
            ],
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
            [
                'attribute' => 'discount',
                'value' => fn($model) => $model->discount ?? '(not set)'
            ],
            [
                'attribute' => 'is_top_shop',
                'value' => fn($model) => $model->is_top_shop ? 'Yes' : 'No'
            ],
            [
                'attribute' => 'gender_type',
                'value' => function ($model) {
                    $genders = [1 => 'Male', 2 => 'Female', 3 => 'Unisex'];
                    return $genders[$model->gender_type] ?? 'N/A';
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn($model) =>
                    $model->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>'
            ],
            [
                'attribute' => 'service_type_home_visit',
                'value' => fn($model) => $model->service_type_home_visit ? 'Yes' : 'No'
            ],
            [
                'attribute' => 'service_type_walk_in',
                'value' => fn($model) => $model->service_type_walk_in ? 'Yes' : 'No'
            ],
        ];

        echo DetailView::widget([
            'model' => $model->vendorDetails,
            'attributes' => $gridColumnVendorDetails,
            'options' => ['class' => 'table table-bordered table-hover table-striped'],
        ]);
        ?>
    </div>
</div>

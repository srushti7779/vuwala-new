<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use yii\data\ArrayDataProvider;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BusinessImages */

$this->title = "Store Timings – ID: " . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Timings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #212529;
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
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
    border-bottom: none;
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

<!-- Action Buttons -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header">
        <h5>
            <i class="fas fa-image me-2"></i>
            ID: <?= Html::encode($model->id) ?> —
            Business: <?= Html::encode($model->vendorDetails->business_name ?? 'N/A') ?>
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

<!-- Store Timings GridView -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock me-2"></i>Store Timings</h5>
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
                    'label' => Yii::t('app', 'Vendor ID'),
                    'value' => function($model) {
                        return $model->vendorDetails->id ?? '-';
                    },
                ],
                [
                    'label' => Yii::t('app', 'Day'),
                    'value' => fn($model) => $model->day->title ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Start Time'),
                    'value' => fn($model) => $model->start_time ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Close Time'),
                    'value' => fn($model) => $model->close_time ?? '-',
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
<!-- Vendor Details -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-store me-2"></i>Vendor Details</h5>
    </div>
    <div class="card-body">
        <?php
        $gridColumnVendorDetails = [
            ['attribute' => 'id', 'visible' => false],
            'user_id',
            'business_name',
            [
                'attribute' => 'main_category_id',
                'label' => 'Main Category',
                'value' => fn($model) => $model->mainCategory->name ?? '-',
            ],
            'website_link',
            'gst_number',
            'latitude',
            'longitude',
            'address',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn($model) => $model->getStateOptionsBadges(),
            ],
        ];

        echo DetailView::widget([
            'model' => $model->vendorDetails,
            'attributes' => $gridColumnVendorDetails
        ]);
        ?>
    </div>
</div>

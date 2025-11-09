<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\City */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cities'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #343a40;
}
.table td img, .table td video {
    max-width: 100px;
    height: auto;
}
.card-header {
    background: linear-gradient(90deg, #6a11cb, #2575fc);
    color: white;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.card-header h5 {
    margin: 0;
}
.card {
    border-radius: 1rem;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.card-body {
    background-color: #ffffff;
    padding: 1.5rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 8px 20px;
    border-radius: 30px;
    color: #fff;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

/* GridView Enhancements */
.kv-panel-heading {
    background: linear-gradient(135deg, #36d1dc, #5b86e5);
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}
.kv-grid-table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 14px;
}
.kv-grid-table td {
    font-size: 13px;
    color: #212529;
    padding: 0.75rem;
    vertical-align: middle;
}
.kv-grid-table tr:nth-child(even) {
    background-color: #f8fafd;
}
.kv-grid-table tr:hover {
    background-color: #e2f0ff;
    transition: background 0.3s ease;
}
CSS);
?>

<div class="city-view">
    <!-- Card with Action Buttons -->
    <div class="card">
        <div class="card-header">
            <h4><?= Yii::t('app', 'City') . ' ' . Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body text-center">
            <div class="d-flex flex-wrap justify-content-center gap-3 py-2">
                <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn beautiful-btn update']) ?>
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

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
        <h4><?= Yii::t('app', 'City') . ' ' . Html::encode($this->title) ?></h4>
    </div>
    <div class="card-body">
        <?php
       
       
        $dataProvider = new ArrayDataProvider([
            'allModels' => [$model],
            'pagination' => false
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'export' => false,
            'toolbar' => false,
            'bordered' => false,
            'striped' => true,
            'condensed' => true,
            'hover' => true,
            'responsive' => true,
            'columns' => [
                [
                    'attribute' => 'user.username',
                    'label' => Yii::t('app', 'User'),
                ],
                'name',
                [
                    'attribute' => 'method_reason',
                    'label' => Yii::t('app', 'Method / Reason'),
                    'format' => 'ntext',
                ],
                [
                    'attribute' => 'status',
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    },
                ],
            ],
            'tableOptions' => ['class' => 'table table-bordered table-sm mb-0'],
        ]);
        ?>
    </div>
</div>


    <!-- Service Pin Codes -->
    <?php if ($providerServicePinCode->totalCount): ?>
    <div class="card">
        <div class="card-body">
            <?php
            $gridColumnServicePinCode = [
                ['class' => 'yii\grid\SerialColumn'],
                ['attribute' => 'id', 'visible' => false],
                'area_pin_code',
                'status',
            ];
            echo GridView::widget([
                'dataProvider' => $providerServicePinCode,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-service-pin-code']],
                'responsive' => true,
                'hover' => true,
                'bordered' => false,
                'striped' => false,
                'condensed' => false,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-map-marker-alt me-2"></i> ' . Yii::t('app', 'Service Pin Code'),
                    'headingOptions' => ['class' => 'kv-panel-heading'],
                ],
                'export' => false,
                'columns' => $gridColumnServicePinCode,
            ]);
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vendor Details -->
<?php if ($providerVendorDetails->totalCount): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <?php
            $gridColumnVendorDetails = [
                ['class' => 'yii\grid\SerialColumn'],
                ['attribute' => 'id', 'visible' => false],
                [
                    'attribute' => 'user.username',
                    'label' => Yii::t('app', 'User'),
                ],
                [
                    'attribute' => 'business_name',
                    'format' => 'text',
                    'contentOptions' => ['style' => 'font-weight: 600; color: #3b3b3b;'],
                ],
                'description:ntext',
                [
                    'attribute' => 'mainCategory.title',
                    'label' => Yii::t('app', 'Main Category'),
                ],
                [
                    'attribute' => 'website_link',
                    'format' => 'url',
                ],
                'gst_number',
                'latitude',
                'longitude',
                'coordinates',
                'address:ntext',
                [
                    'attribute' => 'logo',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->logo, ['style' => 'max-width:80px;']);
                    },
                ],
                'shop_licence_no',
                [
                    'attribute' => 'avg_rating',
                    'format' => ['decimal', 1],
                    'contentOptions' => ['class' => 'text-end'],
                ],
                [
                    'attribute' => 'min_order_amount',
                    'format' => ['decimal', 2],
                    'label' => Yii::t('app', 'Min Order'),
                    'contentOptions' => ['class' => 'text-end'],
                ],
                'commission_type',
                [
                    'attribute' => 'commission',
                    'format' => ['decimal', 2],
                    'contentOptions' => ['class' => 'text-end'],
                ],
                'offer_tag',
                'service_radius',
                'min_service_fee',
                'discount',
                [
                    'attribute' => 'is_top_shop',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->is_top_shop ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                    },
                ],
                'gender_type',
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    },
                ],
                [
                    'attribute' => 'service_type_home_visit',
                    'format' => 'boolean',
                    'label' => 'Home Visit',
                ],
                [
                    'attribute' => 'service_type_walk_in',
                    'format' => 'boolean',
                    'label' => 'Walk-in',
                ],
            ];

            echo \kartik\grid\GridView::widget([
                'dataProvider' => $providerVendorDetails,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-vendor-details']],
                'responsive' => true,
                'hover' => true,
                'striped' => true,
                'bordered' => false,
                'condensed' => false,
                'summary' => false,
                'export' => false,
                'toolbar' => false,
                'panel' => [
                    'type' => \kartik\grid\GridView::TYPE_PRIMARY,
                    'heading' => '<i class="fas fa-store me-2"></i>' . Yii::t('app', 'Vendor Details'),
                    'headingOptions' => ['class' => 'kv-panel-heading'],
                ],
                'tableOptions' => ['class' => 'table table-bordered table-sm mb-0'],
                'columns' => $gridColumnVendorDetails,
            ]);
            ?>
        </div>
    </div>
<?php endif; ?>

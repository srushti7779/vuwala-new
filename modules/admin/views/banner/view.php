<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Banner */
/* @var $bannerRecharges array */
/* @var $providerBannerTimings array */
/* @var $bannerChargeLogs array */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banners'), 'url' => ['index']];
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

.beautiful-btn.recharge {
    background: linear-gradient(to right, #56ab2f, #a8e063);
}

.beautiful-btn.timing {
    background: linear-gradient(to right, #fbc02d, #ffeb3b);
    color: #000;
}

.beautiful-btn.logs {
    background: linear-gradient(to right, #0db423ff, #09ac42ff);
    color: #000;
}

.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
  .summary-card {
        height: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 15px;
        border-radius: 15px;
        color: #fff;
        transition: transform 0.3s, box-shadow 0.3s;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .summary-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .summary-card i {
        font-size: 2rem;
        margin-right: 20px;
        padding: 5px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .summary-card .card-info h4 {
        font-size: 1rem;
        font-weight: 800;
        margin: 10px;
    }

    .summary-card .card-info p {
        font-size: 1.3rem;
        font-weight: bold;
        margin: 5px 0 0;
    }

    @media (max-width: 768px) {
        .summary-card {
            flex-direction: column;
            text-align: center;
        }

        .summary-card i {
            margin-bottom: 10px;
        }
    }
CSS);
?>

<?php
$dataProvider = new ArrayDataProvider([
    'allModels' => [$model],
    'pagination' => false,
]);
?>
<!-- Banner Action Buttons -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header" style="background: linear-gradient(135deg, #6a11cb, #2575fc); color: white;">
        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Banner Creations</h5>
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap justify-content-center gap-4 py-3">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
                'class' => 'btn beautiful-btn update'
            ]) ?>

            <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>

                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn beautiful-btn delete',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>

                <?= Html::a('<i class="fas fa-wallet"></i> Banner Recharges', [
                    '/admin/banner-recharges/create',
                    'vendor_id' => $model->vendor_details_id,
                    'banner_id' => $model->id
                ], [
                    'class' => 'btn beautiful-btn recharge',
                    'title' => 'Create a recharge for this banner'
                ]) ?>

                <?= Html::a('<i class="fas fa-clock"></i> Banner Timings', [
                    '/admin/banner-timings/create',
                    'banner_id' => $model->id
                ], [
                    'class' => 'btn beautiful-btn timing',
                    'title' => 'Create timing for this banner'
                ]) ?>

                <?= Html::a('<i class="fas fa-file-invoice-dollar"></i> Banner View Logs', ['/admin/banner-charge-logs/'], [
                    'class' => 'btn beautiful-btn logs',
                    'title' => 'View charge logs for this banner'
                ]) ?>

            <?php } ?>
        </div>

    </div>
</div>
<?php
$chargeLogsProvider = new ArrayDataProvider([
    'allModels' => $bannerChargeLogs,
    'pagination' => false,
]);

$clickCount = 0;
$viewCount = 0;

foreach ($bannerChargeLogs as $log) {
    if (isset($log['action'])) {
        if ($log['action'] === 'click') {
            $clickCount++;
        } elseif ($log['action'] === 'view') {
            $viewCount++;
        }
    }
}
?>

<!-- Clicks & Views Summary Card Section -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header" style="background: linear-gradient(135deg, #2f80ed, #1cb5e0); color: white;">
        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Click & View Summary</h5>
    </div>
    <div class="card-body">
        <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center">

            <!-- Total Clicks -->
           <div class="col">
            <div class="summary-card text-white text-center p-4 rounded-4 shadow" style="background: linear-gradient(135deg, rgb(5, 138, 56), rgb(32, 3, 78));">
                <i class="fas fa-mouse-pointer fa-2x mb-3"></i>
                <div class="card-info">
                    <h4 class="mb-2">Total Clicks</h4>
                    <h3 class="mb-0"><?= $clickCount ?></h3>
                </div>
            </div>
        </div>
            <!-- Total Views -->
            <div class="col">
                <div class="summary-card text-white text-center p-4 rounded-4 shadow" style="background: linear-gradient(135deg,rgba(18, 165, 178, 1),rgba(32, 3, 100, 1));">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <div class="card-info">
                         <h4 class="mb-2">Total Views</h4>
                        <h3 class="mb-0"><?= $viewCount ?></h3>

                    </div>
                   
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Banner Details -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2"></i>Banner Details</h5>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => true,
            'condensed' => true,
            'responsiveWrap' => false,
            'bordered' => false,
            'striped' => true,
            'layout' => '{items}{pager}',
            'tableOptions' => ['class' => 'table table-sm table-hover mb-4 table-bordered table-striped'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'title',
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->image
                            ? Html::img($model->image, ['class' => 'img-thumbnail', 'style' => 'max-width:100px'])
                            : '<span class="text-muted">No image</span>';
                    },
                ],
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'white-space: normal; max-width: 250px;']
                ],
                [
                    'attribute' => 'mainCategory.title',
                    'label' => 'Main Category',
                ],
                [
                    'attribute' => 'vendorDetails.id',
                    'label' => 'Vendor ID',
                ],
                'position',
                'type_id',
                'sort_order',
                'start_date',
                'end_date',
                'views_count',
                [
                    'attribute' => 'is_top_banner',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->is_top_banner
                            ? '<span class="badge bg-success">Yes</span>'
                            : '<span class="badge bg-secondary">No</span>';
                    },
                ],
                [
                    'attribute' => 'is_pop_up_banner',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->is_pop_up_banner
                            ? '<span class="badge bg-warning text-dark">Yes</span>'
                            : '<span class="badge bg-light text-muted">No</span>';
                    },
                ],
                [
                    'attribute' => 'is_featured',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->is_featured
                            ? '<span class="badge bg-primary">Featured</span>'
                            : '<span class="badge bg-light text-muted">No</span>';
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->status
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-danger">Inactive</span>';
                    },
                ],
            ],
        ]) ?>
    </div>
</div>


<?php
$rechargesProvider = new ArrayDataProvider([
    'allModels' => $bannerRecharges,
    'pagination' => false,
]);
?>

<!-- Banner Recharges -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-bolt me-2"></i>Banner Recharges</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($bannerRecharges)): ?>
            <?= GridView::widget([
                'dataProvider' => $rechargesProvider,
                'hover' => true,
                'condensed' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-sm table-hover mb-4 table-bordered table-striped'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'vendor_id',
                    'banner_id',
                    'start_time',
                    'amount',
                    'end_time',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->status
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-danger">Inactive</span>';
                        },
                    ],
                ],
            ]) ?>
        <?php else: ?>
            <p class="text-muted">No banner recharges found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$timingsProvider = new ArrayDataProvider([
    'allModels' => $providerBannerTimings,
    'pagination' => false,
]);
?>

<!-- Banner Timings -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock me-2"></i>Banner Timings</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($providerBannerTimings)): ?>
            <?= GridView::widget([
                'dataProvider' => $timingsProvider,
                'hover' => true,
                'condensed' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-sm table-hover mb-4 table-bordered table-striped'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'banner_id',
                    'start_time',
                    'end_time',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->status
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-danger">Inactive</span>';
                        },
                    ],
                ],
            ]) ?>
        <?php else: ?>
            <p class="text-muted">No banner timings found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$chargeLogsProvider = new ArrayDataProvider([
    'allModels' => $bannerChargeLogs,
    'pagination' => false,
]);
?>

<!-- Banner Charge Logs -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-file-invoice-dollar me-2"></i>Banner Charge Logs</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($bannerChargeLogs)): ?>
            <?= GridView::widget([
                'dataProvider' => $chargeLogsProvider,
                'hover' => true,
                'condensed' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-sm table-hover mb-4 table-bordered table-striped'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    [
                        'attribute' => 'user_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model['user_id'] ?? '<span class="text-muted">(not set)</span>';
                        }
                    ],
                    [
                        'attribute' => 'action',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model['action'] === 'view' && isset($model['user_id']) && is_numeric($model['user_id'])) {
                                return Html::a('view', ['/admin/users/view', 'id' => $model['user_id']], [
                                    'target' => '_blank',
                                    'title' => 'View User Profile',
                                    'style' => 'color: #0d6efd; text-decoration: underline;'
                                ]);
                            }
                            return Html::encode($model['action']);
                        },
                    ],
                    'charge_amount',
                    'ip_address',
                    'performed_at',
                    'user_agent',
                ],
            ]) ?>
        <?php else: ?>
            <p class="text-muted">No banner charge logs found.</p>
        <?php endif; ?>
    </div>
</div>


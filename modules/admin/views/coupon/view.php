<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coupons'), 'url' => ['index']];
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
<div class="container-fluid coupon-view">

    <!-- Coupon Header -->
  <div class="card">
    <div class="card-header">
          <h4><?= Yii::t('app', 'Coupon') . ' ' . Html::encode($this->title) ?></h4>
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

   <!-- Coupon Details (GridView) -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h4><?= Yii::t('app', 'Coupon') . ' ' . Html::encode($this->title) ?></h4>
    </div>
    
    <div class="card-body">
        <?php 
        $dataProvider = new ArrayDataProvider([
        'allModels' => [$model],
        'pagination' => false,
    ]);
        
        
        ?>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-sm table-bordered table-hover mb-4'],
            'pjax' => false,
            'export' => false,
            'toolbar' => false,
            'panel' => false,
            'responsive' => true,
            'hover' => true,
            'striped' => false,
            'bordered' => false,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'name',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'value' => function ($model) {
                        return strip_tags(html_entity_decode($model->description));
                    },
                ],
                'code',
                'discount',
                'max_discount',
                'min_cart',
                'start_date',
                'end_date',
                [
                    'attribute' => 'is_global',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getGlobalOptionsBadges();
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    },
                ],
            ],
        ]) ?>
    </div>
</div>

    <?php
    $appliedQuery = $model->getCouponsApplieds()
        ->where(['IS NOT', 'order_id', null])
        ->andWhere(['coupon_id' => $model->id])
        ->orderBy(['id' => SORT_DESC]);

    $applied = $appliedQuery->all();

    // Calculate total coupon amount
    $totalAmount = 0;
    foreach ($applied as $appliedCoupon) {
        if (!empty($appliedCoupon->order) && $appliedCoupon->order->voucher_amount > 0) {
            $totalAmount += $appliedCoupon->order->voucher_amount;
        }
    }
    ?>
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header" style="background: linear-gradient(135deg, #2f80ed, #1cb5e0); color: white;">
        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Coupon Summary</h5>
    </div>
    <div class="card-body">
        <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center">

            <!-- Total Used Coupon Amount -->
            <div class="col">
                <div class="summary-card text-white text-center p-4 rounded-4 shadow" style="background: linear-gradient(135deg, rgba(14, 2, 104, 1), #45c921ff);">
                    <i class="fas fa-wallet fa-2x mb-3"></i>
                    <div class="card-info">
                        <h4 class="mb-2">Total Used Coupon Amount</h4>
                        <h3 class="mb-0">₹<?= number_format($totalAmount, 2) ?></h3>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



</div>
    <!-- Coupon Usage History -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5><i class="fas fa-users me-2"></i>Coupon Usage History</h5>
        </div>
        <div class="card-body">
            <?php
            if (!empty($applied)) {
                $appliedProvider = new ActiveDataProvider([
                    'query' => $appliedQuery,
                    'pagination' => ['pageSize' => 10],
                ]);

                echo GridView::widget([
                    'dataProvider' => $appliedProvider,
                    'hover' => true,
                    'condensed' => true,
                    'responsiveWrap' => false,
                    'bordered' => false,
                    'striped' => true,
                    'layout' => '{items}{pager}',
                    'tableOptions' => ['class' => 'table table-sm table-hover mb-0'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'id',
                        'order_id',
                        'coupon_id',
                        'status',
                        [
                            'attribute' => 'applied_on',
                            'format' => ['date', 'php:d M Y h:i A'],
                        ],
                        [
                            'label' => 'Voucher Discount',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if (!empty($model->order) && $model->order->voucher_amount > 0) {
                                    return Html::a(
                                        '₹' . number_format($model->order->voucher_amount, 2),
                                        ['orders/view', 'id' => $model->order_id],
                                        ['class' => 'btn btn-sm btn-outline-success']
                                    );
                                }
                                return '<span class="text-muted small">No Discount</span>';
                            },
                        ],
                    ],
                ]);
            } else {
                echo '<div class="text-muted small"><i class="fas fa-info-circle"></i> No users have applied this coupon yet.</div>';
            }
            ?>
        </div>
    </div>

</div>

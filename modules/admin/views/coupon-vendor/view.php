<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\CouponVendor */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coupon Vendors'), 'url' => ['index']];
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

<div class="coupon-vendor-view">

     <!-- Coupon Header -->
  <div class="card">
    <div class="card-header">
          <h4><?= Yii::t('app', 'Coupon vendor') . ' ' . Html::encode($this->title) ?></h4>
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
    <!-- Coupon Vendor GridView -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #2f80ed, #1cb5e0);">
            <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i><?= Yii::t('app', 'Vendor Information') ?></h5>
        </div>
        <div class="card-body">
            <?php
            $vendorProvider = new ArrayDataProvider([
                'allModels' => [$model],
                'pagination' => false,
            ]);

            echo GridView::widget([
                'dataProvider' => $vendorProvider,
                'summary' => false,
                'panel' => false,
                'export' => false,
                'toolbar' => false,
                'columns' => [
                    [
                        'attribute' => 'coupon.name',
                        'label' => Yii::t('app', 'Coupon'),
                    ],
                    [
                        'attribute' => 'vendorDetails.id',
                        'label' => Yii::t('app', 'Vendor Details'),
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model->getStateOptionsBadges();
                        },
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

    <!-- Coupon Details GridView -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #9D50BB, #6E48AA);">
            <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i><?= Yii::t('app', 'Coupon Details') ?></h5>
        </div>
        <div class="card-body">
            <?php
            $couponProvider = new ArrayDataProvider([
                'allModels' => [$model->coupon],
                'pagination' => false,
            ]);

            echo GridView::widget([
                'dataProvider' => $couponProvider,
                'summary' => false,
                'panel' => false,
                'export' => false,
                'toolbar' => false,
                'columns' => [
                    'name',
                    [
                        'attribute' => 'description',
                        'format' => 'ntext',
                        'value' => function($coupon) {
                            return strip_tags(html_entity_decode($coupon->description));
                        },
                    ],
                    'code',
                    'discount',
                    'max_discount',
                    'start_date',
                    'end_date',
                    'is_global',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($coupon) {
                            return $coupon->getStateOptionsBadges();
                        },
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

</div>

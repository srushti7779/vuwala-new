<?php

use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Wallet */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Wallets'), 'url' => ['index']];
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
CSS);
?>

<div class="wallet-view">
    <!-- Card with Action Buttons -->
    <div class="card">
        <div class="card-header">
            <h4><?= Yii::t('app', 'Wallet') . ' ' . Html::encode($this->title) ?></h4>
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

    <!-- GridView for Wallet Data -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Wallet Transactions</h5>
        </div>
        <div class="card-body">
            <?php 

            $dataProvider = new ArrayDataProvider([
                'allModels'=> [$model],
                'pagination' =>false

            ]); 
            ?> 
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'export' => false,
                'toolbar' => false,
                'columns' => [
                    [
                        'attribute' => 'user.username',
                        'label' => Yii::t('app', 'User'),
                    ],
                    [
                        'attribute' => 'amount',
                        'label' => Yii::t('app', 'Amount'),
                    ],
                    [
                        'attribute' => 'payment_type',
                        'label' => Yii::t('app', 'Payment Type'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getPaymentTypeOptionsBadges();
                        },
                    ],
                    [
                        'attribute' => 'method_reason',
                        'format' => 'ntext',
                        'label' => Yii::t('app', 'Method/Reason'),
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
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered mb-0'],
            ]) ?>
        </div>
    </div>
</div>

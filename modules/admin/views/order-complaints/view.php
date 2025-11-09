<?php

use app\modules\admin\models\User;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Order Complaints', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Custom CSS
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
.beautiful-btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.beautiful-btn.update {
    background-color: #0d6efd;
    color: #fff;
}

.beautiful-btn.delete {
    background-color: #dc3545;
    color: #fff;
}

.beautiful-btn.support {
    background-color: #47b860ff;
    color: #fff !important;   
}

.beautiful-btn:hover {
    opacity: 0.9;
    transform: scale(1.03);
}

CSS);
?>

<div class="order-complaints-view">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0"><?= Yii::t('app', 'Order Complaints') . ' — ' . Html::encode($model->title) ?></h4>
        </div>
        <div class="card-body text-center">
            <div class="d-flex flex-wrap justify-content-center gap-3 py-3">

                <!-- Update Button -->
                <?= Html::a(
                    '<i class="fas fa-pen"></i> Update',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn beautiful-btn update']
                ) ?>

                <!-- Delete Button -->
                <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
                    <?= Html::a(
                        '<i class="fas fa-trash"></i> Delete',
                        ['delete', 'id' => $model->id],
                        [
                            'class' => 'btn beautiful-btn delete',
                            'data' => [
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                'method' => 'post',
                            ],
                        ]
                    ) ?>

                    <!-- Support Button -->
                    <?= Html::a(
                        '<i class="fas fa-life-ring"></i> Support',
                        ['support', 'order_id' => $model->order_id, 'store_id' => $model->store_id],
                        ['class' => 'btn beautiful-btn support']
                    ) ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>



    <!-- Vendor Details -->
    <?php if ($model->store): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-store"></i> Vendor Details</h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->store,
                'options' => ['class' => 'table table-sm table-bordered table-striped table-hover mb-0'],
                'attributes' => [
                    'business_name',
                    'city_id',
                    'gst_number',
                    'website_link',
                    'account_holder_name',
                    'account_number',
                    'ifsc_code',
                    'bank_name',
                    'address',
                    'avg_rating',
                    'status',
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Order Info -->
    <?php if ($model->order): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-receipt"></i> Order Information</h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->order,
                'options' => ['class' => 'table table-sm table-bordered table-striped table-hover mb-0'],
                'attributes' => [
                    ['attribute' => 'user.username', 'label' => 'User'],
                    'qty',
                    'payment_type',
                    'sub_total',
                    'tax',
                    'payable_amount',
                    'cancel_reason',
                    'schedule_date',
                    'status',
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

 
  

    <!-- Complaint Owner -->
    <?php if ($model->user): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-user-circle"></i> Complaint Owner</h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->user,
                'options' => ['class' => 'table table-sm table-bordered table-striped table-hover mb-0'],
                'attributes' => [
                    'username',
                    'email',
                    'contact_no',
                    'gender',
                    'address',
                    'status',
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

   <?php


$dataProvider = new ArrayDataProvider([
    'allModels' => [$model], 
    'pagination' => false,   
]);
?>

<!-- Complaint Info -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-comment-dots"></i> Order Complaint Details</h5>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-sm table-bordered table-hover mb-0'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'], 
                'id',
                'order_id',
                'user_id',
                'store_id',
                'title',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                ],
                [
                    'attribute' => 'response',
                    'format' => 'ntext',
                ],
              [
                'attribute' => 'status',
                'label' => 'Status',
                'value' => function ($model) {
                    return $model->getStatusName(); // or $model->statusName if using magic getter
                },
            ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}',
                    'header' => 'Actions',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-eye"></i>',
                                Url::to(['/admin/order-complaint/view', 'id' => $model['id']]),
                                ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'View']
                            );
                        },
                        'update' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-edit"></i>',
                                Url::to(['/admin/order-complaint/update', 'id' => $model['id']]),
                                ['class' => 'btn btn-sm btn-outline-success', 'title' => 'Edit']
                            );
                        },
                    ],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]) ?>
    </div>
</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\CancellationPolicy */

$this->title = "Cancellation Policy #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cancellation Policies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.card {
    border-radius: 1rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
}
.card-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.85rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
    border: none;
}
.card-body {
    background: #fff;
    padding: 1.25rem 1.5rem;
}
.action-buttons .btn {
    border-radius: 50px;
    padding: 8px 18px;
    font-weight: 600;
}
.action-buttons .btn-primary {
    background: linear-gradient(to right, #36d1dc, #5b86e5);
    border: none;
}
.action-buttons .btn-danger {
    background: linear-gradient(to right, #f85032, #e73827);
    border: none;
}
.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.12);
}
.status-active {
    color: #28a745;
    font-weight: 600;
}
.status-inactive {
    color: #dc3545;
    font-weight: 600;
}
CSS);
?>

<div class="cancellation-policy-view">

    <!-- Header + Action Buttons -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header">
            <i class="fas fa-ban"></i>
            <span><?= Html::encode($this->title) ?></span>
        </div>
        <div class="card-body text-end action-buttons">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary me-2'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Policy Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> Policy Details</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'hours_before',
                    [
                        'attribute' => 'refundable_amount_percentage',
                        'value' => $model->refundable_amount_percentage . '%',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => $model->status ? 'Active' : 'Inactive',
                        'contentOptions' => ['class' => $model->status ? 'status-active' : 'status-inactive'],
                    ],
                    'create_user_id',
                    'update_user_id',
                    'updated_by',
                    'updated_on:datetime',
                ],
            ]) ?>
        </div>
    </div>

</div>

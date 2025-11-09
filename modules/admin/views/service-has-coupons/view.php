<?php

    use app\models\User;
    use yii\helpers\Html;
    use yii\widgets\DetailView;

    $this->title                   = "Service Has Coupons #{$model->id}";
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Has Coupons'), 'url' => ['index']];
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

<div class="service-has-coupons-view">

    <!-- Header + Action Buttons -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header">
            <i class="fas fa-ticket-alt"></i>
            <span><?php echo Html::encode($this->title)?></span>
        </div>
        <div class="card-body text-end action-buttons">
            <?php echo Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
    'class' => 'btn btn-primary me-2',
])?>
<?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
                <?php echo Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data'  => [
        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
        'method'  => 'post',
    ],
])?>
<?php endif; ?>
        </div>
    </div>

    <!-- coupon  Info -->
    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> Coupon Info</div>
        <div class="card-body">
            <?php echo DetailView::widget([
    'model'      => $model,
    'attributes' => [
        'id',
        [
            'attribute' => 'service.id',
            'label'     => Yii::t('app', 'Service'),
        ],
        [
            'attribute' => 'coupon.name',
            'label'     => Yii::t('app', 'Coupon'),
        ],
        [
            'attribute' => 'status',
            'format'    => 'raw',
            'value'     => $model->status
            ? '<span class="status-active">Active</span>'
            : '<span class="status-inactive">Inactive</span>',
        ],
    ],
])?>
        </div>
    </div>


    <!-- Service Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-concierge-bell"></i> Service Details</div>
        <div class="card-body">
            <?php echo DetailView::widget([
    'model'      => $model->service,
    'attributes' => [
        'service_name',
        'slug',
        'original_price',
        'discount_price',
        'duration',
        'type',
        [
            'attribute' => 'status',
            'format'    => 'raw',
            'value'     => $model->service->status
            ? '<span class="status-active">Active</span>'
            : '<span class="status-inactive">Inactive</span>',
        ],
    ],
])?>
        </div>
    </div>

    <!-- Coupon Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-tags"></i> Coupon Details</div>
        <div class="card-body">
            <?php echo DetailView::widget([
    'model'      => $model->coupon,
    'attributes' => [
        'name',
        'description',
        'code',
        'discount_type',
        'discount',
        'start_date',
        'end_date',
        [
            'attribute' => 'status',
            'format'    => 'raw',
            'value'     => $model->coupon->status
            ? '<span class="status-active">Active</span>'
            : '<span class="status-inactive">Inactive</span>',
        ],
    ],
])?>
        </div>
    </div>





</div>

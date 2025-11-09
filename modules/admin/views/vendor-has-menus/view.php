<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorHasMenus */

$this->title = "Vendor-Menu Mapping #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Has Menuses'), 'url' => ['index']];
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
.section-title {
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: 15px;
    color: #333;
    border-left: 4px solid #2575fc;
    padding-left: 10px;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
}
.status-active {
    color: #28a745;
    font-weight: 600;
}
.status-inactive {
    color: #dc3545;
    font-weight: 600;
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
CSS);
?>

<div class="vendor-has-menus-view">

    <!-- Header + Action Buttons -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header">
            <i class="fas fa-link"></i>
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

    <!-- Vendor-Menu Mapping -->
    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> Mapping Details</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    ['attribute' => 'id', 'visible' => false],
                    [
                        'attribute' => 'vendor.id',
                        'label' => Yii::t('app', 'Vendor'),
                    ],
                    [
                        'attribute' => 'menu.id',
                        'label' => Yii::t('app', 'Menu'),
                    ],
                    [
                        'attribute' => 'status',
                        'value' => $model->status ? 'Active' : 'Inactive',
                        'contentOptions' => ['class' => $model->status ? 'status-active' : 'status-inactive'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- Vendor Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-store"></i> Vendor Details</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->vendor,
                'attributes' => [
                    ['attribute' => 'id', 'visible' => false],
                    'business_name',
                    'description:ntext',
                    'city_id',
                    'website_link',
                    'gst_number',
                    'is_gst_number_verified',
                    'account_holder_name',
                    'account_number',
                    'ifsc_code',
                    'bank_name',
                    'bank_branch',
                    'address:ntext',
                    'status',
                    'is_verified',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Menu Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-bars"></i> Menu Details</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->menu,
                'attributes' => [
                    ['attribute' => 'id', 'visible' => false],
                    'label',
                    'route',
                    'icon',
                    'sort_order',
                    [
                        'attribute' => 'status',
                        'value' => $model->menu->status ? 'Active' : 'Inactive',
                        'contentOptions' => ['class' => $model->menu->status ? 'status-active' : 'status-inactive'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- Created By User -->
    <div class="card">
        <div class="card-header"><i class="fas fa-user"></i> Created By</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->createUser,
                'attributes' => [
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                    'contact_no',
                    'status',
                    'created_at',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Updated By User -->
    <div class="card">
        <div class="card-header"><i class="fas fa-user-edit"></i> Last Updated By</div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model->updateUser,
                'attributes' => [
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                    'contact_no',
                    'status',
                    'updated_at',
                ],
            ]) ?>
        </div>
    </div>

</div>

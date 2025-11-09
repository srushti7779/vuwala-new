<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorEarnings */

$this->title =  "Vendor Earnings – ID: " . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Earnings'), 'url' => ['index']];
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
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
CSS);
?>


<!-- Vendor Earnings Action Buttons -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header">
        <h5>
            <i class="fas fa-image me-2"></i>
            <?= Html::encode($model->id) ?>
            <?= Html::encode($model->vendorDetails->business_name ?? 'N/A') ?>
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
<!-- Vendor Earnings GridView -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock me-2"></i> Vendor Earnings</h5>
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
                    'label' => Yii::t('app', 'ID'),
                    'value' => fn($model) => $model->id ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Order'),
                    'value' => fn($model) => $model->order_id ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Vendor Details'),
                    'value' => fn($model) => $model->vendor_details_id ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Order Sub Total'),
                    'value' => fn($model) => $model->order->sub_total ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Admin Commission %'),
                    'value' => fn($model) => $model->admin_commission_per ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Admin Commission Amount'),
                    'value' => fn($model) => $model->admin_commission_amount ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Earnings Added Reason'),
                    'value' => fn($model) => $model->earnings_added_reason ?? '-',
                ],
                [
                    'label' => Yii::t('app', 'Cancelled Reason'),
                    'value' => fn($model) => $model->cancelled_reason ?? '-',
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

  <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header bg-primary text-white rounded-top-4">
        <h4 class="mb-0">
            <i class="fas fa-receipt me-2"></i>
            Orders <?= Html::encode($this->title) ?>
        </h4>
    </div>

    <div class="card-body p-4">
        <?php
        $gridColumnOrders = [
            ['attribute' => 'id', 'visible' => false],
            'shop_id',
            'vendor_details_id',
            'json_details:ntext',
            'qty',

            [
                'attribute' => 'trans_type',
                'label' => 'Transaction Type',
                'format' => 'raw',
                'value' => fn($model) => $model->getTransTypeOptionsBadges(),
            ],
            [
                'attribute' => 'payment_type',
                'label' => 'Payment Type',
                'format' => 'raw',
                'value' => fn($model) => $model->getPaymentTypeOptionBadges(),
            ],

            'sub_total',
            'tip_amt',
            'tax',
            'processing_charges',
            'service_charge',
            'taxable_total',
            'total_w_tax',
            'status',
            'cancel_reason',
            'cancel_description:ntext',
            'schedule_date:date',
            'schedule_time',
            'service_instruction:ntext',
            'voucher_code',
            'voucher_amount',
            'voucher_type',

            [
                'attribute' => 'payment_status',
                'label' => 'Payment Status',
                'format' => 'raw',
                'value' => fn($model) => $model->getPaymentStatusOptionBadges(),
            ],

            'ip_ress',
            'service_address:ntext',
            'otp',
            'cgst',
            'sgst',
            'is_verify:boolean',

            [
                'attribute' => 'service_type',
                'label' => 'Service Type',
                'format' => 'raw',
                'value' => fn($model) => $model->getServiceTypeOptionBadges(),
            ],
        ];

        echo DetailView::widget([
            'model' => $model->order,
            'attributes' => $gridColumnOrders,
            'options' => ['class' => 'table table-striped table-bordered table-hover'],
        ]);
        ?>
    </div>
</div>




</div>
<?php
use yii\helpers\Html;

$homeVisitorAssign = \app\modules\admin\models\HomeVisitorsHasOrders::find()->where(['order_id' => $model->id])->one();
$comboOrders = \app\modules\admin\models\ComboOrder::find()->where(['order_id' => $model->id])->all();
?>

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        background: #fff;
        color: #212529;
    }

    .invoice-container {
        padding: 30px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    .invoice-header {
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 25px;
    }

    .invoice-header h2 {
        margin: 0;
        color: #007bff;
    }

    .section-title {
        font-size: 16px;
        font-weight: bold;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 10px;
        padding-bottom: 3px;
        color: #343a40;
    }

    .highlight-box {
        background-color: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #007bff;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
    }

    thead th {
        background-color: #f1f3f5;
        color: #495057;
        font-weight: bold;
        border: 1px solid #dee2e6;
        padding: 8px;
    }

    tbody td {
        border: 1px solid #dee2e6;
        padding: 8px;
        vertical-align: top;
    }

    .text-right {
        text-align: right;
    }

    .total-row {
        font-weight: bold;
        background-color: #e9ecef;
    }

    .summary-table td:first-child {
        font-weight: bold;
    }
</style>

<div class="invoice-container">

    <!-- Header -->
    <div class="invoice-header">
        <h2><?= Html::encode(Yii::$app->name); ?> - Invoice</h2>
        <small>Order Date: <?= $model->created_on; ?></small>
    </div>

    <!-- From / To / Invoice Info -->
    <div class="row">
        <div class="col-4">
            <div class="section-title">From</div>
            <p>
                <strong><?= $model->vendorDetails->business_name; ?></strong><br>
                <?= $model->vendorDetails->address; ?><br>
                <?= $model->vendorDetails->user->contact_no; ?>
            </p>
        </div>
        <div class="col-4">
            <div class="section-title">To</div>
            <p>
                <strong><?= $model->user->username; ?></strong><br>
                <?= $model->deliveryAdd->address ?? ''; ?><br>
                <?= $model->deliveryAdd->location ?? ''; ?>
            </p>
        </div>
        <div class="col-4">
            <div class="section-title">Invoice Details</div>
            <p>
                Invoice #: <?= $model->id; ?><br>
                Payment Type: <?= strip_tags($model->getPaymentTypeOptionBadges()); ?><br>
                Payment Status: <?= strip_tags($model->getPaymentStatusOptionBadges()); ?><br>
                Order Status: <?= strip_tags($model->getStateOptionsBadges()); ?><br>
                Service Type: <?= strip_tags($model->getServiceTypeOptionBadges()); ?><br>
                Service Date: <?= $model->schedule_date; ?><br>
                Address: <?= $model->service_type == $model::HOME_VISIT
                    ? ($model->deliveryAddress->address ?? '')
                    : ($model->vendorDetails->address ?? '') ?>
            </p>
        </div>
    </div>

    <!-- OTP -->
    <?php if (!empty($model->otp)) : ?>
        <div class="highlight-box">
            <strong>OTP:</strong> <?= $model->otp; ?>
        </div>
    <?php endif; ?>

    <!-- Services Ordered -->
    <div class="section-title">Services Ordered</div>
    <table>
        <thead>
            <tr>
                <th>Qty</th>
                <th>Service</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($providerOrderDetails->models as $item): ?>
                <tr>
                    <td><?= $item->qty; ?></td>
                    <td><?= Html::encode($item->service->service_name ?? ''); ?></td>
                    <td class="text-right"><?= $item->price; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pricing Summary -->
    <div class="section-title">Pricing Summary</div>
    <table>
        <tbody class="summary-table">
            <tr><td>Subtotal</td><td class="text-right"><?= $model->sub_total; ?></td></tr>
            <tr><td>Tax</td><td class="text-right"><?= $model->tax; ?></td></tr>
            <tr><td>Tip</td><td class="text-right"><?= $model->tip_amt; ?></td></tr>
            <tr><td>Processing Fees</td><td class="text-right"><?= $model->processing_charges; ?></td></tr>
            <tr><td>Service Charge</td><td class="text-right"><?= $model->service_charge; ?></td></tr>
            <tr><td>Coupon Discount</td><td class="text-right"><?= $model->voucher_amount; ?></td></tr>
            <tr class="total-row"><td>Grand Total</td><td class="text-right"><?= $model->total_w_tax; ?></td></tr>
            <?php if (!empty($orderCancelled)) : ?>
                <tr><td>Cancellation Charges</td><td class="text-right"><?= $orderCancelled->cancellation_charges; ?></td></tr>
                <tr class="total-row"><td>Refund Amount</td><td class="text-right"><?= $model->total_w_tax - $orderCancelled->cancellation_charges; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Home Visitor -->
    <?php if ($model->service_type == $model::TRANS_TYPE_HOME_VISIT && $homeVisitorAssign): ?>
        <div class="section-title">Assigned Home Visitor</div>
        <table>
            <tr><th>Name</th><td><?= $homeVisitorAssign->homeVisitor->full_name ?? ''; ?></td></tr>
            <tr><th>Mobile</th><td><?= $homeVisitorAssign->homeVisitor->mobile_no ?? ''; ?></td></tr>
            <tr><th>Email</th><td><?= $homeVisitorAssign->homeVisitor->email ?? ''; ?></td></tr>
            <tr><th>Gender</th><td><?= $homeVisitorAssign->homeVisitor->gender == 'M' ? 'Male' : 'Female'; ?></td></tr>
            <tr><th>DOB</th><td><?= Yii::$app->formatter->asDate($homeVisitorAssign->homeVisitor->dob); ?></td></tr>
            <tr><th>Role</th><td><?= $homeVisitorAssign->homeVisitor->role ?? ''; ?></td></tr>
        </table>
    <?php endif; ?>

    <!-- Combo Orders -->
    <?php if (!empty($comboOrders)) : ?>
        <div class="section-title">Combo Orders</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Vendor ID</th>
                    <th>Package ID</th>
                    <th>Status</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comboOrders as $index => $combo): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= $combo->order_id; ?></td>
                        <td><?= $combo->vendor_details_id; ?></td>
                        <td><?= $combo->combo_package_id; ?></td>
                        <td><?= $combo->status; ?></td>
                        <td class="text-right"><?= $combo->amount; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php

use yii\helpers\Html;

/** @var array $order */
?>

<!DOCTYPE html>
<html>

<head>
    <title>Invoice #<?= Html::encode($order['id']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
        }

        .items-table th {
            background-color: #f2f2f2;
        }

        .totals {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="invoice-header">
        <h1>Invoice #<?= Html::encode($order['id']) ?></h1>
    </div>

    <div class="invoice-details">
        <p><strong>Customer:</strong> <?= Html::encode($order['customer_details']['first_name'] ?? '') ?> <?= Html::encode($order['customer_details']['last_name'] ?? '') ?></p>
        <p><strong>Order Date:</strong> <?= Html::encode($order['completed_on']) ?></p>
        <p><strong>Payment Status:</strong> <?= Html::encode($order['payment_status']) ?></p>
        <p><strong>Amount Paid:</strong> Rs.<?= Html::encode(number_format($order['total_with_tax'], 2)) ?></p>
        <p><strong>Amount Pending:</strong> Rs.0</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Sub Total</th>
                <th>Tax (%)</th>
                <th>Tax Amount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['productOrderItems'] as $item): ?>
                <tr>
                    <td><?= Html::encode($item['product_id']) ?></td>
                    <td><?= Html::encode($item['quantity']) ?> <?= Html::encode($item['units']) ?></td>
                    <td>$<?= Html::encode(number_format($item['selling_price'], 2)) ?></td>
                    <td>$<?= Html::encode(number_format($item['sub_total'], 2)) ?></td>
                    <td><?= Html::encode($item['tax_percentage']) ?>%</td>
                    <td>$<?= Html::encode(number_format($item['tax_amount'], 2)) ?></td>
                    <td>$<?= Html::encode(number_format($item['total_with_tax'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Sub Total:</strong> $<?= Html::encode(number_format($order['sub_total'], 2)) ?></p>
        <p><strong>Tax Amount:</strong> $<?= Html::encode(number_format($order['tax_amount'], 2)) ?></p>
        <p><strong>Total with Tax:</strong> $<?= Html::encode(number_format($order['total_with_tax'], 2)) ?></p>
    </div>
</body>

</html>







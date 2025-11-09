<?php

use yii\helpers\Url;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            vertical-align: top;
            padding: 0 10px;
        }
        .company-info {
            text-align: right;
            line-height: 1.6;
        }
        .company-info strong {
            font-size: 14px;
            color: #1a1a1a;
        }
        .logo {
            width: 140px;
            height: auto;
            margin-bottom: 10px;
        }
        .brand-tagline {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .no-border td {
            border: none;
            padding: 5px 10px;
            vertical-align: top;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .details-table th {
            background-color: #f4f4f4;
            font-weight: bold;
            color: #333;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount-table td {
            border: none;
            padding: 5px 10px;
        }
        .amount-table .label {
            font-weight: bold;
            color: #333;
        }
        .amount-table .total {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .amount-in-words {
            margin: 20px 0;
            font-size: 12px;
            color: #333;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #777;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="title">Tax Invoice</div>
        <table class="header-table">
            <tr>
                <td>
                    <img src="<?= Url::base().'/web/logo.png' ?>" class="logo" alt="Esteticanow Logo"><br>
                    <strong>esteticanow</strong><br>
                    <span class="brand-tagline">YOUR CONFIDENCE PARTNER</span>
                </td>
                <td class="company-info">
                    <strong>SAS ESTETICA SOLUTIONS PRIVATE LIMITED</strong><br>
                    CIN: U63990TS2024PTC190523<br>
                    Reg Office: D.No 2-86/2/G5, Koppula Towers,<br>
                    Peerzadiguda, Uppal, Medipalli,<br>
                    Ghatkesar, Hyderabad, Telangana, 500098<br>
                    GSTIN: 36ABOCS5671F1ZW<br>
                    MSME Reg No: UDYAM-TS-20-0125500
                </td>
            </tr>
        </table>

        <table class="no-border" style="margin-top: 25px;">
            <tr>
                <td>
                    <strong>Billing & Shipping Address</strong><br>
                    <?= $order['user_details']['full_name'] ?? '' ?><br>
                    <?= $order['user_details']['address'] ?? '' ?><br>
                    <?= $order['user_details']['mobile'] ?? '' ?><br>
                    GSTIN: <?= $order['user_details']['gst'] ?? '-' ?>
                </td>
                <td>
                    <strong>Date:</strong> <?= date('d-m-Y', strtotime($order['schedule_date'])) ?><br>
                    <strong>Tax Invoice No:</strong> <?= $order['id'] ?><br>
                    <strong>Due Date:</strong> -<br>
                    <strong>Reference No:</strong> -<br>
                    <strong>Other Terms:</strong> -
                </td>
            </tr>
        </table>

        <table class="details-table" style="margin-top: 25px;">
            <thead>
                <tr>
                    <th class="text-center">S.No</th>
                    <th>Particulars</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order->orderDetails)): ?>
                    <?php $i = 1; ?>
                    <?php foreach ($order->orderDetails as  $item): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                        <?php if($item->is_package_service!=1): ?>
                            <td><?= $item->service->service_name ?? 'Service' ?></td>
                            <td class="text-center"><?= $item->qty ?? 1 ?></td>
                            <td class="text-right">₹<?= number_format($item->service->price ?? 0, 2) ?></td>
                            <?php endif;  ?>

                            <?php if (!empty($order->comboOrders)): ?>

                                <?php 
                                    foreach($order->comboOrders as $comboOrder){
                                        
                                    }
                                     
                                    
                                    ?>


                            <?php endif;  ?>





                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="amount-table" style="margin-top: 15px;">
            <tr>
                <td class="label text-right">Sub Total</td>
                <td class="text-right">₹<?= number_format($order['sub_total'], 2) ?></td>
            </tr>
            <tr>
                <td class="label text-right">CGST @<?= $order->cgst ?>%</td>
                <td class="text-right">₹<?= number_format($order['Subtotal_tax']/2, 2) ?></td>
            </tr>
            <tr>
                <td class="label text-right">SGST @<?= $order->sgst ?>%</td>
                <td class="text-right">₹<?= number_format($order['Subtotal_tax']/2, 2) ?></td>
            </tr>
        
            <tr>
                <td class="label text-right">Total Tax</td>
                <td class="text-right">₹<?= number_format($order['Subtotal_tax'], 2) ?></td>
            </tr>   
            <tr>
                <td class="label text-right">Service Charge</td>
                <td class="text-right">₹<?= number_format($order['service_charge_w_tax'], 2) ?></td>
            </tr>
            <tr class="total">
                <td class="label text-right">Invoice Value</td>
                <td class="text-right">₹<?= number_format($order['total_w_tax'], 2) ?></td>
            </tr>
        </table>

        <p class="amount-in-words"><strong>Amount in Words:</strong> <?= Yii::$app->formatter->asSpellout($order['payable_amount']) ?> rupees only</p>

        <div class="footer">
            This is a computer-generated document and does not require any signature.
        </div>
    </div>
</body>
</html>
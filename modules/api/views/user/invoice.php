<!DOCTYPE html>
<html>

<head>
    <style>
        /* Define your CSS styles here */
        body {
            font-family: Arial, sans-serif;
        }

        .invoice {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
        }

        .logo {
            max-width: 150px;
        }

        .invoice-details {
            margin-top: 20px;
        }

        .item-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            text-align: center;
        }


        .item-list td {

            text-align: center;
        }

        .item-list th,
        .item-list td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .total {
            margin-top: 20px;
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="header">
            <img src="https://ik.imagekit.io/asbgbgese/Group_1000004746.png?updatedAt=1682576249866" alt="Company Logo" class="logo">
            <h1>Invoice</h1>
            <p>Order Number: <?= $data->id ?></p>
            <p>Expiry Date: <?= isset($data->course->validity) ? $data->course->validity : $data->course->validity ?></p>
        </div>
        <div class="invoice-details">
            <p>Issued to: <?= isset($data->user->full_name) ? $data->user->full_name . ' ' . $data->user->last_name : $data->user->username ?></p>
            <p>Invoice Date: <?= isset($data->updated_on) ? $data->updated_on  :  $data->updated_on ?></p>
        </div>
        <table class="item-list">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= isset($data->course->name) ? $data->course->name : "" ?></td>
                    <td>₹<?= $data->price ?></td>
                    <td>₹<?= $data->price ?></td>
                </tr>
                <!-- Add more items here -->
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for choosing COP Education.</p>
        </div>
    </div>
</body>

</html>
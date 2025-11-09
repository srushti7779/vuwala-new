<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        font-family: 'Raleway', sans-serif;
        background-color: #f4f4f4;
    }

    .section-title {
        font-family: 'Raleway', sans-serif;
        font-weight: 600;
        font-size: 1.8rem;
        color: #bd995e;
        border-bottom: 3px solid #bd995e;
        padding-bottom: 10px;
    }

    .summary-card {
        height: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 15px;
        border-radius: 15px;
        color: #fff;
        transition: transform 0.3s, box-shadow 0.3s;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .summary-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .summary-card i {
        font-size: 2rem;
        margin-right: 20px;
        padding: 5px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .summary-card .card-info h4 {
        font-size: 1rem;
        font-weight: 800;
        margin: 10px;
    }

    .summary-card .card-info p {
        font-size: 1.3rem;
        font-weight: bold;
        margin: 5px 0 0;
    }

    @media (max-width: 768px) {
        .summary-card {
            flex-direction: column;
            text-align: center;
        }

        .summary-card i {
            margin-bottom: 10px;
        }
    }

    .beautiful-heading {
        font-size: 2.5rem;
        font-weight: 700;
        color: #bd995e; /* Soft Gold - elegant, light, and premium */
        font-family: 'Playfair Display', serif;
        letter-spacing: 0.5px;
    }

    .beautiful-heading-latest {
        background: linear-gradient(to right, #4A00E0, #8E2DE2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
        font-size: 1.7rem;
        position: relative;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .beautiful-heading-latest::after {
        content: '';
        display: block;
        width: 60%;
        height: 3px;
        margin: 6px auto 0;
        background: linear-gradient(to right, #8E2DE2, #4A00E0);
        border-radius: 2px;
    }

    .metric-card {
        border-radius: 20px;
        transition: transform 0.3s ease;
        border: none;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .metric-card h5 {
        font-weight: 600;
        color: #4a4a4a;
    }

    canvas {
        max-height: 300px;
    }

    .chart-section {
        padding-bottom: 50px;
    }
</style>

</head>

<body>
   <div class="container py-2">
    <!-- Header Section: Summary Cards -->
   <h1 class="text-center mt-4 section-title beautiful-heading">Admin Earnings Overview</h1>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="summary-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <i class="fas fa-money-bill-wave"></i>
                <div class="card-info">
                    <h4>Daily Earnings</h4>
                    <p>₹ <?= round($data['admin_commission_day'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-calendar-week"></i>
                <div class="card-info">
                    <h4>Weekly Earnings</h4>
                    <p>₹ <?= round($data['admin_commission_week'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-calendar-month"></i>
                <div class="card-info">
                    <h4>Monthly Earnings</h4>
                    <p>₹ <?= round($data['admin_commission_month'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Orders Overview Section -->
<h1 class="text-center mt-5 mb-4 section-title">Orders Overview</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(161, 11, 129), #00223E);">
            <i class="fas fa-box"></i>
            <div class="card-info">
                <h4>New Orders</h4>
                <p><?= $data['new_orders'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #FDC830, #F37335);">
            <i class="fas fa-check-circle"></i>
            <div class="card-info">
                <h4>Accepted Orders</h4>
                <p><?= $data['accepted_orders'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #43cea2, #185a9d);">
            <i class="fas fa-spinner fa-spin"></i>
            <div class="card-info">
                <h4>Ongoing Orders</h4>
                <p><?= $data['ongoing_orders'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(91, 133, 12),rgb(16, 112, 176));">
            <i class="fas fa-check-double"></i>
            <div class="card-info">
                <h4>Completed Orders</h4>
                <p><?= $data['completed_orders'] ?></p>
            </div>
        </div>
    </div>
</div>
<!-- Orders Overview Section end-->
 <!-- Shops and Businesses Overview Section -->
<h1 class="text-center mt-5 mb-4 section-title">Shops and Businesses Overview</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(16, 53, 132),rgb(112, 177, 26));">
            <i class="fas fa-store"></i>
            <div class="card-info">
                <h4>Total Shops</h4>
                <p><?= $data['total_shops'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(55, 15, 71),rgb(2, 63, 153), #2C5364);">
            <i class="fas fa-spa"></i>
            <div class="card-info">
                <h4>Total Spa</h4>
                <p><?= $data['total_spa'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgba(235, 34, 114, 0.4),rgb(161, 33, 99));">
            <i class="fas fa-clinic-medical"></i>
            <div class="card-info">
                <h4>Total Skin Clinic</h4>
                <p><?= $data['total_skin'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(115, 68, 132),rgb(131, 75, 169));">
            <i class="fas fa-cut"></i>
            <div class="card-info">
                <h4>Total Saloon</h4>
                <p><?= $data['total_saloon'] ?></p>
            </div>
        </div>
    </div>
</div>
<!---Total (Users, Staff & Vendors Overview Section-->
<h1 class="text-center mt-5 mb-4 section-title">Total (Users, Staff & Vendors Overview)</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(5, 66, 95),rgb(18, 77, 155));">
            <i class="fas fa-store"></i>
            <div class="card-info">
                <h4>Total Vendors</h4>
                <p><?= $data['total_vendors'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(97, 130, 48),rgb(19, 23, 100));">
            <i class="fas fa-user-tie"></i>
            <div class="card-info">
                <h4>Total Employees</h4>
                <p><?= $data['total_staff_home'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(24, 2, 66),rgb(109, 1, 82));">
            <i class="fas fa-users"></i>
            <div class="card-info">
                <h4>Total Users</h4>
                <p><?= $data['total_users'] ?></p>
            </div>
        </div>
    </div>
</div>
<!---Total (Users, Staff & Vendors Overview Section End-->

<!--In Detail Overview Section-->
<h1 class="text-center mt-5 mb-4 section-title">In Detail Overview</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(5, 138, 56),rgb(32, 3, 78));">
            <i class="fas fa-check-circle"></i>
            <div class="card-info">
                <h4>Active Vendors</h4>
                <p><?= $data['active_vendors'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(9, 154, 36),rgb(40, 145, 220));">
            <i class="fas fa-hourglass-half"></i>
            <div class="card-info">
                <h4>Pending Vendors</h4>
                <p><?= $data['pending_onboarding'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(14, 3, 67),rgb(5, 67, 149));">
            <i class="fas fa-users-cog"></i>
            <div class="card-info">
                <h4>No. of Staff</h4>
                <p><?= htmlspecialchars($data['total_staff_home']) ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>
<!--In Detail Overview Section End-->
<!-- No of Home Visitors Section -->
<h1 class="text-center mt-5 mb-4 section-title">No of Home Visitors Overview</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg,rgb(48, 4, 94),rgb(64, 47, 164));">
            <i class="fas fa-user-check"></i>
            <div class="card-info">
                <h4>No of Home Visitors</h4>
                <p><?= htmlspecialchars($data['total_homevisitors']) ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #FF4E50, #F9D423);">
            <i class="fas fa-user-check"></i>
            <div class="card-info">
                <h4>Users</h4>
                <p><?= $data['only_users'] ?></p>
            </div>
        </div>
    </div>
</div>
<!---Subscriptions Overview Section -->
<h1 class="text-center mt-5 mb-4 section-title">Subscriptions Overview</h1>
<div class="row row-cols-1 row-cols-md-3 g-4 text-center">
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #7b4397, #dc2430);">
            <i class="fas fa-boxes"></i>
            <div class="card-info">
                <h4>Total Subscriptions</h4>
                <p><?= $data['total_subscriptions'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
            <i class="fas fa-clipboard-list"></i>
            <div class="card-info">
                <h4>Basic Subscriptions</h4>
                <p><?= $data['basic_subscriptions'] ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="summary-card" style="background: linear-gradient(135deg, #e65c00, #F9D423);">
            <i class="fas fa-star"></i>
            <div class="card-info">
                <h4>Premium Subscriptions</h4>
                <p><?= $data['premium_subscriptions'] ?></p>
            </div>
        </div>
    </div>
</div>
<!---- Graphical Metrics Overview Section -->
<h1 class="text-center mt-5 mb-4 section-title">Graphical Metrics Overview</h1>

<div class="row row-cols-1 row-cols-md-3 g-4 text-center chart-section">
    <div class="col d-flex">
        <div class="card p-4 shadow-sm metric-card w-100 d-flex flex-column justify-content-between">
       <h4 class="beautiful-heading-latest">Earnings Trend</h4>

            <canvas id="earningsChart"></canvas>
        </div>
    </div>
    <div class="col d-flex">
        <div class="card p-4 shadow-sm metric-card w-100 d-flex flex-column justify-content-between">
            <h4 class="beautiful-heading-latest">Order Status Distribution</h4>
            <canvas id="orderChart"></canvas>
        </div>
    </div>
    <div class="col d-flex">
        <div class="card p-4 shadow-sm metric-card w-100 d-flex flex-column justify-content-between">
            <h4 class="beautiful-heading-latest">Vendor Onboarding Status</h4>
            <canvas id="vendorOnboardingChart"></canvas>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 g-4 text-center chart-section">
    <div class="col d-flex">
        <div class="card p-4 shadow-sm metric-card w-100 d-flex flex-column justify-content-between">
            <h4 class="beautiful-heading-latest">Geographical Insights</h4>
            <canvas id="geoChart"></canvas>
        </div>
    </div>
    <div class="col d-flex">
        <div class="card p-4 shadow-sm metric-card w-100 d-flex flex-column justify-content-between">
            <h4 class="beautiful-heading-latest">Shop Types Insights</h4>
            <canvas id="shopChart"></canvas>
        </div>
    </div>
</div>
<!-- Chart.js Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data from PHP
    const orderData = [<?= $data['new_orders'] ?>, <?= $data['accepted_orders'] ?>, <?= $data['ongoing_orders'] ?>, <?= $data['completed_orders'] ?>];
    const vendorData = [<?= $data['active_vendors'] ?>, <?= $data['pending_onboarding'] ?>];
    const shopLabels = <?= json_encode(['Saloon', 'Spa', 'Skin Clinics']) ?>;
    const shopData = <?= json_encode([$data['total_saloon'], $data['total_spa'], $data['total_skin']]) ?>;
    const earningsData = <?= json_encode([$data['admin_commission_day'], $data['admin_commission_week'], $data['admin_commission_month']]) ?>;

    // Chart: Order Status
    new Chart(document.getElementById('orderChart'), {
        type: 'polarArea',
        data: {
            labels: ['New', 'Accepted', 'Ongoing', 'Completed'],
            datasets: [{
                data: orderData,
                backgroundColor: ['#FFB3BA', '#FFEB99', '#B5EAD7', '#FF9E6D']
            }]
        },
        options: { responsive: true }
    });

    // Chart: Vendor Onboarding
    new Chart(document.getElementById('vendorOnboardingChart'), {
        type: 'polarArea',
        data: {
            labels: ['Active', 'Pending'],
            datasets: [{
                data: vendorData,
                backgroundColor: ['#89CFF0', '#FF5C8D']
            }]
        },
        options: { responsive: true }
    });

    // Chart: Shop Types
    new Chart(document.getElementById('shopChart'), {
        type: 'polarArea',
        data: {
            labels: shopLabels,
            datasets: [{
                data: shopData,
                backgroundColor: ['#66C2FF', '#FFDFBA', '#E0A9FF']
            }]
        },
        options: { responsive: true }
    });

    // Chart: Geo Insights (Static Example)
    new Chart(document.getElementById('geoChart'), {
        type: 'pie',
        data: {
            labels: ['North', 'South', 'East', 'West'],
            datasets: [{
                data: [15, 25, 20, 40],
                backgroundColor: ['#FFD6E8', '#B5EAD7', '#C9C9FF', '#FFDAC1']
            }]
        },
        options: { responsive: true }
    });

    // Chart: Earnings Trend
    new Chart(document.getElementById('earningsChart'), {
        type: 'bar',
        data: {
            labels: ['Daily', 'Weekly', 'Monthly'],
            datasets: [{
                label: 'Earnings (₹)',
                data: earningsData,
                backgroundColor: ['#66C2FF', '#FFEB99', '#FF5C8D']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>





</body>

</html>

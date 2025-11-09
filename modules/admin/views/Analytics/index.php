<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Base Card Styles */
        /* Base Card Styles */
        .summary-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            /* Reduced padding */
            border-radius: 12px;
            /* Slightly smaller corners */
            color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            /* Lighter shadow */
            transition: all 0.3s ease;
            margin-bottom: 15px;
            /* Reduced margin */
            position: relative;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            /* Smaller hover effect */
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            /* Reduced shadow on hover */
        }

        .summary-card i {
            font-size: 2rem;
            /* Reduced icon size */
            margin-right: 15px;
            /* Adjusted margin */
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px;
            /* Adjusted padding */
            border-radius: 50%;
            color: white;
        }

        .summary-card .card-info {
            flex-grow: 1;
            text-align: left;
        }

        .summary-card h4 {
            font-size: 1rem;
            /* Reduced title font size */
            font-weight: 600;
            margin-bottom: 4px;
            /* Adjusted margin */
        }

        .summary-card p {
            font-size: 1.2rem;
            /* Reduced number font size */
            font-weight: 700;
        }

        /* General Adjustments for Rows */
        .row .col-md-3 {
            margin-bottom: 10px;
            /* Reduced spacing between cards */
        }


        /* Gradient Backgrounds for Cards */
        .daily-earnings {
            background: linear-gradient(135deg, #89CFF0, #66C2FF);
        }

        .weekly-earnings {
            background: linear-gradient(135deg, #FFEB99, #FFE84D);
        }

        .monthly-earnings {
            background: linear-gradient(135deg, #FFB3BA, #FF5C8D);
        }

        .total-customers {
            background: linear-gradient(135deg, #B5EAD7, #64D9D9);
        }

        .new-orders {
            background: linear-gradient(135deg, #FFDFBA, #FF9E6D);
        }

        .pending-orders {
            background: linear-gradient(135deg, #FFE156, #FFAB4D);
        }

        .ongoing-orders {
            background: linear-gradient(135deg, #A7D8B8, #6DD5ED);
        }

        .completed-orders {
            background: linear-gradient(135deg, #E0A9FF, #BB8BFF);
        }

        .chart-container {
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Header Section: Summary Cards -->
        <h2 class="text-center mb-4">Earnings Overview</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="summary-card daily-earnings">
                    <i class="fas fa-wallet"></i>
                    <div class="card-info">
                        <h4>Daily Earnings</h4>
                        <p>₹ <?= $data['admin_commission_day'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card weekly-earnings">
                    <i class="fas fa-calendar-week"></i>
                    <div class="card-info">
                        <h4>Weekly Earnings</h4>
                        <p>₹ <?= $data['admin_commission_week'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card monthly-earnings">
                    <i class="fas fa-calendar-month"></i>
                    <div class="card-info">
                        <h4>Monthly Earnings</h4>
                        <p>₹ <?= $data['admin_commission_month'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Overview Section -->
        <h2 class="text-center mt-5 mb-4">Orders Overview</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="summary-card new-orders">
                    <i class="fas fa-cart-plus"></i>
                    <div class="card-info">
                        <h4>New Orders</h4>
                        <p><?= $data['new_orders'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card pending-orders">
                    <i class="fas fa-clock"></i>
                    <div class="card-info">
                        <h4>Accepted Orders</h4>
                        <p><?= $data['accepted_orders'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card ongoing-orders">
                    <i class="fas fa-sync-alt"></i>
                    <div class="card-info">
                        <h4>Ongoing Orders</h4>
                        <p><?= $data['ongoing_orders'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card completed-orders">
                    <i class="fas fa-check-circle"></i>
                    <div class="card-info">
                        <h4>Completed Orders</h4>
                        <p><?= $data['completed_orders'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shops and Businesses Overview Section -->
        <h2 class="text-center mt-5 mb-4">Shops and Businesses Overview</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="summary-card new-orders">
                    <i class="fas fa-cart-plus"></i>
                    <div class="card-info">
                        <h4>Total Shops</h4>
                        <p><?= $data['total_shops'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card pending-orders">
                    <i class="fas fa-clock"></i>
                    <div class="card-info">
                        <h4>Total Spa</h4>
                        <p><?= $data['total_spa'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card ongoing-orders">
                    <i class="fas fa-sync-alt"></i>
                    <div class="card-info">
                        <h4>Total Skin Clinic</h4>
                        <p><?= $data['total_skin'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card completed-orders">
                    <i class="fas fa-check-circle"></i>
                    <div class="card-info">
                        <h4>Total Saloon</h4>
                        <p><?= $data['total_saloon'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users and Vendors Overview Section -->
        <h2 class="text-center mt-5 mb-4">Users and Vendors Overview</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="summary-card total-customers">
                    <i class="fas fa-users"></i>
                    <div class="card-info">
                        <h4>Total Users</h4>
                        <p><?= $data['total_users'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card new-orders">
                    <i class="fas fa-cart-plus"></i>
                    <div class="card-info">
                        <h4>Total Vendors</h4>
                        <p><?= $data['total_vendors'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card pending-orders">
                    <i class="fas fa-clock"></i>
                    <div class="card-info">
                        <h4>Active Vendors</h4>
                        <p><?= $data['active_vendors'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card ongoing-orders">
                    <i class="fas fa-sync-alt"></i>
                    <div class="card-info">
                        <h4>Pending Vendors</h4>
                        <p><?= $data['pending_onboarding'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions Overview Section -->
        <h2 class="text-center mt-5 mb-4">Subscriptions Overview</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="summary-card new-orders">
                    <i class="fas fa-cart-plus"></i>
                    <div class="card-info">
                        <h4>Total Subscriptions</h4>
                        <p><?= $data['total_subscriptions'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Graphical Metrics Section -->
    <div class="row mt-4">
        <div class="col-md-4 chart-container">
            <h5>Earnings Trend</h5>
            <canvas id="earningsChart"></canvas>
        </div>
        <div class="col-md-4 chart-container">
            <h5>Order Status Distribution</h5>
            <canvas id="orderChart"></canvas>
        </div>
        <div class="col-md-4 chart-container">
            <h5>Vendor Onboarding Status</h5>
            <canvas id="vendorOnboardingChart"></canvas>
        </div>
    </div>

    <div class="row mt-4">

        <div class="col-md-4 chart-container">
            <h5>Geographical Insights</h5>
            <canvas id="geoChart"></canvas>
        </div>

        <div class="col-md-4 chart-container">
            <h5>Shop Types Insights</h5>
            <canvas id="shopChart"></canvas>
        </div>
    </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
  
        var newOrders = <?= $data['new_orders'] ?>;
        var acceptedOrders = <?= $data['accepted_orders'] ?>;
        var ongoingOrders = <?= $data['ongoing_orders'] ?>;
        var completedOrders = <?= $data['completed_orders'] ?>;

        // Order Status Distribution Chart
        new Chart(document.getElementById('orderChart'), {
            type: 'polarArea',
            data: {
                labels: ['New Orders', 'Pending Orders', 'Ongoing Orders', 'Completed Orders'],
                datasets: [{
                    data: [newOrders, acceptedOrders, ongoingOrders, completedOrders],
                    backgroundColor: ['#FFDFBA', '#FFE156', '#A7D8B8', '#E0A9FF'],
                }]
            },
            options: {
                responsive: true
            }
        });

        var activeVendors = <?= $data['active_vendors'] ?>;
        var pendingOnboarding = <?= $data['pending_onboarding'] ?>; 

        // Vendor Onboarding Status Chart
        new Chart(document.getElementById('vendorOnboardingChart'), {
            type: 'polarArea',
            data: {
                labels: ['Active Vendors', 'Pending Onboarding'],
                datasets: [{
                    data: [activeVendors, pendingOnboarding],
                    backgroundColor: ['#B5EAD7', '#FF9E6D'],
                }]
            },
            options: {
                responsive: true
            }
        });

        // Geographical Insights Chart
        new Chart(document.getElementById('geoChart'), {
            type: 'pie',
            data: {
                labels: ['North', 'South', 'East', 'West'], // Example regions
                datasets: [{
                    data: [15, 25, 20, 40], // Example data
                    backgroundColor: ['#89CFF0', '#FF9E6D', '#B5EAD7', '#FF5C8D'],
                }]
            },
            options: {
                responsive: true
            }
        });


        // Shops Insights Chart
        // new Chart(document.getElementById('shopChart'), {
        //     type: 'polarArea',
        //     data: {
        //         labels: ['Saloon', 'Spa', 'Skin Clinics'],
        //         datasets: [{
        //             data: [200, 150, 100],
        //             backgroundColor: ['#FFB3BA', '#FFEB99', '#FF9E6D', '#B5EAD7'],
        //         }]
        //     },
        //     options: {
        //         responsive: true
        //     }
        // });
    </script>
    <?php
    // Prepare data for the chart
    $labels = ['Saloon', 'Spa', 'Skin Clinics'];
    $dataset = [
        $data['total_saloon'],
        $data['total_spa'],
        $data['total_skin']
    ];
    ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <canvas id="shopChart" width="400" height="400"></canvas>
    <script>
        // Prepare dynamic data for the chart
        const shopLabels = <?php echo json_encode($labels); ?>;
        const shopData = <?php echo json_encode($dataset); ?>;

        // Initialize the polar area chart
        new Chart(document.getElementById('shopChart'), {
            type: 'polarArea',
            data: {
                labels: shopLabels,
                datasets: [{
                    data: shopData,
                    backgroundColor: ['#FFB3BA', '#FFEB99', '#FF9E6D', '#B5EAD7'],
                }]
            },
            options: {
                responsive: true
            }
        }); 
    </script>

<?php
// Prepare dynamic earnings data
$earnings = [
    $data['admin_commission_day'],  // Daily earnings
    $data['admin_commission_week'], // Weekly earnings
    $data['admin_commission_month'] // Monthly earnings
];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<canvas id="earningsChart" width="400" height="400"></canvas>
<script>
    // Prepare dynamic earnings data for the chart
    const earningsData = <?php echo json_encode($earnings); ?>;

    // Initialize the bar chart
    new Chart(document.getElementById('earningsChart'), {
        type: 'bar',
        data: {
            labels: ['Daily', 'Weekly', 'Monthly'],
            datasets: [{
                label: 'Earnings (₹)',
                data: earningsData,  // Dynamic data here
                backgroundColor: ['#66C2FF', '#FFEB99', '#FF5C8D'], 
                borderColor: '#66C2FF',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true
        }
    });
</script>


</body>

</html>
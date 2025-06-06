<?php
$page_title = 'Daily Sales';
require_once('includes/load.php');
page_require_level(3);

$year  = date('Y');
$month = date('m');
$sales = dailySales($year, $month);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --danger: #f72585;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 100;
            left: 0;
            top: 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--gray);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 30px;
            transition: all 0.3s;
            width: calc(100% - 240px);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .header h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 600;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
            margin-left: 15px;
        }
        
        /* Sales Chart Section */
        .chart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            font-size: 18px;
            color: var(--dark);
        }
        
        /* Total Sales Display */
        .total-sales {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            margin-top: 20px;
        }
        
        .total-sales h3 {
            color: var(--gray);
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .total-sales h2 {
            color: var(--primary);
            font-size: 28px;
            font-weight: 600;
        }
        
        /* Search Bar Styles */
        .search-container {
            position: relative;
            width: 250px;
            margin-left: auto;
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        /* Category Navigation */
        .category-nav {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 15px 0;
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .category-nav::-webkit-scrollbar {
            display: none;
        }
        
        .category-nav a {
            display: block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            color: var(--gray);
            border-radius: 20px;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .category-nav a:hover, 
        .category-nav a.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        /* Overlay for mobile menu */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .overlay.active {
            opacity: 0;
            
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 20px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .chart-container {
                height: 300px;
            }
            
            .search-container {
                width: 100%;
                margin: 15px 0;
            }
            
            .main-content {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .total-sales h2 {
                font-size: 24px;
            }
            
            .section-header h2 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Overlay for mobile menu -->
        <div class="overlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="home_staff.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="add_sale.php">
                        <i class="fas fa-cart-plus"></i>
                        <span>Product Bought</span>
                    </a>
                </li>
                <li>
                    <a href="product_staff.php">
                        <i class="fas fa-box-open"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="daily_sales.php" class="active">
                        <i class="fas fa-chart-line"></i>
                        <span>Sales</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Daily Sales Report</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name'] ?? 'Staff'); ?>&background=4361ee&color=fff" alt="User">
                    <span><?php echo $user['name'] ?? 'Staff User'; ?></span>
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <?php echo display_msg($msg); ?>

            <!-- Sales Chart -->
            <div class="chart-section">
                <div class="section-header">
                    <h2>Daily Sales for <?php echo date('F Y'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="total-sales">
                <h3>Total Sales This Month</h3>
                <h2>₱<?php echo number_format(array_sum(array_column($sales, 'total_saleing_price')), 2); ?></h2>
            </div>
        </main>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');
            
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Initialize chart
            const ctx = document.getElementById('dailySalesChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [<?php foreach ($sales as $sale) { echo "'" . $sale['date'] . "',"; } ?>],
                    datasets: [{
                        label: 'Daily Sales (₱)',
                        data: [<?php foreach ($sales as $sale) { echo $sale['total_saleing_price'] . ","; } ?>],
                        borderColor: 'rgba(67, 97, 238, 1)',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Poppins',
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.😎',
                            titleFont: {
                                size: 14,
                                family: 'Poppins'
                            },
                            bodyFont: {
                                size: 12,
                                family: 'Poppins'
                            },
                            padding: 12,
                            cornerRadius: 4,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    family: 'Poppins',
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Sales Amount (₱)',
                                font: {
                                    family: 'Poppins',
                                    size: 12
                                }
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0,0,0,0.05)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                chart.resize();
            });
        });
    </script>
</body>
</html>
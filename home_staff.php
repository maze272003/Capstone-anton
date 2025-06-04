<?php
$page_title = 'Staff Dashboard';
require_once('includes/load.php');

// Permission check
page_require_level(2);

// Initialize variables with default values to prevent undefined variable errors
$c_categorie = array('total' => 0);
$c_product = array('total' => 0);
$c_sale = array('total' => 0);
$c_user = array('total' => 0);
$products_sold = array();
$recent_products = array();

// Safely get dashboard data
try {
    $c_categorie = count_by_id('categories') ?? $c_categorie;
    $c_product = count_by_id('products') ?? $c_product;
    $c_sale = count_by_id('sales') ?? $c_sale;
    $c_user = count_by_id('users') ?? $c_user;
    $products_sold = find_higest_saleing_product('10') ?? $products_sold;
    $recent_products = find_recent_product_added('5') ?? $recent_products;
} catch (Exception $e) {
    // Log error but continue execution
    error_log("Dashboard data error: " . $e->getMessage());
}
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
            transition: all 0.3s ease;
            z-index: 100;
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
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
            padding: 20px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .sidebar.active + .main-content {
            transform: translateX(240px);
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
        
        /* Dashboard Cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: white;
            font-size: 20px;
        }
        
        .card-icon.blue {
            background: linear-gradient(135deg, var(--primary), var(--accent));
        }
        
        .card-icon.green {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
        }
        
        .card-icon.purple {
            background: linear-gradient(135deg, #7209b7, #f72585);
        }
        
        .card h3 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .card h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        /* Chart Section */
        .chart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
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
        
        /* Recent Products Table */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        th {
            background-color: var(--light);
            color: var(--gray);
            font-weight: 500;
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .badge-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        /* Overlay for mobile menu */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .overlay.active {
            opacity: 1;
            visibility: visible;
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
            }
            
            .menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 20px;
            }
            
            .card-container {
                grid-template-columns: 1fr;
            }
            
            .card h2 {
                font-size: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
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
        }
        
        /* Error message styling */
        .error-message {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
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
                    <a href="home_staff.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="add_sale.php">
                        <i class="fas fa-cart-plus"></i>
                        <span>Add New Sale</span>
                    </a>
                </li>
                <li>
                    <a href="product_staff.php">
                        <i class="fas fa-box-open"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="daily_sales.php">
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
                <h1>Dashboard Overview</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name'] ?? 'Staff'); ?>&background=4361ee&color=fff" alt="User">
                    <span><?php echo $user['name'] ?? 'Staff User'; ?></span>
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="card-container">
                <div class="card fade-in delay-1">
                    <div class="card-icon blue">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3>Total Products</h3>
                    <h2><?php echo isset($c_product['total']) ? $c_product['total'] : 0; ?></h2>
                    <?php if(!isset($c_product['total'])): ?>
                        <div class="error-message">Unable to load product data</div>
                    <?php endif; ?>
                    <a href="product_staff.php" style="color: var(--accent); text-decoration: none; font-size: 14px;">
                        View all products <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="card fade-in delay-2">
                    <div class="card-icon green">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3>Total Sales</h3>
                    <h2><?php echo isset($c_sale['total']) ? $c_sale['total'] : 0; ?></h2>
                    <?php if(!isset($c_sale['total'])): ?>
                        <div class="error-message">Unable to load sales data</div>
                    <?php endif; ?>
                    <a href="daily_sales.php" style="color: var(--accent); text-decoration: none; font-size: 14px;">
                        View sales report <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="card fade-in delay-3">
                    <div class="card-icon purple">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3>Product Categories</h3>
                    <h2><?php echo isset($c_categorie['total']) ? $c_categorie['total'] : 0; ?></h2>
                    <?php if(!isset($c_categorie['total'])): ?>
                        <div class="error-message">Unable to load category data</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="chart-section fade-in">
                <div class="section-header">
                    <h2>Top Selling Products</h2>
                </div>
                <?php if(!empty($products_sold)): ?>
                    <div class="chart-container">
                        <canvas id="productSalesChart"></canvas>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--gray);">
                        <i class="fas fa-chart-bar" style="font-size: 50px; opacity: 0.5; margin-bottom: 15px;"></i>
                        <p>No sales data available to display</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Products -->
            <div class="table-container fade-in">
                <div class="section-header">
                    <h2>Recently Added Products</h2>
                    <a href="product_staff.php" style="color: var(--accent); text-decoration: none; font-size: 14px;">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php if(!empty($recent_products)): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_products as $recent_product): ?>
                                <tr>
                                    <td><?php echo remove_junk(first_character($recent_product['name'])); ?></td>
                                    <td><?php echo remove_junk(first_character($recent_product['categorie'])); ?></td>
                                    <td>₱<?php echo (int)$recent_product['sale_price']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--gray);">
                        <i class="fas fa-box-open" style="font-size: 50px; opacity: 0.5; margin-bottom: 15px;"></i>
                        <p>No recent products to display</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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
        
        <?php if(!empty($products_sold)): ?>
            const productLabels = [<?php foreach ($products_sold as $product) { echo "'" . $product['name'] . "',"; } ?>];
            const productQtyData = [<?php foreach ($products_sold as $product) { echo $product['totalSold'] . ","; } ?>];

            // Get chart container and canvas
            const chartContainer = document.querySelector('.chart-container');
            const canvas = document.getElementById('productSalesChart');
            
            // Set fixed dimensions
            chartContainer.style.height = '250px';
            chartContainer.style.width = '100%';
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            
            // Create chart
            const ctx = canvas.getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Units Sold',
                        data: productQtyData,
                        backgroundColor: 'rgba(67, 97, 238, 0.7)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 12
                            },
                            padding: 12,
                            cornerRadius: 4
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                stepSize: 5
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                chart.resize();
            });
        <?php endif; ?>
    </script>
</body>
</html>
<?php
$page_title = 'Admin Dashboard';
require_once('includes/load.php');

// Permission check
page_require_level(1);

// Get current user data
$user = current_user();

// Dashboard data
$c_categorie = count_by_id('categories');
$c_product = count_by_id('products');
$c_sale = count_by_id('sales');
$c_user = count_by_id('users');

// Function to find highest selling products
function find_highest_selling_products($limit = '10', $filter = 'all') {
  global $db;

  $query = "SELECT p.id, p.name, SUM(s.qty) as totalSold, SUM(s.qty * s.price) as totalSales
            FROM sales s
            LEFT JOIN products p ON s.product_id = p.id";

  // Add filter conditions
  if ($filter === 'month') {
      $query .= " WHERE MONTH(s.date) = MONTH(CURRENT_DATE()) AND YEAR(s.date) = YEAR(CURRENT_DATE())";
  } elseif ($filter === 'year') {
      $query .= " WHERE YEAR(s.date) = YEAR(CURRENT_DATE())";
  } elseif ($filter === 'day') {
      $query .= " WHERE DATE(s.date) = CURDATE()";
  }
  // 'all' filter doesn't need any conditions

  $query .= " GROUP BY p.id, p.name
              ORDER BY totalSales DESC
              LIMIT {$limit}";

  $result = $db->query($query);

  if ($result && $db->num_rows($result)) {
      return $result;
  } else {
      return array(); // Return empty array if no results
  }

}


// Get filter for top selling products
$product_filter = isset($_GET['product_filter']) ? $_GET['product_filter'] : 'all';

// Get top selling products with accurate total sales calculation
$products_sold = find_highest_selling_products('10', $product_filter);
$recent_products = find_recent_product_added('5');
$inventory_value = 0;
$products = find_all('products');
foreach ($products as $product) {
    $inventory_value += ($product['quantity'] * $product['buy_price']);
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'year';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$chart_type = isset($_GET['chart_type']) ? $_GET['chart_type'] : 'bar';

// Get sales data based on filter
$sales_data = array();
$labels = array();
$sales_values = array();
$item_sold_values = array();
$chart_title = '';
$total_sales_for_filter = 0;

// Function to create a date range array
function createDateRangeArray($startDate, $endDate) {
    $dates = array();
    $currentDate = $startDate;

    while (strtotime($currentDate) <= strtotime($endDate)) {
        $dates[] = $currentDate;
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    return $dates;
}

// Function for getting sales data by month (Moved from inside the custom filter block)
function get_sales_by_month($year) {
    global $db;
    $sql = "SELECT MONTH(date) AS month, SUM(qty * price) AS total_sales
            FROM sales
            WHERE YEAR(date) = '{$db->escape($year)}'
            GROUP BY MONTH(date)";
    return find_by_sql($sql);
}


if ($filter == 'year') {
    $chart_title = 'Yearly Sales & Items Sold ('.date('Y').')';
    $sales_by_month = get_sales_by_month(date('Y'));
    $items_sold_by_month = get_items_sold_by_month(date('Y'));
    $labels = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $sales_values = array_fill(0, 12, 0);
    $item_sold_values = array_fill(0, 12, 0);
    foreach ($sales_by_month as $sale) {
        $month_index = (int)$sale['month'] - 1;
        $sales_values[$month_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales'];
    }
    foreach ($items_sold_by_month as $item) {
        $month_index = (int)$item['month'] - 1;
        $item_sold_values[$month_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'month') {
    $chart_title = 'Monthly Sales & Items Sold ('.date('F Y').')';
    $sales_by_day = get_sales_by_day(date('Y'), date('m'));
    $items_sold_by_day = get_items_sold_by_day(date('Y'), date('m'));
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
    $labels = range(1, $days_in_month);
    $sales_values = array_fill(0, $days_in_month, 0);
    $item_sold_values = array_fill(0, $days_in_month, 0);
    foreach ($sales_by_day as $sale) {
        $day_index = (int)$sale['day'] - 1;
        $sales_values[$day_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales'];
    }
    foreach ($items_sold_by_day as $item) {
        $day_index = (int)$item['day'] - 1;
        $item_sold_values[$day_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'day') {
    $chart_title = 'Daily Sales & Items Sold ('.date('F j, Y').')';
    $sales_by_hour = get_sales_by_hour(date('Y-m-d'));
    $items_sold_by_hour = get_items_sold_by_hour(date('Y-m-d'));
    $labels = array();
    for ($i = 0; $i < 24; $i++) {
        $labels[] = sprintf("%02d:00", $i);
    }
    $sales_values = array_fill(0, 24, 0);
    $item_sold_values = array_fill(0, 24, 0);
    foreach ($sales_by_hour as $sale) {
        $hour_index = (int)$sale['hour'];
        $sales_values[$hour_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales'];
    }
    foreach ($items_sold_by_hour as $item) {
        $hour_index = (int)$item['hour'];
        $item_sold_values[$hour_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'custom') {
    if (empty($end_date)) {
        $end_date = $start_date;
    }
    $chart_title = 'Sales & Items Sold from '.date('M j, Y', strtotime($start_date)).' to '.date('M j, Y', strtotime($end_date));

    $sales_by_date = get_sales_by_date_range($start_date, $end_date);
    $items_sold_by_date = get_items_sold_by_date_range($start_date, $end_date);
    $date_range = createDateRangeArray($start_date, $end_date);
    $labels = array();
    $sales_values = array();
    $item_sold_values = array();

    foreach ($date_range as $date) {
        $labels[] = date('M j', strtotime($date));
        $sales_values[] = 0;
        $item_sold_values[] = 0;
    }

    foreach ($sales_by_date as $sale) {
        $date = date('Y-m-d', strtotime($sale['date']));
        $index = array_search($date, $date_range);
        if ($index !== false) {
            $sales_values[$index] = (float)$sale['total_sales'];
            $total_sales_for_filter += (float)$sale['total_sales'];
        }
    }
    foreach ($items_sold_by_date as $item) {
        $date = date('Y-m-d', strtotime($item['date']));
        $index = array_search($date, $date_range);
        if ($index !== false) {
            $item_sold_values[$index] = (int)$item['total_qty'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #3a56d4 0%, #2a3eb1 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }

        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 25px;
        }

        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
        }

        .user-profile {
            display: flex;
            align-items: center;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .user-profile .user-info {
            margin-right: 15px;
            text-align: right;
        }

        .user-profile .user-info .name {
            font-weight: 500;
            font-size: 14px;
        }

        .user-profile .user-info .role {
            font-size: 12px;
            color: var(--gray);
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: none;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            color: var(--dark);
        }

        .card-body {
            padding: 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card a {
            text-decoration: none;
            color: inherit;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .stat-icon.users {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .stat-icon.categories {
            background-color: rgba(248, 37, 133, 0.1);
            color: var(--danger);
        }

        .stat-icon.products {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .stat-icon.sales {
            background-color: rgba(72, 149, 239, 0.1);
            color: var(--info);
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }

        .filter-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .filter-form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            align-items: center;
        }

        .filter-group label {
            margin-right: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .date-range-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .table th {
            font-weight: 500;
            color: var(--gray);
            text-transform: uppercase;
            font-size: 12px;
            background-color: #f9f9f9;
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        .text-success {
            color: #52c41a;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #52c41a;
        }

        .alert-danger {
            background-color: #fff1f0;
            border: 1px solid #ffa39e;
            color: #f5222d;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .inventory-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .inventory-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
    <?php include_once('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="name"><?php echo isset($user['name']) ? remove_junk(ucfirst($user['name'])) : 'Guest'; ?></div>
                        <div class="role"><?php echo isset($user['group_name']) ? remove_junk(ucfirst($user['group_name'])) : 'Unknown'; ?></div>
                    </div>
                    <img src="uploads/users/<?php echo isset($user['image']) ? $user['image'] : 'default.jpg'; ?>" alt="User Image">
                </div>
            </div>

            <?php echo display_msg($msg); ?>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <a href="users.php">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?php echo $c_user['total']; ?></div>
                        <div class="stat-label">Total Users</div>
                    </a>
                </div>

                <div class="stat-card">
                    <a href="categorie.php">
                        <div class="stat-icon categories">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-value"><?php echo $c_categorie['total']; ?></div>
                        <div class="stat-label">Categories</div>
                    </a>
                </div>

                <div class="stat-card">
                    <a href="product.php">
                        <div class="stat-icon products">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="stat-value"><?php echo $c_product['total']; ?></div>
                        <div class="stat-label">Products</div>
                    </a>
                </div>

                <div class="stat-card">
                    <a href="sales.php">
                        <div class="stat-icon sales">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-value"><?php echo $c_sale['total']; ?></div>
                        <div class="stat-label">Total Sales</div>
                    </a>
                </div>
            </div>
            
            <!-- Inventory Value -->
            <div class="inventory-card">
                <h3><i class="fas fa-warehouse"></i> Inventory Value</h3>
                <div class="inventory-value">₱<?php echo number_format($inventory_value, 2); ?></div>
            </div>
            
            <!-- Filter Container -->
            <div class="filter-container">
                <form id="filterForm" method="get" action="" class="filter-form">
                    <div class="filter-group">
                        <label for="filter">Filter By:</label>
                        <select name="filter" id="filter" class="filter-select">
                            <option value="year" <?= ($filter == 'year') ? 'selected' : '' ?>>This Year</option>
                            <option value="month" <?= ($filter == 'month') ? 'selected' : '' ?>>This Month</option>
                            <option value="day" <?= ($filter == 'day') ? 'selected' : '' ?>>Today</option>
                            <option value="custom" <?= ($filter == 'custom') ? 'selected' : '' ?>>Custom Date Range</option>
                        </select>
                    </div>
                    
                    <div id="dateRangeSelector" class="date-range-selector" style="<?= ($filter == 'custom') ? 'display: flex;' : 'display: none;' ?>">
                        <label for="start_date">From:</label>
                        <input type="date" name="start_date" id="start_date" class="date-input"
                               value="<?= $start_date ?>" max="<?= date('Y-m-d') ?>">
                        <label for="end_date">To:</label>
                        <input type="date" name="end_date" id="end_date" class="date-input"
                               value="<?= $end_date ?>" max="<?= date('Y-m-d') ?>">
                        <button type="button" id="applyFilter" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>
            
            <!-- Charts Row -->
            <div class="grid-container">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Sales Performance</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Items Sold</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="itemsSoldChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tables Row -->
            <div class="grid-container">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-star"></i> Top Selling Products</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Units Sold</th>
                                        <th>Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $grandTotalSold = 0;
                                $grandTotalSales = 0;
                                
                                foreach ($products_sold as $product): 
                                    $name = isset($product['name']) ? remove_junk(first_character($product['name'])) : 'Unknown Product';
                                    $totalSold = isset($product['totalSold']) ? (int)$product['totalSold'] : 0;
                                    $totalSales = isset($product['totalSales']) ? (float)$product['totalSales'] : 0;
                                    
                                    $grandTotalSold += $totalSold;
                                    $grandTotalSales += $totalSales;
                                ?>
                                    <tr>
                                        <td><?= $name ?></td>
                                        <td><?= $totalSold ?></td>
                                        <td>₱<?= number_format($totalSales, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background-color: #f5f7fb; font-weight: 600;">
                                    <td>Grand Total</td>
                                    <td><?= $grandTotalSold ?></td>
                                    <td>₱<?= number_format($grandTotalSales, 2) ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Recently Added Products</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Sale Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_products as $recent_product): ?>
                                        <tr>
                                            <td><?= remove_junk(first_character($recent_product['name'])) ?></td>
                                            <td><?= remove_junk(first_character($recent_product['categorie'])) ?></td>
                                            <td class="text-success">₱<?= (int)$recent_product['sale_price'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize elements
        const filterForm = document.getElementById('filterForm');
        const filterSelect = document.getElementById('filter');
        const dateRangeSelector = document.getElementById('dateRangeSelector');
        const applyBtn = document.getElementById('applyFilter');
        
        // Set default dates if empty
        if (!document.getElementById('start_date').value) {
            const today = new Date();
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(today.getDate() - 7);
            document.getElementById('start_date').valueAsDate = oneWeekAgo;
            document.getElementById('end_date').valueAsDate = today;
        }
        
        // Toggle date picker visibility
        function toggleDatePicker() {
            dateRangeSelector.style.display = filterSelect.value === 'custom' ? 'flex' : 'none';
        }
        
        // Initialize visibility
        toggleDatePicker();
        
        // Set max date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').max = today;
        document.getElementById('end_date').max = today;
        
        // Ensure end date is not before start date
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            if (startDate > endDateInput.value) {
                endDateInput.value = startDate;
            }
            endDateInput.min = startDate;
        });
        
        // Handle filter change
        filterSelect.addEventListener('change', function() {
            if (this.value !== 'custom') {
                filterForm.submit();
            } else {
                toggleDatePicker();
            }
        });
        
        // Handle apply button click
        applyBtn.addEventListener('click', function() {
            filterForm.submit();
        });
        
        // Initialize charts
        const salesLabels = <?= json_encode($labels) ?>;
        const salesData = <?= json_encode($sales_values) ?>;
        const itemSoldData = <?= json_encode($item_sold_values) ?>;
        const xAxisTitle = <?= json_encode(
            $filter == 'year' ? 'Month' : ($filter == 'month' ? 'Day' : ($filter == 'day' ? 'Hour' : 'Date'))
        ) ?>;
        
        // Sales Chart configuration
        const salesConfig = {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Total Sales (₱)',
                    data: salesData,
                    backgroundColor: 'rgba(67, 97, 238, 0.6)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ₱${context.raw.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Sales (₱)' }
                    },
                    x: {
                        title: { display: true, text: xAxisTitle }
                    }
                }
            }
        };
        
        // Items Sold Chart configuration
        const itemsSoldConfig = {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Total Items Sold',
                    data: itemSoldData,
                    backgroundColor: 'rgba(72, 149, 239, 0.6)',
                    borderColor: 'rgba(72, 149, 239, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.raw} Items`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Items Sold' }
                    },
                    x: {
                        title: { display: true, text: xAxisTitle }
                    }
                }
            }
        };
        
        // Create charts
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const itemsSoldCtx = document.getElementById('itemsSoldChart').getContext('2d');
        
        let salesChart = new Chart(salesCtx, salesConfig);
        let itemsSoldChart = new Chart(itemsSoldCtx, itemsSoldConfig);
        
        // Update charts when window is resized
        window.addEventListener('resize', function() {
            salesChart.destroy();
            itemsSoldChart.destroy();
            salesChart = new Chart(salesCtx, salesConfig);
            itemsSoldChart = new Chart(itemsSoldCtx, itemsSoldConfig);
        });
    });
    </script>
</body>
</html>
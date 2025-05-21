<?php
$page_title = 'Admin Home Page';
require_once('includes/load.php');

// Permission check
page_require_level(1);

// Dashboard data
$c_categorie = count_by_id('categories');
$c_product = count_by_id('products');
$c_sale = count_by_id('sales');
$c_user = count_by_id('users');

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
$products_sold = find_highest_selling_products('10', $filter);

// Function to get highest selling products with correct total sales

function find_highest_selling_products($limit, $filter = 'all') {
    global $db;

    $where = '';
    $today = date('Y-m-d');
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $start_of_month = date('Y-m-01');
    $start_of_year = date('Y-01-01');

    switch($filter) {
        case 'year':
            $where = "WHERE YEAR(s.date) = YEAR(CURDATE())";
            break;
        case 'month':
            $where = "WHERE YEAR(s.date) = YEAR(CURDATE()) AND MONTH(s.date) = MONTH(CURDATE())";
            break;
        case 'day':
            $where = "WHERE DATE(s.date) = CURDATE()";
            break;
        case 'custom':
            if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                $where = "WHERE DATE(s.date) BETWEEN '{$_GET['start_date']}' AND '{$_GET['end_date']}'";
            }
            break;
        default:
            $where = "";
    }

    $sql = "SELECT p.name, p.id, SUM(s.qty) as totalSold, SUM(s.price * s.qty) as totalSales
            FROM sales s
            LEFT JOIN products p ON p.id = s.product_id
            {$where}
            GROUP BY s.product_id
            ORDER BY totalSold DESC
            LIMIT {$limit}";
    return find_by_sql($sql);
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'year';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$chart_type = isset($_GET['chart_type']) ? $_GET['chart_type'] : 'bar';
$year = date('Y');
$month = date('m');
$day = date('d');

// Get sales data based on filter
$sales_data = array();
$labels = array();
$sales_values = array();
$item_sold_values = array(); // Added for items sold data
$chart_title = '';
$total_sales_for_filter = 0; // Variable to hold total sales for the current filter

if ($filter == 'year') {
    $chart_title = 'Yearly Sales & Items Sold ('.date('Y').')'; // Updated title
    $sales_by_month = get_sales_by_month($year);
    $items_sold_by_month = get_items_sold_by_month($year); // Fetch items sold data
    $labels = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $sales_values = array_fill(0, 12, 0);
    $item_sold_values = array_fill(0, 12, 0); // Initialize items sold array
    foreach ($sales_by_month as $sale) {
        $month_index = (int)$sale['month'] - 1;
        $sales_values[$month_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales']; // Sum up total sales
    }
    foreach ($items_sold_by_month as $item) { // Populate items sold array
        $month_index = (int)$item['month'] - 1;
        $item_sold_values[$month_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'month') {
    $chart_title = 'Monthly Sales & Items Sold ('.date('F Y').')'; // Updated title
    $sales_by_day = get_sales_by_day($year, $month);
    $items_sold_by_day = get_items_sold_by_day($year, $month); // Fetch items sold data
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $labels = range(1, $days_in_month);
    $sales_values = array_fill(0, $days_in_month, 0);
    $item_sold_values = array_fill(0, $days_in_month, 0); // Initialize items sold array
    foreach ($sales_by_day as $sale) {
        $day_index = (int)$sale['day'] - 1;
        $sales_values[$day_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales']; // Sum up total sales
    }
    foreach ($items_sold_by_day as $item) { // Populate items sold array
        $day_index = (int)$item['day'] - 1;
        $item_sold_values[$day_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'day') {
    $chart_title = 'Daily Sales & Items Sold ('.date('F j, Y').')'; // Updated title
    $sales_by_hour = get_sales_by_hour(date('Y-m-d'));
    $items_sold_by_hour = get_items_sold_by_hour(date('Y-m-d')); // Fetch items sold data
    $labels = array();
    for ($i = 0; $i < 24; $i++) {
        $labels[] = sprintf("%02d:00", $i);
    }
    $sales_values = array_fill(0, 24, 0);
    $item_sold_values = array_fill(0, 24, 0); // Initialize items sold array
    foreach ($sales_by_hour as $sale) {
        $hour_index = (int)$sale['hour'];
        $sales_values[$hour_index] = (float)$sale['total_sales'];
        $total_sales_for_filter += (float)$sale['total_sales']; // Sum up total sales
    }
    foreach ($items_sold_by_hour as $item) { // Populate items sold array
        $hour_index = (int)$item['hour'];
        $item_sold_values[$hour_index] = (int)$item['total_qty'];
    }
} elseif ($filter == 'custom') {
    if (empty($end_date)) {
        $end_date = $start_date;
    }
    $chart_title = 'Sales & Items Sold from '.date('M j, Y', strtotime($start_date)).' to '.date('M j, Y', strtotime($end_date)); // Updated title

    $sales_by_date = get_sales_by_date_range($start_date, $end_date);
    $items_sold_by_date = get_items_sold_by_date_range($start_date, $end_date); // Fetch items sold data
    $date_range = createDateRangeArray($start_date, $end_date);
    $labels = array();
    $sales_values = array();
    $item_sold_values = array(); // Initialize items sold array

    foreach ($date_range as $date) {
        $labels[] = date('M j', strtotime($date));
        $sales_values[] = 0;
        $item_sold_values[] = 0; // Initialize items sold for each date
    }

    foreach ($sales_by_date as $sale) {
        $date = date('Y-m-d', strtotime($sale['date']));
        $index = array_search($date, $date_range);
        if ($index !== false) {
            $sales_values[$index] = (float)$sale['total_sales'];
            $total_sales_for_filter += (float)$sale['total_sales']; // Sum up total sales
        }
    }
     foreach ($items_sold_by_date as $item) { // Populate items sold array
        $date = date('Y-m-d', strtotime($item['date']));
        $index = array_search($date, $date_range);
        if ($index !== false) {
            $item_sold_values[$index] = (int)$item['total_qty'];
        }
    }
}

// Added functions to get items sold data
function get_items_sold_by_month($year) {
    global $db;
    $sql = "SELECT MONTH(date) as month, SUM(qty) as total_qty
            FROM sales
            WHERE YEAR(date) = '{$year}'
            GROUP BY MONTH(date)";
    return find_by_sql($sql);
}

function get_items_sold_by_day($year, $month) {
    global $db;
    $sql = "SELECT DAY(date) as day, SUM(qty) as total_qty
            FROM sales
            WHERE YEAR(date) = '{$year}' AND MONTH(date) = '{$month}'
            GROUP BY DAY(date)";
    return find_by_sql($sql);
}

function get_items_sold_by_hour($date) {
    global $db;
    $sql = "SELECT HOUR(date) as hour, SUM(qty) as total_qty
            FROM sales
            WHERE DATE(date) = '{$date}'
            GROUP BY HOUR(date)";
    return find_by_sql($sql);
}

function get_items_sold_by_date_range($start_date, $end_date) {
    global $db;
    $sql = "SELECT DATE(date) as date, SUM(qty) as total_qty
            FROM sales
            WHERE DATE(date) BETWEEN '{$start_date}' AND '{$end_date}'
            GROUP BY DATE(date)";
    return find_by_sql($sql);
}


function get_sales_by_month($year) {
    global $db;
    $sql = "SELECT MONTH(date) as month, SUM(price * qty) as total_sales
            FROM sales
            WHERE YEAR(date) = '{$year}'
            GROUP BY MONTH(date)";
    return find_by_sql($sql);
}

function get_sales_by_day($year, $month) {
    global $db;
    $sql = "SELECT DAY(date) as day, SUM(price * qty) as total_sales
            FROM sales
            WHERE YEAR(date) = '{$year}' AND MONTH(date) = '{$month}'
            GROUP BY DAY(date)";
    return find_by_sql($sql);
}

function get_sales_by_hour($date) {
    global $db;
    $sql = "SELECT HOUR(date) as hour, SUM(price * qty) as total_sales
            FROM sales
            WHERE DATE(date) = '{$date}'
            GROUP BY HOUR(date)";
    return find_by_sql($sql);
}

function get_sales_by_date_range($start_date, $end_date) {
    global $db;
    $sql = "SELECT DATE(date) as date, SUM(price * qty) as total_sales
            FROM sales
            WHERE DATE(date) BETWEEN '{$start_date}' AND '{$end_date}'
            GROUP BY DATE(date)";
    return find_by_sql($sql);
}

function createDateRangeArray($startDate, $endDate) {
    $begin = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end = $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($begin, $interval, $end);

    $dates = array();
    foreach ($dateRange as $date) {
        $dates[] = $date->format("Y-m-d");
    }
    return $dates;
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-3 col-sm-6">
    <a href="users.php" style="color:black;">
      <div class="panel panel-box clearfix square-panel">
        <div class="panel-icon pull-left bg-secondary1">
          <i class="glyphicon glyphicon-user"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"><?php echo $c_user['total']; ?></h2>
          <p class="text-muted">Users</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-md-3 col-sm-6">
    <a href="categorie.php" style="color:black;">
      <div class="panel panel-box clearfix square-panel">
        <div class="panel-icon pull-left bg-red">
          <i class="glyphicon glyphicon-th-large"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"><?php echo $c_categorie['total']; ?></h2>
          <p class="text-muted">Categories</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-md-3 col-sm-6">
    <a href="product.php" style="color:black;">
      <div class="panel panel-box clearfix square-panel">
        <div class="panel-icon pull-left bg-blue2">
          <i class="glyphicon glyphicon-shopping-cart"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"><?php echo $c_product['total']; ?></h2>
          <p class="text-muted">Products</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-md-3 col-sm-6">
    <a href="sales.php" style="color:black;">
      <div class="panel panel-box clearfix square-panel">
        <div class="panel-icon pull-left bg-green">
          <span style="font-size: 30px; font-weight: bold; color: white;">₱</span>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"><?php echo $c_sale['total']; ?></h2>
          <p class="text-muted">Sales</p>
        </div>
      </div>
    </a>
  </div>
</div>

<style>
  .square-panel {
    height: 150px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-sizing: border-box;
  }
  .panel-value {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-end;
  }
  .panel-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 60px;
  }
  .panel .panel-icon i {
    font-size: 30px;
  }
  .date-range-selector {
    margin-left: 10px; /* Added margin for spacing */
    display: <?php echo ($filter == 'custom') ? 'inline-block' : 'none'; ?>; /* Changed to inline-block */
    vertical-align: middle; /* Align vertically */
  }
  .chart-type-selector {
    margin-left: 10px;
  }
  .chart-container {
    position: relative;
    height: 400px;
    width: 100%;
  }
  /* Added style for the new filter row */
  .filter-row {
      margin-bottom: 20px;
      padding: 10px;
      background-color: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 4px;
  }
  .filter-row .form-inline .form-group {
      margin-right: 15px; /* Space between filter elements */
  }
  .inventory-panel {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
  }
  .inventory-value {
    font-size: 24px;
    font-weight: bold;
    color: #28a745;
  }
</style>

<div class="row filter-row">
    <div class="col-md-5">
        <form id="filterForm" method="get" action="" class="form-inline">
            <div class="form-group">
              <label for="filter" class="">Filter By: </label>
              <select name="filter" id="filter" class="form-control input-sm">
                <option value="year" <?= ($filter == 'year') ? 'selected' : '' ?>>This Year</option>
                <option value="month" <?= ($filter == 'month') ? 'selected' : '' ?>>This Month</option>
                <option value="day" <?= ($filter == 'day') ? 'selected' : '' ?>>Today</option>
                <option value="custom" <?= ($filter == 'custom') ? 'selected' : '' ?>>Custom Date Range</option>
              </select>
            </div>
            <div id="dateRangeSelector" class="date-range-selector form-group">
              <label for="start_date">From:</label>
              <input type="date" name="start_date" id="start_date" class="form-control input-sm"
                     value="<?= $start_date ?>" max="<?= date('Y-m-d') ?>">
              <label for="end_date">To:</label>
              <input type="date" name="end_date" id="end_date" class="form-control input-sm"
                     value="<?= $end_date ?>" max="<?= date('Y-m-d') ?>">
              <button type="button" id="applyFilter" class="btn btn-primary btn-sm">Apply</button>
            </div>
          </form>
    </div>
</div>


<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-stats"></span>
          <span><?php echo $chart_title; ?></span>
        </strong>
        <!-- Removed filter form from here -->
      </div>
      <div class="panel-body">
        <div class="chart-container">
          <canvas id="salesChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-shopping-cart"></span>
          <span>Total Items Sold</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="chart-container">
          <canvas id="itemsSoldChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Top Selling Products</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered table-condensed" style="height:370px;">
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
          <tr class="info">
            <td><strong>Grand Total</strong></td>
            <td><strong><?= $grandTotalSold ?></strong></td>
            <td><strong>₱<?= number_format($grandTotalSales, 2) ?></strong></td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-stats"></span>
          <span>Inventory Value</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="chart-container">
          <canvas id="inventoryChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Recently Added Products</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered table-condensed">
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
                <td>₱<?= (int)$recent_product['sale_price'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
    
    const inventoryChart = new Chart(inventoryCtx, {
        type: 'bar',
        data: {
            labels: ['Inventory Value'],
            datasets: [{
                label: 'Total Inventory Value',
                data: [<?php echo $inventory_value; ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize elements
    const filterForm = document.getElementById('filterForm');
    const filterSelect = document.getElementById('filter');
    const dateRangeSelector = document.getElementById('dateRangeSelector');
    const applyBtn = document.getElementById('applyFilter');
    const salesCtx = document.getElementById('salesChart').getContext('2d'); // Renamed for clarity
    const itemsSoldCtx = document.getElementById('itemsSoldChart').getContext('2d'); // Get context for items sold chart

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
        // Changed display to 'inline-block' to fit form-inline layout
        dateRangeSelector.style.display = filterSelect.value === 'custom' ? 'inline-block' : 'none';
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

    // Initialize chart
    const salesLabels = <?= json_encode($labels) ?>;
    const salesData = <?= json_encode($sales_values) ?>;
    const itemSoldData = <?= json_encode($item_sold_values) ?>; // Pass items sold data
    const xAxisTitle = <?= json_encode(
        $filter == 'year' ? 'Month' : ($filter == 'month' ? 'Day' : ($filter == 'day' ? 'Hour' : 'Date'))
    ) ?>;

    // Sales Chart configuration
    const salesConfig = { // Renamed config
        type: 'bar',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Total Sales (₱)',
                data: salesData,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
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

    // Items Sold Chart configuration (New)
    const itemsSoldConfig = {
        type: 'bar', // Use bar chart
        data: {
            labels: salesLabels, // Use the same labels as sales chart
            datasets: [{
                label: 'Total Items Sold',
                data: itemSoldData, // Use items sold data
                backgroundColor: 'rgba(255, 99, 132, 0.6)', // Different color
                borderColor: 'rgba(255, 99, 132, 1)',
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
                            return ` ${context.raw} Items`; // Tooltip for items
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Total Items Sold' } // Y-axis title
                },
                x: {
                    title: { display: true, text: xAxisTitle } // X-axis title
                }
            }
        }
    };


    // Create charts
    let salesChart = new Chart(salesCtx, salesConfig); // Create sales chart
    let itemsSoldChart = new Chart(itemsSoldCtx, itemsSoldConfig); // Create items sold chart

    // Update charts when window is resized
    window.addEventListener('resize', function() {
        salesChart.destroy();
        itemsSoldChart.destroy(); // Destroy items sold chart too
        salesChart = new Chart(salesCtx, salesConfig);
        itemsSoldChart = new Chart(itemsSoldCtx, itemsSoldConfig); // Recreate items sold chart
    });
});
</script>

<?php include_once('layouts/footer.php'); ?>

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
$products_sold = find_higest_saleing_product('10');
$recent_products = find_recent_product_added('5');

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'year';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$year = date('Y');
$month = date('m');
$day = date('d');

// Get sales data based on filter
$sales_data = array();
$labels = array();
$sales_values = array();
$chart_title = '';

if ($filter == 'year') {
    $chart_title = 'Yearly Sales ('.date('Y').')';
    $sales_by_month = get_sales_by_month($year);
    $labels = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $sales_values = array_fill(0, 12, 0);
    foreach ($sales_by_month as $sale) {
        $month_index = (int)$sale['month'] - 1;
        $sales_values[$month_index] = (float)$sale['total_sales'];
    }
} elseif ($filter == 'month') {
    $chart_title = 'Monthly Sales ('.date('F Y').')';
    $sales_by_day = get_sales_by_day($year, $month);
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $labels = range(1, $days_in_month);
    $sales_values = array_fill(0, $days_in_month, 0);
    foreach ($sales_by_day as $sale) {
        $day_index = (int)$sale['day'] - 1;
        $sales_values[$day_index] = (float)$sale['total_sales'];
    }
} elseif ($filter == 'day') {
    $chart_title = 'Daily Sales ('.date('F j, Y').')';
    $sales_by_hour = get_sales_by_hour(date('Y-m-d'));
    $labels = array();
    for ($i = 0; $i < 24; $i++) {
        $labels[] = sprintf("%02d:00", $i);
    }
    $sales_values = array_fill(0, 24, 0);
    foreach ($sales_by_hour as $sale) {
        $hour_index = (int)$sale['hour'];
        $sales_values[$hour_index] = (float)$sale['total_sales'];
    }
} elseif ($filter == 'custom') {
    if (empty($end_date)) {
        $end_date = $start_date;
    }
    $chart_title = 'Sales from '.date('M j, Y', strtotime($start_date)).' to '.date('M j, Y', strtotime($end_date));
    
    $sales_by_date = get_sales_by_date_range($start_date, $end_date);
    $date_range = createDateRangeArray($start_date, $end_date);
    $labels = array();
    $sales_values = array();
    
    foreach ($date_range as $date) {
        $labels[] = date('M j', strtotime($date));
        $sales_values[] = 0;
    }
    
    foreach ($sales_by_date as $sale) {
        $date = date('Y-m-d', strtotime($sale['date']));
        $index = array_search($date, $date_range);
        if ($index !== false) {
            $sales_values[$index] = (float)$sale['total_sales'];
        }
    }
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
    margin-bottom: 15px;
    display: <?php echo ($filter == 'custom') ? 'block' : 'none'; ?>;
  }
</style>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-stats"></span>
          <span><?php echo $chart_title; ?></span>
        </strong>
        <div class="pull-right">
          <form id="filterForm" method="get" action="" class="form-inline">
            <div class="form-group">
              <label for="filter" class="control-label">View: </label>
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
      <div class="panel-body">
        <canvas id="salesChart" height="400"></canvas>
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
          <span>Top Selling Products</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered table-condensed">
          <thead>
            <tr>
              <th>Product</th>
              <th>Units Sold</th>
              <th>Total Sales</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products_sold as $product): ?>
              <tr>
                <td><?= remove_junk(first_character($product['name'])) ?></td>
                <td><?= (int)$product['totalSold'] ?></td>
                <td>₱<?= number_format($product['totalSales'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
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
        dateRangeSelector.style.display = filterSelect.value === 'custom' ? 'block' : 'none';
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
    const xAxisTitle = <?= json_encode(
        $filter == 'year' ? 'Month' : ($filter == 'month' ? 'Day' : ($filter == 'day' ? 'Hour' : 'Date'))
    ) ?>;
    
    new Chart(
        document.getElementById('salesChart').getContext('2d'),
        {
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
        }
    );
});
</script>

<?php include_once('layouts/footer.php'); ?>
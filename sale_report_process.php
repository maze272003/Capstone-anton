<?php
$page_title = 'Sales Report';
require_once('includes/load.php');
require_once('tcpdf/tcpdf.php');

page_require_level(3);

if (isset($_POST['submit'])) {
    $req_dates = array('start-date', 'end-date');
    validate_fields($req_dates);

    if (empty($errors)) {
        $start_date = remove_junk($db->escape($_POST['start-date']));
        $end_date = remove_junk($db->escape($_POST['end-date']));
        
        $results = find_sale_by_dates($start_date, $end_date);

        // Convert the mysqli_result to an array
        $sales_data = [];
        if ($results) {
            foreach ($results as $result) {
                $sales_data[] = [
                    'date' => $result['date'],
                    'name' => $result['name'],
                    'buy_price' => $result['buy_price'],
                    'sale_price' => $result['sale_price'],
                    'total_sales' => $result['total_sales'],
                    'total_saleing_price' => $result['total_saleing_price'],
                ];
            }
        }

        // Check if no sales data found in selected date range
        if (empty($sales_data)) {
            $session->msg("d", "No sales records found in the selected date range.");
            redirect('sales_report.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('sales_report.php', false);
    }
} else {
    $session->msg("d", "Please select dates");
    redirect('sales_report.php', false);
}

if (!empty($sales_data)): 
?>
<!doctype html>
<html lang="en-US">
<head>
   <meta charset="utf-8">
   <title>Sales Report</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
</head>
<body>
<div class="container">
  <div class="clearfix">
  <a href="sales_report.php" class="btn btn-primary pull-left" style="margin-top: 15px;">Back</a>
  </div>
  <h1 class="text-center">Sales Report</h1>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Date</th>
          <th>Product Title</th>
          <th>Buying Price</th>
          <th>Selling Price</th>
          <th>Total Qty</th>
          <th>Total Sales</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $grand_total = 0;
        $total_buying_price = 0;
        foreach ($sales_data as $row): 
          $grand_total += $row['total_saleing_price'];
          $total_buying_price += ($row['buy_price'] * $row['total_sales']);
        ?>
          <tr>
            <td><?php echo remove_junk($row['date']); ?></td>
            <td><?php echo remove_junk(ucfirst($row['name'])); ?></td>
            <td>₱<?php echo remove_junk($row['buy_price']); ?></td>
            <td>₱<?php echo remove_junk($row['sale_price']); ?></td>
            <td><?php echo remove_junk($row['total_sales']); ?></td>
            <td>₱<?php echo remove_junk($row['total_saleing_price']); ?></td>
          </tr>
        <?php endforeach; 
        $profit = $grand_total - $total_buying_price;
        ?>
        <tr>
          <td colspan="5" class="text-right"><strong>Grand Total</strong></td>
          <td><strong>₱<?php echo number_format($grand_total, 2); ?></strong></td>
        </tr>
        <tr>
          <td colspan="5" class="text-right"><strong>Profit</strong></td>
          <td><strong>₱<?php echo number_format($profit, 2); ?></strong></td>
        </tr>
      </tbody>
    </table>

    <!-- Export to PDF Buttons -->
    <div class="dropdown" style="margin-bottom: 20px;">
      <!-- Change button text to English -->
      <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Select Report to Download
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" aria-labelledby="exportDropdown">
        <li>
          <form action="export_pdf.php" method="post" target="_blank">
            <?php 
            $today = date('Y-m-d');
            // Kunin ang data para sa kasalukuyang araw lang
            $daily_data = [];
            $daily_grand_total = 0;
            $daily_total_buying_price = 0;

            foreach ($sales_data as $item) {
                if (date('Y-m-d', strtotime($item['date'])) === $today) {
                    $daily_data[] = $item;
                    $daily_grand_total += $item['total_saleing_price'];
                    $daily_total_buying_price += ($item['buy_price'] * $item['total_sales']);
                }
            }
            $daily_profit = $daily_grand_total - $daily_total_buying_price;

            // Idagdag ang grand total at profit sa data
            $daily_summary = [
                'data' => $daily_data,
                'grand_total' => $daily_grand_total,
                'profit' => $daily_profit
            ];
            ?>
            <input type="hidden" name="start_date" value="<?php echo $today; ?>">
            <input type="hidden" name="end_date" value="<?php echo $today; ?>">
            <input type="hidden" name="sales_data" value='<?php echo json_encode($daily_summary); ?>'>
            <input type="hidden" name="report_type" value="daily">
            <button type="submit" class="btn btn-link">Daily Report</button>
          </form>
        </li>
        <li>
          <form action="export_pdf.php" method="post" target="_blank">
            <?php 
            // Get the start and end dates for the current week
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $week_end = date('Y-m-d', strtotime('sunday this week'));

            // Filter data for current week
            $weekly_data = [];
            $weekly_grand_total = 0;
            $weekly_total_buying_price = 0;

            foreach ($sales_data as $item) {
                $item_date = date('Y-m-d', strtotime($item['date']));
                if ($item_date >= $week_start && $item_date <= $week_end) {
                    $weekly_data[] = $item;
                    $weekly_grand_total += $item['total_saleing_price'];
                    $weekly_total_buying_price += ($item['buy_price'] * $item['total_sales']);
                }
            }
            $weekly_profit = $weekly_grand_total - $weekly_total_buying_price;

            // Prepare weekly summary
            $weekly_summary = [
                'data' => $weekly_data,
                'grand_total' => $weekly_grand_total,
                'profit' => $weekly_profit
            ];
            ?>
            <input type="hidden" name="start_date" value="<?php echo $week_start; ?>">
            <input type="hidden" name="end_date" value="<?php echo $week_end; ?>">
            <input type="hidden" name="sales_data" value='<?php echo json_encode($weekly_summary); ?>'>
            <input type="hidden" name="report_type" value="weekly">
            <button type="submit" class="btn btn-link">Weekly Report</button>
          </form>
        </li>
        <li>
          <form action="export_pdf.php" method="post" target="_blank">
            <?php 
            // Get the start and end dates for the current month
            $month_start = date('Y-m-01');
            $month_end = date('Y-m-t');

            // Filter data for current month
            $monthly_data = [];
            $monthly_grand_total = 0;
            $monthly_total_buying_price = 0;

            foreach ($sales_data as $item) {
                $item_date = date('Y-m-d', strtotime($item['date']));
                if ($item_date >= $month_start && $item_date <= $month_end) {
                    $monthly_data[] = $item;
                    $monthly_grand_total += $item['total_saleing_price'];
                    $monthly_total_buying_price += ($item['buy_price'] * $item['total_sales']);
                }
            }
            $monthly_profit = $monthly_grand_total - $monthly_total_buying_price;

            // Prepare monthly summary
            $monthly_summary = [
                'data' => $monthly_data,
                'grand_total' => $monthly_grand_total,
                'profit' => $monthly_profit
            ];
            ?>
            <input type="hidden" name="start_date" value="<?php echo $month_start; ?>">
            <input type="hidden" name="end_date" value="<?php echo $month_end; ?>">
            <input type="hidden" name="sales_data" value='<?php echo json_encode($monthly_summary); ?>'>
            <input type="hidden" name="report_type" value="monthly">
            <button type="submit" class="btn btn-link">Monthly Report</button>
          </form>
        </li>
        <li>
          <form action="export_pdf.php" method="post" target="_blank">
            <?php 
            // Get the start and end dates for the current year
            $year_start = date('Y-01-01');
            $year_end = date('Y-12-31');

            // Filter data for current year
            $yearly_data = [];
            $yearly_grand_total = 0;
            $yearly_total_buying_price = 0;

            foreach ($sales_data as $item) {
                $item_date = date('Y-m-d', strtotime($item['date']));
                if ($item_date >= $year_start && $item_date <= $year_end) {
                    $yearly_data[] = $item;
                    $yearly_grand_total += $item['total_saleing_price'];
                    $yearly_total_buying_price += ($item['buy_price'] * $item['total_sales']);
                }
            }
            $yearly_profit = $yearly_grand_total - $yearly_total_buying_price;

            // Prepare yearly summary
            $yearly_summary = [
                'data' => $yearly_data,
                'grand_total' => $yearly_grand_total,
                'profit' => $yearly_profit
            ];
            ?>
            <input type="hidden" name="start_date" value="<?php echo $year_start; ?>">
            <input type="hidden" name="end_date" value="<?php echo $year_end; ?>">
            <input type="hidden" name="sales_data" value='<?php echo json_encode($yearly_summary); ?>'>
            <input type="hidden" name="report_type" value="yearly">
            <button type="submit" class="btn btn-link">Yearly Report</button>
          </form>
        </li>
      </ul>
    </div>
  </div>

  <!-- Add Bootstrap JavaScript and jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>

<?php 
endif;
if (isset($db)) { $db->db_disconnect(); }
?>




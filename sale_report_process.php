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
    } else {
        $session->msg("d", $errors);
        redirect('sales_report.php', false);
    }
} else {
    $session->msg("d", "Select dates");
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
        <?php foreach ($sales_data as $row): ?>
          <tr>
            <td><?php echo remove_junk($row['date']); ?></td>
            <td><?php echo remove_junk(ucfirst($row['name'])); ?></td>
            <td>₱<?php echo remove_junk($row['buy_price']); ?></td>
            <td>₱<?php echo remove_junk($row['sale_price']); ?></td>
            <td><?php echo remove_junk($row['total_sales']); ?></td>
            <td>₱<?php echo remove_junk($row['total_saleing_price']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Export to PDF Button -->
    <form action="export_pdf.php" method="post" target="_blank">
      <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
      <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
      <input type="hidden" name="sales_data" value='<?php echo json_encode($sales_data); ?>'>
      <button type="submit" class="btn btn-primary">Export to PDF</button>
    </form>
  </div>
</body>
</html>

<?php 
endif;
if (isset($db)) { $db->db_disconnect(); }
?>




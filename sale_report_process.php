<?php
$page_title = 'Sales Report';
require_once('includes/load.php');
// Make sure the TCPDF path is correct for your installation
require_once('tcpdf/tcpdf.php');

page_require_level(3);

if (isset($_POST['submit'])) {
    $req_dates = array('start-date', 'end-date');
    validate_fields($req_dates);

    if (empty($errors)) {
        $start_date = remove_junk($db->escape($_POST['start-date']));
        $end_date = remove_junk($db->escape($_POST['end-date']));
        
        // Get sales data - modified to handle both array and mysqli_result returns
        $sales_data = [];
        $results = find_sale_by_dates($start_date, $end_date);
        
        if (is_array($results)) {
            // If results is already an array, use it directly
            $sales_data = $results;
        } elseif (is_object($results) && get_class($results) === 'mysqli_result') {
            // If results is a mysqli_result object, fetch data from it
            while ($result = $db->fetch_assoc($results)) {
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

// Get low stock products (quantity < 100) organized by categories
$low_stock_by_category = [];
$categories = find_all('categories');
if ($categories) {
    foreach ($categories as $category) {
        // Directly query products with quantity < 100 for this category
        $sql = "SELECT p.id, p.name, p.quantity, p.categorie_id, c.name AS category_name 
                FROM products p
                LEFT JOIN categories c ON p.categorie_id = c.id
                WHERE p.quantity < 100 AND p.categorie_id = {$category['id']}";
        
        $result = $db->query($sql);
        $low_stock_products = [];
        
        if ($db->num_rows($result) > 0) {
            while ($product = $db->fetch_assoc($result)) {
                $low_stock_products[] = [
                    'name' => $product['name'],
                    'quantity' => (int)$product['quantity']
                ];
            }
        }
        
        if (!empty($low_stock_products)) {
            $low_stock_by_category[] = [
                'category_name' => $category['name'],
                'products' => $low_stock_products,
                'count' => count($low_stock_products)
            ];
        }
    }
}

if (!empty($sales_data)): 
?>
<!doctype html>
<html lang="en-US">
<head>
   <meta charset="utf-8">
   <title>Sales Report</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
   <style>
       .low-stock { color: #d9534f; font-weight: bold; }
   </style>
</head>
<body>
<div class="container">
  <div class="clearfix">
  <a href="sales.php" class="btn btn-primary pull-left" style="margin-top: 15px;">Back</a>
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

    <!-- Low Stock Products Section -->
    <?php if (!empty($low_stock_by_category)): ?>
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">Low Stock Alert (Quantity < 100)</h3>
        </div>
        <div class="panel-body">
            <?php foreach ($low_stock_by_category as $category): ?>
                <div class="category-section">
                    <h4><?php echo $category['category_name']; ?> 
                        <span class="badge"><?php echo $category['count']; ?> products</span>
                    </h4>
                    <table class="table table-condensed table-hover">
                        <thead>
                            <tr>
                                <th width="70%">Product Name</th>
                                <th width="30%">Current Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category['products'] as $product): ?>
                            <tr>
                                <td><?php echo $product['name']; ?></td>
                                <td class="low-stock"><?php echo $product['quantity']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success">No products with low stock (below 100) found.</div>
    <?php endif; ?>

    <!-- Export to PDF Button -->
    <div class="text-center" style="margin: 20px 0;">
      <form action="export_pdf.php" method="post" target="_blank">
        <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
        <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
        <input type="hidden" name="sales_data" value='<?php echo htmlentities(json_encode([
            'data' => $sales_data,
            'grand_total' => $grand_total,
            'profit' => $profit,
            'low_stock' => $low_stock_by_category
        ])); ?>'>
        <input type="hidden" name="report_type" value="custom">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="glyphicon glyphicon-download"></i> Download Full Report
        </button>
      </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>

<?php 
endif;
if (isset($db)) { $db->db_disconnect(); }
?>
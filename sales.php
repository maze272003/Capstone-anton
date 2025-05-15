<?php
  $page_title = 'All Sales';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(3);
?>

<?php
$sales = find_all_sale(); // Fetch sales data from database
?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>All Sales</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped" id="salesTable">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th> Product Name </th>
              <th class="text-center" style="width: 15%;"> Quantity </th>
              <th class="text-center" style="width: 15%;"> Total ₱ </th>
              <th class="text-center" style="width: 15%;"> Date </th>
            </tr>
          </thead>
          <tbody id="salesBody">
            <?php foreach ($sales as $sale): ?>
            <tr>
              <td class="text-center"><?php echo count_id(); ?></td>
              <td><?php echo remove_junk($sale['name']); ?></td>
              <td class="text-center"><?php echo (int)$sale['qty']; ?></td>
              <td class="text-center sale-total" 
                  data-date="<?php echo $sale['date']; ?>" 
                  data-amount="<?php echo isset($sale['total']) ? $sale['total'] : ($sale['qty'] * $sale['price']); ?>">
                <?php 
                  // Calculate total dynamically if missing from the database
                  $total = isset($sale['total']) ? $sale['total'] : ($sale['qty'] * $sale['price']);
                  echo "₱ " . number_format($total, 2); // PHP format (for backup)
                ?>
              </td>
              <td class="text-center"><?php echo $sale['date']; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Display Monthly Sales Totals (Handled by JavaScript) -->
        <h3 class="text-center">Total Sales Per Month</h3>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">Month</th>
              <th class="text-center">Total ₱</th>
            </tr>
          </thead>
          <tbody id="monthlySalesBody">
            <!-- JavaScript will populate this dynamically -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    let monthlySales = {};

    // Get all sale totals from table
    document.querySelectorAll('.sale-total').forEach(item => {
      let date = item.getAttribute("data-date");
      let amount = parseFloat(item.getAttribute("data-amount")) || 0;
      
      // Extract YYYY-MM format from date
      let month = date.substring(0, 7);

      // Add total sales per month
      if (!monthlySales[month]) {
        monthlySales[month] = 0;
      }
      monthlySales[month] += amount;
    });

    // Insert totals into table
    let monthlySalesBody = document.getElementById("monthlySalesBody");
    for (let month in monthlySales) {
      let formattedTotal = "₱ " + monthlySales[month].toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let row = `<tr>
                  <td class="text-center">${month}</td>
                  <td class="text-center">${formattedTotal}</td>
                </tr>`;
      monthlySalesBody.innerHTML += row;
    }
  });
</script>

<?php include_once('layouts/footer.php'); ?>







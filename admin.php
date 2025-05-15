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
$products_sold = find_higest_saleing_product('10');  // Make sure this fetches updated sales
$recent_products = find_recent_product_added('5');  
?>

<?php include_once('layouts/header.php'); ?>

<!-- Dashboard Panels -->
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

<!-- Style for Panels -->
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
</style>

<!-- ✅ Product Sales Overview Chart -->
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-stats"></span>
          <span>Product Sales Overview</span>
        </strong>
      </div>
      <div class="panel-body">
        <canvas id="productSalesChart" height="400"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Recently Added Products -->
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
                <td><?php echo remove_junk(first_character($recent_product['name'])); ?></td>
                <td><?php echo remove_junk(first_character($recent_product['categorie'])); ?></td>
                <td>₱<?php echo (int)$recent_product['sale_price']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const productLabels = [<?php foreach ($products_sold as $product) { echo "'" . $product['name'] . "',"; } ?>];
  const productQtyData = [<?php foreach ($products_sold as $product) { echo $product['totalSold'] . ","; } ?>];

  const ctx = document.getElementById('productSalesChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: productLabels,
      datasets: [{
        label: 'Units Sold',
        data: productQtyData,
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Units Sold'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Product Name'
          }
        }
      }
    }
  });
</script>

<?php include_once('layouts/footer.php'); ?>

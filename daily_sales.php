<?php
  $page_title = 'Daily Sales';
  require_once('includes/load.php');
  page_require_level(3);

  $year  = date('Y');
  $month = date('m');
  $sales = dailySales($year, $month);
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
          <span>Daily Sales</span>
        </strong>
      </div>
      <div class="panel-body">
        <canvas id="dailySalesChart"></canvas>
        <h3 class="text-center">Total Sales: ₱<?php echo array_sum(array_column($sales, 'total_saleing_price')); ?></h3>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('dailySalesChart').getContext('2d');
    var salesData = {
      labels: [<?php foreach ($sales as $sale) { echo "'" . $sale['date'] . "',"; } ?>],
      datasets: [{
        label: 'Total Sales (₱)',
        data: [<?php foreach ($sales as $sale) { echo $sale['total_saleing_price'] . ","; } ?>],
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderWidth: 2,
        fill: true,
      }]
    };
    
    new Chart(ctx, {
      type: 'line',
      data: salesData,
      options: {
        responsive: true,
        plugins: {
          legend: { display: true },
        },
        scales: {
          x: { title: { display: true, text: 'Date' } },
          y: { title: { display: true, text: 'Total Sales (₱)' } }
        }
      }
    });
  });
</script>

<?php include_once('layouts/footer.php'); ?>


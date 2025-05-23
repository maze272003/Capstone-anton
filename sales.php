<?php
  $page_title = 'All Sales';
  require_once('includes/load.php');
  
  // Check if user is logged in properly
  if (!$session->isUserLoggedIn(true)) {
      redirect('index.php', false);
  }
  
  // Get current user data
  $user = current_user();
  
  // Verify user exists and has required level
  if (empty($user) || !isset($user['user_level'])) {
      $session->msg('d', 'User data not found!');
      redirect('index.php', false);
  }
  
  page_require_level(1);
  $sales = find_all_sale();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sales Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
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
        }
        
        .table tr:hover {
            background-color: #f9f9f9;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #e6f7f0;
            color: #00a854;
        }
        
        .badge-danger {
            background-color: #fff1f0;
            color: #f5222d;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .sale-total {
            font-weight: 500;
            color: var(--primary);
        }
        
        .monthly-total {
            font-weight: 600;
            color: var(--success);
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
        
        .summary-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .summary-card h4 {
            margin-top: 0;
            color: var(--gray);
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .summary-card p {
            font-size: 24px;
            font-weight: 600;
            margin: 10px 0 0;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include_once('sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-shopping-cart"></i> Sales Management</h1>
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
            
            <!-- Sales Summary Cards -->
            <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div class="summary-card" style="flex: 1;">
                    <h4>Total Sales Today</h4>
                    <p id="todayTotal">₱ 0.00</p>
                </div>
                <div class="summary-card" style="flex: 1;">
                    <h4>Total Sales This Month</h4>
                    <p id="monthTotal">₱ 0.00</p>
                </div>
                <div class="summary-card" style="flex: 1;">
                    <h4>Total All Sales</h4>
                    <p id="allTotal">₱ 0.00</p>
                </div>
            </div>
            
            <!-- Sales Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>All Sales Records</h3>
                    <a href="add_sale.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Sale
                    </a>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Date</th>
                            </tr>
                        </thead>
                        <tbody id="salesBody">
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td><?php echo remove_junk($sale['name']); ?></td>
                                <td><?php echo remove_junk($sale['category']); ?></td>
                                <td class="text-center"><?php echo (int)$sale['qty']; ?></td>
                                <td class="text-center">₱<?php echo number_format($sale['price'], 2); ?></td>
                                <td class="text-center sale-total" 
                                    data-date="<?php echo $sale['date']; ?>" 
                                    data-amount="<?php echo $sale['total']; ?>">
                                    ₱<?php echo number_format($sale['total'], 2); ?>
                                </td>
                                <td class="text-center"><?php echo $sale['date']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Monthly Sales Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Monthly Sales Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center">Month</th>
                                <th class="text-center">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody id="monthlySalesBody">
                            <!-- JavaScript will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Highlight current page in sidebar
            $('.sidebar-menu a').each(function() {
                if (window.location.href.indexOf($(this).attr('href')) > -1) {
                    $(this).addClass('active');
                }
            });
            
            let monthlySales = {};
            let todayTotal = 0;
            let monthTotal = 0;
            let allTotal = 0;
            const today = new Date().toISOString().split('T')[0];
            const currentMonth = new Date().toISOString().substring(0, 7);
            
            // Calculate totals
            $('.sale-total').each(function() {
                const date = $(this).data('date');
                const amount = parseFloat($(this).data('amount')) || 0;
                const month = date.substring(0, 7);
                const saleDate = date.split(' ')[0]; // In case datetime format
                
                // Add to monthly totals
                if (!monthlySales[month]) {
                    monthlySales[month] = 0;
                }
                monthlySales[month] += amount;
                
                // Add to all time total
                allTotal += amount;
                
                // Check if sale is from today
                if (saleDate === today) {
                    todayTotal += amount;
                }
                
                // Check if sale is from current month
                if (month === currentMonth) {
                    monthTotal += amount;
                }
            });
            
            // Update summary cards
            $('#todayTotal').text('₱ ' + todayTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#monthTotal').text('₱ ' + monthTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#allTotal').text('₱ ' + allTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            
            // Populate monthly sales table
            const monthlySalesBody = $('#monthlySalesBody');
            Object.keys(monthlySales).sort().reverse().forEach(month => {
                const formattedTotal = '₱ ' + monthlySales[month].toLocaleString('en-US', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });
                
                monthlySalesBody.append(`
                    <tr>
                        <td class="text-center">${month}</td>
                        <td class="text-center monthly-total">${formattedTotal}</td>
                    </tr>
                `);
            });
        });
    </script>
</body>
</html>
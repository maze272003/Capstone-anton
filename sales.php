<?php
$page_title = 'Sales Management & Reports';
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

page_require_level(3);
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
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    
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
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #3a56d4 0%, #2a3eb1 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-250px);
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
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }
        
        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
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
            flex-wrap: wrap;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            color: var(--dark);
        }
        
        .card-body {
            padding: 20px;
            overflow-x: auto;
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
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #3ab7d8;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--gray);
            color: white;
        }
        
        .search-filter-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
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
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 4px;
        }
        
        .badge-success {
            background-color: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }
        
        .badge-danger {
            background-color: #fff1f0;
            color: #f5222d;
            border: 1px solid #ffa39e;
        }
        
        .badge-info {
            background-color: #e6f7ff;
            color: #1890ff;
            border: 1px solid #91d5ff;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
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
        
        .input-group {
            display: flex;
            align-items: center;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .input-group-addon {
            padding: 0 15px;
            background-color: #f5f7fb;
            border: 1px solid #ddd;
            border-left: none;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            color: var(--gray);
        }
        
        .datepicker {
            z-index: 1000 !important;
        }
        
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .col {
            flex: 1;
            min-width: 250px;
        }
        
        .filter-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-group .btn.active {
            background-color: var(--primary);
            color: white;
        }
        
        /* Enhanced Modal Styles */
        .modal-dialog {
            margin-top: 50px;
            transform: translate(0, 0) !important;
        }
        
        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }
        
        .modal-backdrop {
           background-color: rgba(0, 0, 0, 0.7);
        }
        
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            border: none;
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: none;
            padding: 20px;
        }
        
        .modal-header .close {
            color: white;
            opacity: 1;
            text-shadow: none;
            font-size: 24px;
        }
        
        .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            font-size: 20px;
        }
        
        .modal-body {
            padding: 30px;
            overflow-y: auto;
        }
        
        .modal-footer {
            border-top: 1px solid #eee;
            padding: 20px;
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-250px);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .card-header h3 {
                margin-bottom: 10px;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group-addon {
                width: 100%;
                border-left: 1px solid #ddd;
                border-top: none;
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .user-profile .user-info {
                text-align: left;
                margin-right: 0;
            }
            
            .table th, .table td {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .page-title h1 {
                font-size: 20px;
            }
            
            .table th, .table td {
                padding: 6px 8px;
                font-size: 12px;
            }
            
            .badge {
                font-size: 10px;
                padding: 2px 6px;
            }
            
            .summary-card p {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i> Menu
        </button>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-bullseye"></i> Spring Bullbars</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    <li><a href="sales.php" class="active"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="transaction_history.php"><i class="fas fa-history"></i> Transaction History</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-shopping-cart"></i> Sales & Reports</h1>
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
            <div class="row">
                <div class="col">
                    <div class="summary-card">
                        <h4>Total Sales Today</h4>
                        <p id="todayTotal">₱ 0.00</p>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-card">
                        <h4>Total Sales This Month</h4>
                        <p id="monthTotal">₱ 0.00</p>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-card">
                        <h4>Total All Sales</h4>
                        <p id="allTotal">₱ 0.00</p>
                    </div>
                </div>
            </div>
            
            <!-- Report Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Generate Sales Report</h3>
                </div>
                <div class="card-body">
                    <form class="clearfix" method="post" action="sale_report_process.php">
                        <div class="form-group">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="text" class="datepicker form-control" name="start-date" placeholder="From Date" autocomplete="off">
                                <span class="input-group-addon"><i class="fas fa-arrow-right"></i></span>
                                <input type="text" class="datepicker form-control" name="end-date" placeholder="To Date" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Generate PDF Report
                            </button>
                            <button type="button" id="filterSales" class="btn btn-success">
                                <i class="fas fa-filter"></i> Filter Sales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sales Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Sales Records</h3>
                    <div class="filter-actions">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">All</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="today">Today</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="week">This Week</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="month">This Month</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="year">This Year</button>
                        </div>
                    </div>
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
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="salesBody">
                            <?php foreach ($sales as $sale): ?>
                            <tr data-date="<?php echo $sale['date']; ?>">
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
                                <td class="text-center">
                                    <a href="edit_sale_admin.php?id=<?php echo (int)$sale['id'];?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_sale.php?id=<?php echo (int)$sale['id'];?>" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
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
                                <th class="text-center">Actions</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('expanded');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(event.target) && event.target !== mobileMenuToggle) {
                        sidebar.classList.remove('show');
                        mainContent.classList.remove('expanded');
                    }
                }
            });
            
            // Highlight current page in sidebar
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Initialize datepicker
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
            
            // Set default dates (optional)
            $('input[name="start-date"]').datepicker('setDate', '-7d');
            $('input[name="end-date"]').datepicker('setDate', new Date());
            
            let monthlySales = {};
            let todayTotal = 0;
            let monthTotal = 0;
            let allTotal = 0;
            const today = new Date().toISOString().split('T')[0];
            const currentMonth = new Date().toISOString().substring(0, 7);
            
            // Calculate totals
            document.querySelectorAll('.sale-total').forEach(element => {
                const date = element.dataset.date;
                const amount = parseFloat(element.dataset.amount) || 0;
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
            document.getElementById('todayTotal').textContent = '₱ ' + todayTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('monthTotal').textContent = '₱ ' + monthTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('allTotal').textContent = '₱ ' + allTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            // Populate monthly sales table
            const monthlySalesBody = document.getElementById('monthlySalesBody');
            Object.keys(monthlySales).sort().reverse().forEach(month => {
                const formattedTotal = '₱ ' + monthlySales[month].toLocaleString('en-US', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });
                
                monthlySalesBody.innerHTML += `
                    <tr>
                        <td class="text-center">${month}</td>
                        <td class="text-center monthly-total">${formattedTotal}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary view-month" data-month="${month}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <a href="sale_report_process.php?month=${month}" class="btn btn-sm btn-success export-month" data-month="${month}">
                                <i class="fas fa-file-export"></i> Export
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            // Filter sales by date range
            document.getElementById('filterSales').addEventListener('click', function() {
                const startDate = document.querySelector('input[name="start-date"]').value;
                const endDate = document.querySelector('input[name="end-date"]').value;
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }
                
                // Hide all rows first
                document.querySelectorAll('#salesBody tr').forEach(row => {
                    row.style.display = 'none';
                });
                
                // Show only rows within date range
                document.querySelectorAll('#salesBody tr').forEach(row => {
                    const rowDate = row.querySelector('.sale-total').dataset.date.split(' ')[0];
                    
                    if (rowDate >= startDate && rowDate <= endDate) {
                        row.style.display = '';
                    }
                });
            });
            
            // View monthly sales
            document.addEventListener('click', function(event) {
                if (event.target.closest('.view-month')) {
                    const month = event.target.closest('.view-month').dataset.month;
                    
                    // Hide all rows first
                    document.querySelectorAll('#salesBody tr').forEach(row => {
                        row.style.display = 'none';
                    });
                    
                    // Show only rows from selected month
                    document.querySelectorAll('#salesBody tr').forEach(row => {
                        const rowDate = row.querySelector('.sale-total').dataset.date;
                        const rowMonth = rowDate.substring(0, 7);
                        
                        if (rowMonth === month) {
                            row.style.display = '';
                        }
                    });
                    
                    // Scroll to sales table
                    document.querySelector('#salesBody').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
            
            // Filter sales by time period
            document.querySelectorAll('[data-filter]').forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.dataset.filter;
                    const today = new Date();
                    
                    // Remove active class from all buttons
                    document.querySelectorAll('[data-filter]').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    document.querySelectorAll('#salesBody tr').forEach(row => {
                        const rowDateStr = row.dataset.date.split(' ')[0]; // Get date part only
                        const rowDate = new Date(rowDateStr);
                        const rowTime = rowDate.getTime();
                        let showRow = false;
                        
                        // Calculate time periods
                        const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
                        const weekStart = new Date(today.getTime() - (today.getDay() * 24 * 60 * 60 * 1000)).getTime();
                        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1).getTime();
                        const yearStart = new Date(today.getFullYear(), 0, 1).getTime();
                        
                        switch(filter) {
                            case 'today':
                                showRow = rowTime >= todayStart;
                                break;
                            case 'week':
                                showRow = rowTime >= weekStart;
                                break;
                            case 'month':
                                showRow = rowTime >= monthStart;
                                break;
                            case 'year':
                                showRow = rowTime >= yearStart;
                                break;
                            case 'all':
                            default:
                                showRow = true;
                                break;
                        }
                        
                        row.style.display = showRow ? '' : 'none';
                    });
                });
            });
            
            // Initialize with all sales shown
            document.querySelector('[data-filter="all"]').click();
        });
    </script>
</body>
</html>
<?php
$page_title="Transaction History";
require_once('includes/load.php');
page_require_level(1);

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Number of items per page
$start = ($page > 1) ? ($page - 1) * $per_page : 0;

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Query for sales transactions with pagination
$sql = "SELECT s.*, p.name as product_name, u.name as user_name 
       FROM sales s 
       LEFT JOIN products p ON s.product_id = p.id 
       LEFT JOIN users u ON s.user_id = u.id 
       WHERE 1=1";

// Apply filters
if (!empty($search)) {
    $search = remove_junk($db->escape($search));
    $sql .= " AND (p.name LIKE '%{$search}%' OR u.name LIKE '%{$search}%')";
}

if (!empty($date_filter)) {
    switch($date_filter) {
        case 'today':
            $sql .= " AND DATE(s.date) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND YEARWEEK(s.date) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $sql .= " AND MONTH(s.date) = MONTH(CURDATE()) AND YEAR(s.date) = YEAR(CURDATE())";
            break;
    }
}

if (!empty($status_filter)) {
    $status = remove_junk($db->escape($status_filter));
    $sql .= " AND s.status = '{$status}'";
}

// Get total count for pagination
$total_sql = str_replace('SELECT s.*, p.name as product_name, u.name as user_name', 'SELECT COUNT(*) as total', $sql);
$total_result = $db->query($total_sql);
$total = $db->fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $per_page);

// Add sorting and pagination to main query
$sql .= " ORDER BY s.date DESC LIMIT {$start}, {$per_page}";
$sales = find_by_sql($sql);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
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
        
        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
            flex-wrap: wrap;
        }
        
        .pagination li {
            margin: 5px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--primary);
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
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
            
            .pagination a, .pagination span {
                padding: 6px 10px;
                font-size: 12px;
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
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="transaction_history.php" class="active"><i class="fas fa-history"></i> Transaction History</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-history"></i> Transaction History</h1>
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
            
            <!-- Search and Filter Card -->
            <div class="card search-filter-container">
                <form method="get" action="">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="Search transactions..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control" name="date_filter">
                                    <option value="">All Time</option>
                                    <option value="today" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'today') ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'week') ? 'selected' : ''; ?>>This Week</option>
                                    <option value="month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'month') ? 'selected' : ''; ?>>This Month</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="transaction_history.php" class="btn btn-secondary" style="text-decoration: none;">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Transactions Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Transaction Records</h3>
                    <div class="text-muted">Showing <?php echo $start + 1; ?> to <?php echo min($start + $per_page, $total); ?> of <?php echo $total; ?> entries</div>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales)):
                                $count = $start + 1;
                                foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($sale['date'])); ?></td>
                                    <td><?php echo remove_junk($sale['id']); ?></td>
                                    <td><?php echo remove_junk($sale['product_name']); ?></td>
                                    <td class="text-center"><?php echo $sale['qty']; ?></td>
                                    <td class="text-right">₱<?php echo number_format($sale['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No transaction records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"><i class="fas fa-angle-double-left"></i></a></li>
                                <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="fas fa-angle-left"></i></a></li>
                            <?php else: ?>
                                <li class="disabled"><span><i class="fas fa-angle-double-left"></i></span></li>
                                <li class="disabled"><span><i class="fas fa-angle-left"></i></span></li>
                            <?php endif; ?>
                            
                            <?php 
                            // Show page numbers
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li><a href="?'.http_build_query(array_merge($_GET, ['page' => 1])).'">1</a></li>';
                                if ($start_page > 2) echo '<li class="disabled"><span>...</span></li>';
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; 
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) echo '<li class="disabled"><span>...</span></li>';
                                echo '<li><a href="?'.http_build_query(array_merge($_GET, ['page' => $total_pages])).'">'.$total_pages.'</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"><i class="fas fa-angle-right"></i></a></li>
                                <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><i class="fas fa-angle-double-right"></i></a></li>
                            <?php else: ?>
                                <li class="disabled"><span><i class="fas fa-angle-right"></i></span></li>
                                <li class="disabled"><span><i class="fas fa-angle-double-right"></i></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
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
        });
    </script>
</body>
</html>
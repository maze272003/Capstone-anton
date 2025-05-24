<?php
$page_title="Transaction History";
require_once('includes/load.php');
page_require_level(1);


// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_filter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';

$products = join_product_table();
$today = date('Y-m-d');

// Apply search and filters
$filtered_products = array();
foreach ($products as $product) {
    $matches_search = empty($search) || 
                     stripos($product['name'], $search) !== false || 
                     stripos($product['categorie'], $search) !== false;
    
    $matches_category = empty($category_filter) || 
                       $product['categorie'] == $category_filter;
    
    $matches_stock = true;
    if ($stock_filter == 'low') {
        $matches_stock = $product['quantity'] < 10;
    } elseif ($stock_filter == 'available') {
        $matches_stock = $product['quantity'] >= 10;
    }
    
    if ($matches_search && $matches_category && $matches_stock) {
        $filtered_products[] = $product;
    }
}

// Group products by category and sort by date
$categorized_products = array();
foreach ($filtered_products as $product) {
    $categorized_products[$product['categorie']][] = $product;
}

foreach ($categorized_products as &$category_products) {
    usort($category_products, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
unset($category_products);

// Get all categories for filter dropdown
$all_categories = array_unique(array_column($products, 'categorie'));
sort($all_categories);

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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
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
    
    .btn-success {
        background-color: var(--success);
        color: white;
    }
    
    .btn-warning {
        background-color: var(--warning);
        color: white;
    }
    
    .btn-danger {
        background-color: var(--danger);
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
    
    .badge-warning {
        background-color: #fff7e6;
        color: #fa8c16;
    }
    
    .badge-danger {
        background-color: #fff1f0;
        color: #f5222d;
    }
    
    .badge-info {
        background-color: #e6f7ff;
        color: #1890ff;
    }
    
    .action-buttons .btn {
        padding: 5px 10px;
        font-size: 12px;
        margin-right: 5px;
    }
    
    .img-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .category-nav {
        position: sticky;
        top: 80px;
        background: white;
        z-index: 100;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .category-nav ul {
        margin: 0;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .category-nav li {
        list-style: none;
    }
    
    .category-nav a {
        display: block;
        padding: 5px 15px;
        text-decoration: none;
        color: var(--gray);
        border-radius: 20px;
        border: 1px solid #e0e0e0;
        font-size: 13px;
        transition: all 0.3s;
    }
    
    .category-nav a:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .new-product {
        background-color: #fffde7 !important;
        border-left: 4px solid #ffc107 !important;
    }
    
    .new-badge {
        background-color: #ff5722;
        color: white;
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
        animation: blink 1.5s infinite;
    }
    
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
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
    
    .section-title {
        font-size: 18px;
        font-weight: 500;
        margin: 30px 0 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
        color: var(--primary);
    }
</style>
<body>
    <div class="admin-container">
    <?php include_once('sidebar.php'); ?>

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-exchange-alt"></i> Transaction History</h1>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="Search sales..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select class="form-control" name="date_filter">
                                    <option value="">All Time</option>
                                    <option value="today" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'today') ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'week') ? 'selected' : ''; ?>>This Week</option>
                                    <option value="month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'month') ? 'selected' : ''; ?>>This Month</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select class="form-control" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="completed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button type="button" onclick="window.location.href='transaction_history.php'" class="btn btn-secondary" style="text-decoration: none;">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Transactions Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Transaction History</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Sales ID</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th class="text-center">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query para sa sales transactions lang
                            $sql = "SELECT s.*, p.name as product_name, u.name as user_name 
                                   FROM sales s 
                                   LEFT JOIN products p ON s.product_id = p.id 
                                   LEFT JOIN users u ON s.user_id = u.id 
                                   WHERE 1=1";
                            
                            // Mga filters
                            if (!empty($_GET['search'])) {
                                $search = remove_junk($db->escape($_GET['search']));
                                $sql .= " AND (p.name LIKE '%{$search}%' OR u.name LIKE '%{$search}%')";
                            }
                            
                            if (!empty($_GET['date_filter'])) {
                                switch($_GET['date_filter']) {
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
                            
                            if (!empty($_GET['status_filter'])) {
                                $status = remove_junk($db->escape($_GET['status_filter']));
                                $sql .= " AND s.status = '{$status}'";
                            }
                            
                            $sql .= " ORDER BY s.date DESC";
                            $sales = find_by_sql($sql);
                            
                            if (!empty($sales)):
                                $count = 1;
                                foreach ($sales as $sale):
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($sale['date'])); ?></td>
                                    <td><?php echo remove_junk($sale['id']); ?></td>
                                    <td><?php echo remove_junk($sale['product_name']); ?></td>
                                    <td class="text-center"><?php echo $sale['qty']; ?></td>
                                    <td class="text-right">₱<?php echo number_format($sale['price'], 2); ?></td>
                                </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <tr>
                                    <td colspan="9" class="text-center">No sales transactions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
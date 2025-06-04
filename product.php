<?php
$page_title = 'Product and Category Management';
require_once('includes/load.php');
page_require_level(1);

// Get current user data
$user = current_user();

// Check if user is logged in properly
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

// Verify user exists and has required level
if (empty($user) || !isset($user['user_level'])) {
    $session->msg('d', 'User data not found!');
    redirect('index.php', false);
}

// PRODUCTS SECTION
// Get search and filter parameters for products
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_filter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';

$products = join_product_table();
$today = date('Y-m-d');

// Apply search and filters for products
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

// Get all categories for product filter dropdown
$all_categories = array_unique(array_column($products, 'categorie'));
sort($all_categories);

// CATEGORIES SECTION
// Fetch all categories for the dropdown filter
$all_categories_for_filter = find_all('categories');

// Find all categories or filter by search query or dropdown
$sql = "SELECT * FROM categories";
$conditions = [];

if (isset($_GET['cat_search']) && !empty($_GET['cat_search'])) {
    $search_term = remove_junk($_GET['cat_search']);
    $conditions[] = "name LIKE '%" . $db->escape($search_term) . "%'";
}

if (isset($_GET['category_filter']) && !empty($_GET['category_filter']) && $_GET['category_filter'] !== 'all') {
    $category_filter_id = remove_junk($_GET['category_filter']);
    $conditions[] = "id = '" . $db->escape($category_filter_id) . "'";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$all_categories_list = find_by_sql($sql);

// Handle category addition
if(isset($_POST['add_cat'])) {
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($db->escape($_POST['categorie-name']));
    if(empty($errors)){
       $sql  = "INSERT INTO categories (name)";
       $sql .= " VALUES ('{$cat_name}')";
       if($db->query($sql)){
         $session->msg("s", "Successfully Added New Category");
         redirect('product.php', false);
       } else {
         $session->msg("d", "Sorry Failed to insert.");
         redirect('product.php', false);
       }
    } else {
      $session->msg("d", $errors);
      redirect('product.php', false);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Product and Category Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    
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
        
        .badge-warning {
            background-color: #fffbe6;
            color: #faad14;
            border: 1px solid #ffe58f;
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
        
        .img-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .category-nav {
            position: static;
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
        
        .alert-info {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            color: #1890ff;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 500;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            color: var(--primary);
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
            width: 500px;
            height: 380px;
            margin: auto;
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: none;
            padding: 20px;
            height: 80px;
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
            height: 80px;
        }
        
        .input-group-text {
            border-right: none;
            background-color: #f8f9fa !important;
            border-radius: 6px 0 0 6px !important;
            height: 50px;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
        }
        
        .filter-group label {
            margin-right: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        
        .date-range-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .date-input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
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
            
            .modal-content {
                width: 95%;
                height: auto;
                min-height: 250px;
            }
            
            .modal-header,
            .modal-footer {
                height: auto;
                padding: 15px;
            }
            
            .modal-body {
                height: auto;
                padding: 20px;
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
            
            .category-nav ul {
                flex-direction: column;
                gap: 5px;
            }
            
            .category-nav a {
                padding: 5px 10px;
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
            
            .img-avatar {
                width: 40px;
                height: 40px;
            }
            
            .section-title {
                font-size: 16px;
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
                    <li><a href="product.php" class="active"><i class="fas fa-box-open"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
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
                    <h1><i class="fas fa-boxes"></i> Product and Category Management</h1>
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
            
            <!-- Products Section -->
            <div class="section-title">
                <i class="fas fa-box-open"></i> Products Management
            </div>
            
            <!-- Search and Filter Card -->
            <div class="card search-filter-container">
                <form method="get" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($all_categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter == $category) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select class="form-control" name="stock_filter">
                                    <option value="">All Stock</option>
                                    <option value="low" <?php echo ($stock_filter == 'low') ? 'selected' : ''; ?>>Low Stock (<10)</option>
                                    <option value="available" <?php echo ($stock_filter == 'available') ? 'selected' : ''; ?>>Available Stock (≥10)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="product.php" class="btn btn-secondary" style="background-color: #6c757d; color: white; text-decoration: none;">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Category Navigation -->
            <?php if (!empty($categorized_products)): ?>
            <div class="card category-nav">
                <ul>
                    <?php foreach ($categorized_products as $category => $products): ?>
                        <li><a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Products Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>All Products</h3>
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($categorized_products)): ?>
                        <?php foreach ($categorized_products as $category => $products): ?>
                            <h3 id="<?php echo htmlspecialchars($category); ?>" class="section-title">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($category); ?>
                            </h3>
                            
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Buy Price</th>
                                        <th class="text-center">Sell Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1; ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php 
                                        $productDate = date('Y-m-d', strtotime($product['date']));
                                        $isNew = ($productDate == $today) ? true : false;
                                        ?>
                                        <tr class="<?php echo $isNew ? 'new-product' : ''; ?>">
                                            <td><?php echo $count++; ?></td>
                                            <td>
                                                <?php if ($product['media_id'] === '0'): ?>
                                                    <img class="img-avatar" src="uploads/products/no_image.png" alt="">
                                                <?php else: ?>
                                                    <img class="img-avatar" src="uploads/products/<?php echo $product['image']; ?>" alt="">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo remove_junk($product['name']); ?>
                                                <?php if ($isNew): ?>
                                                    <span class="new-badge">NEW</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo max(0, remove_junk($product['quantity'])); ?></td>
                                            <td class="text-center">
                                                <?php if ($product['quantity'] < 10): ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-exclamation-circle"></i> Low Stock
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> In Stock
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">₱<?php echo remove_junk($product['buy_price']); ?></td>
                                            <td class="text-center">₱<?php echo remove_junk($product['sale_price']); ?></td>
                                            <td class="action-buttons">
                                                <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No products found matching your criteria.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Categories Section -->
            <div class="section-title">
                <i class="fas fa-tags"></i> Categories Management
            </div>
            
            <!-- Search and Filter Section -->
            <div class="card search-filter-container">
                <form method="get" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cat_search" placeholder="Search categories..." 
                                       value="<?php echo isset($_GET['cat_search']) ? htmlspecialchars($_GET['cat_search']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="product.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Category List</h3>
                    <button class="btn btn-success" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($all_categories_list)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_categories_list as $category): ?>
                                <tr>
                                    <td><?php echo remove_junk($category['id']); ?></td>
                                    <td><?php echo remove_junk($category['name']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_categorie.php?id=<?php echo (int)$category['id'];?>" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_categorie.php?id=<?php echo (int)$category['id'];?>" 
                                               class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No categories found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Add New Category
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="product.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category-name">
                                <i class="fas fa-tag mr-2"></i>Category Name
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-folder"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="category-name" name="categorie-name" 
                                       placeholder="e.g. Electronics, Clothing, etc." required>
                            </div>
                            <small class="form-text text-muted">
                                Enter a descriptive name for your new category
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" name="add_cat" class="btn btn-primary">
                            <i class="fas fa-check mr-2"></i>Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
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
            
            // Smooth scrolling for category links
            document.querySelectorAll('.category-nav a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href');
                    document.querySelector(target).scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        });
        </script>
        </body>
        </html>
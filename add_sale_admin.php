<?php
$page_title = 'Add Sale';
require_once('includes/load.php');
page_require_level(1);

// Handle form submission
if (isset($_POST['confirm_sale'])) {
    $p_id = $db->escape((int)$_POST['s_id']);
    $s_qty = $db->escape((int)$_POST['quantity']);
    $s_price = $db->escape($_POST['price']);
    $s_total = $s_qty * $s_price;
    $s_date = make_date();

    $sql = "INSERT INTO sales (product_id, qty, price, date, user_id) 
            VALUES ('{$p_id}', '{$s_qty}', '{$s_price}', '{$s_date}', '{$user_id}')";

    if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id);
        $session->msg('s', "Product Bought.");
        redirect('add_sale_admin.php', false);
    } else {
        $session->msg('d', 'Sorry, failed to add!');
        redirect('add_sale.php', false);
    }
}

// Fetch products along with images, categories, and quantity
$sql = "SELECT p.id, p.name, p.sale_price, p.quantity, m.file_name, c.name AS category 
        FROM products p 
        LEFT JOIN media m ON p.media_id = m.id 
        LEFT JOIN categories c ON p.categorie_id = c.id
        ORDER BY c.name";
$products = $db->query($sql);

// Group products by category
$categorized_products = [];
while ($product = $products->fetch_assoc()) {
    $categorized_products[$product['category']][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Add Sale</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
        
        .btn-success {
            background-color: #52c41a;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #3fad09;
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
        
        .category-nav {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1000;
            padding: 15px 0;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .category-nav ul {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .category-nav li {
            list-style: none;
        }
        
        .category-nav a {
            font-size: 14px;
            padding: 8px 12px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            border-radius: 20px;
            transition: all 0.3s;
        }
        
        .category-nav a:hover {
            background-color: var(--primary);
            color: white;
            text-decoration: none;
        }
        
        .category-section {
            scroll-margin-top: 120px;
            margin-bottom: 30px;
        }
        
        .category-section h3 {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-light);
            color: var(--dark);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-card .card-header {
            background-color: var(--light);
            border-bottom: 1px solid var(--gray-light);
            padding: 12px 15px;
        }
        
        .product-card .card-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .product-stock {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 15px;
        }
        
        .stock-available {
            color: #52c41a;
        }
        
        .stock-low {
            color: #faad14;
        }
        
        .stock-out {
            color: #f5222d;
        }
        
        .form-control {
            border-radius: 6px;
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            margin-bottom: 10px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }
        
        .product-actions {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-bullseye"></i> Spring Bullbars</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <!-- Admin Menu Links -->
                    <li><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="sales_report.php"><i class="fa-solid fa-calendar-days"></i>Selacted date Sales</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-cart-plus"></i> Add Sale</h1>
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
            
            <!-- Category Navigation -->
            <div class="card category-nav">
                <div class="card-body">
                    <ul>
                        <?php foreach ($categorized_products as $category => $products): ?>
                            <li><a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Products by Category -->
            <div class="card">
                <div class="card-header">
                    <h3>Available Products</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($categorized_products as $category => $products): ?>
                        <div id="<?php echo htmlspecialchars($category); ?>" class="category-section">
                            <h3><?php echo htmlspecialchars($category); ?></h3>
                            <div class="product-grid">
                                <?php foreach ($products as $product): 
                                    $stock_class = '';
                                    if ($product['quantity'] <= 0) {
                                        $stock_class = 'stock-out';
                                    } elseif ($product['quantity'] <= 10) {
                                        $stock_class = 'stock-low';
                                    } else {
                                        $stock_class = 'stock-available';
                                    }
                                ?>
                                <div class="product-card">
                                    <div class="card-header">
                                        <strong><?php echo remove_junk($product['name']); ?></strong>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($product['file_name'])): ?>
                                            <img src="uploads/products/<?php echo $product['file_name']; ?>" alt="Product Image" class="img-thumbnail product-image">
                                        <?php else: ?>
                                            <img src="uploads/no_image.png" alt="No Image" class="img-thumbnail product-image">
                                        <?php endif; ?>
                                        
                                        <div class="product-price">
                                            Price: <?php echo remove_junk($product['sale_price']); ?> PHP
                                        </div>
                                        
                                        <div class="product-stock <?php echo $stock_class; ?>">
                                            Stock: <?php echo (int)$product['quantity']; ?> units
                                        </div>
                                        
                                        <div class="product-actions">
                                            <form onsubmit="return showConfirmation(this);">
                                                <input type="hidden" name="s_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="price" value="<?php echo $product['sale_price']; ?>">
                                                <input type="number" name="quantity" class="form-control" 
                                                       placeholder="Quantity" min="1" max="<?php echo (int)$product['quantity']; ?>" 
                                                       required <?php echo ($product['quantity'] <= 0) ? 'disabled' : ''; ?>>
                                                <button type="submit" class="btn btn-success w-100" 
                                                    <?php echo ($product['quantity'] <= 0) ? 'disabled' : ''; ?>>
                                                    <?php echo ($product['quantity'] <= 0) ? 'Out of Stock' : 'Bought'; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm Purchase</h4>
                </div>
                <div class="modal-body">
                    <p id="confirmDetails"></p>
                    <form method="post" action="add_sale.php">
                        <input type="hidden" name="s_id" id="confirm_s_id">
                        <input type="hidden" name="quantity" id="confirm_quantity">
                        <input type="hidden" name="price" id="confirm_price">
                        <button type="submit" name="confirm_sale" class="btn btn-success">Confirm</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showConfirmation(form) {
            var productName = form.closest('.product-card').querySelector('.card-header strong').innerText;
            var quantity = form.querySelector('input[name="quantity"]').value;
            var price = form.querySelector('input[name="price"]').value;
            var total = quantity * price;

            document.getElementById('confirmDetails').innerHTML = 
                "Product: " + productName + "<br>" +
                "Quantity: " + quantity + "<br>" +
                "Price: " + price + " PHP<br>" +
                "Total: " + total + " PHP";
                
            document.getElementById('confirm_s_id').value = form.querySelector('input[name="s_id"]').value;
            document.getElementById('confirm_quantity').value = quantity;
            document.getElementById('confirm_price').value = price;

            $('#confirmationModal').modal('show');
            return false;
        }
    </script>
</body>
</html>
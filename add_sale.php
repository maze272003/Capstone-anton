<?php
$page_title = 'Add Sale';
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['confirm_sale'])) {
    $success = true;
    $errors = [];
    
    foreach ($_POST['s_id'] as $index => $p_id) {
        $p_id = $db->escape((int)$p_id);
        $s_qty = $db->escape((int)$_POST['quantity'][$index]);
        $s_price = $db->escape($_POST['price'][$index]);
        
        // Check available stock
        $product = find_by_id('products', $p_id);
        if ((int)$product['quantity'] < $s_qty) {
            $errors[] = "Not enough stock for {$product['name']} (Available: {$product['quantity']}, Requested: {$s_qty})";
            $success = false;
            continue;
        }
        
        $s_total = $s_qty * $s_price;
        $s_date = make_date();

        $sql = "INSERT INTO sales (product_id, qty, price, date, user_id) 
                VALUES ('{$p_id}', '{$s_qty}', '{$s_price}', '{$s_date}', '{$user_id}')";        

        if ($db->query($sql)) {
            update_product_qty($s_qty, $p_id);
        } else {
            $success = false;
            $errors[] = "Failed to record sale for {$product['name']}";
            break;
        }
    }
    
    if ($success) {
        $session->msg('s', "Products Bought.");
    } else {
        $error_msg = 'Failed to add some items!<br>' . implode('<br>', $errors);
        $session->msg('d', $error_msg);
    }
    redirect('add_sale.php', false);
}

if (isset($_POST['buy_now'])) {
    $p_id = $db->escape((int)$_POST['p_id']);
    $s_qty = $db->escape((int)$_POST['quantity']);
    $s_price = $db->escape($_POST['price']);
    
    // Check available stock
    $product = find_by_id('products', $p_id);
    if ((int)$product['quantity'] < $s_qty) {
        $session->msg('d', "Not enough stock for {$product['name']} (Available: {$product['quantity']}, Requested: {$s_qty})");
        redirect('add_sale.php', false);
    }
    
    $s_total = $s_qty * $s_price;
    $s_date = make_date();

    $sql = "INSERT INTO sales (product_id, qty, price, date, user_id) 
            VALUES ('{$p_id}', '{$s_qty}', '{$s_price}', '{$s_date}', '{$user_id}')";        

    if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id);
        $session->msg('s', "Product Bought.");
    } else {
        $session->msg('d', 'Sorry, failed to buy the product!');
    }
    redirect('add_sale.php', false);
}

$sql = "SELECT p.id, p.name, p.sale_price, p.quantity, m.file_name, c.name AS category 
        FROM products p 
        LEFT JOIN media m ON p.media_id = m.id 
        LEFT JOIN categories c ON p.categorie_id = c.id
        ORDER BY c.name";
$products = $db->query($sql);

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
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --danger: #f72585;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            z-index: 100;
            left: 0;
            top: 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--gray);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 240px;
            transition: all 0.3s ease;
            width: calc(100% - 240px);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .header h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 600;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
            margin-left: 15px;
        }
        
        /* Category Navigation */
        .category-nav-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .category-nav {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 5px;
        }
        
        .category-nav::-webkit-scrollbar {
            display: none;
        }
        
        .category-nav a {
            display: block;
            padding: 8px 16px;
            margin-right: 8px;
            text-decoration: none;
            color: var(--gray);
            border-radius: 20px;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .category-nav a:hover, .category-nav a.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        /* Search and Cart */
        .search-cart-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .search-box {
            position: relative;
            flex: 1;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .cart-btn {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .cart-btn:hover {
            background: var(--secondary);
        }
        
        .cart-badge {
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }
        
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }
        
        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 12px;
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 40px;
        }
        
        .product-price {
            color: var(--primary);
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex: 1;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #3a8b8f;
        }
        
        .btn-disabled {
            background-color: var(--light-gray);
            color: var(--gray);
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        /* Stock Status */
        .product-stock {
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .stock-high {
            color: var(--success);
        }
        
        .stock-low {
            color: var(--danger);
            font-weight: bold;
        }
        
        .stock-out {
            color: var(--danger);
            font-weight: bold;
        }
        
        .product-card.low-stock {
            border: 1px solid var(--danger);
            position: relative;
        }
        
        .product-card.low-stock::after {
            content: 'Low Stock';
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .product-card.out-of-stock {
            border: 1px solid var(--danger);
            position: relative;
            opacity: 0.7;
        }
        
        .product-card.out-of-stock::after {
            content: 'Out of Stock';
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Cart Sidebar */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        .cart-sidebar.active {
            right: 0;
        }
        
        .cart-header {
            padding: 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .close-cart {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        
        .cart-body {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        
        .cart-empty {
            text-align: center;
            padding: 40px 0;
            color: var(--gray);
        }
        
        .cart-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .cart-item.invalid {
            border-left: 3px solid var(--danger);
            background-color: rgba(247, 37, 133, 0.05);
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .cart-item-price {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            background: var(--light-gray);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .quantity-input {
            width: 40px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
        }
        
        .remove-item {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 14px;
        }
        
        .cart-footer {
            padding: 15px;
            border-top: 1px solid var(--light-gray);
            background: white;
        }
        
        .cart-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .cart-total {
            font-weight: 600;
            color: var(--primary);
        }
        
        .checkout-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .checkout-btn:disabled {
            background-color: var(--gray) !important;
            cursor: not-allowed !important;
        }
        
        .checkout-btn:hover {
            background: var(--secondary);
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 20px;
            background-color: #4BB543;
            color: white;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification.error {
            background-color: var(--danger);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .cart-sidebar {
                width: 350px;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
            
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
            
            .search-cart-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .cart-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .product-name {
                font-size: 13px;
            }
            
            .product-price {
                font-size: 14px;
            }
            
            .btn {
                padding: 5px 8px;
                font-size: 11px;
            }
            
            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="home_staff.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="add_sale.php" class="active">
                        <i class="fas fa-cart-plus"></i>
                        <span>Product Bought</span>
                    </a>
                </li>
                <li>
                    <a href="product_staff.php">
                        <i class="fas fa-box-open"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="daily_sales.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Sales</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Add New Sale</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name'] ?? 'Staff'); ?>&background=4361ee&color=fff" alt="User">
                    <span><?php echo $user['name'] ?? 'Staff User'; ?></span>
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <?php echo display_msg($msg); ?>

            <!-- Category Navigation -->
            <div class="category-nav-container">
                <div class="category-nav">
                    <?php foreach ($categorized_products as $category => $products): ?>
                        <a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="search-cart-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="productSearch" placeholder="Search products..." onkeyup="searchProducts()">
                    </div>
                    <div class="cart-btn" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart"></i>
                        Add Sale <span class="cart-badge" id="cartCount">0</span>
                    </div>
                </div>
            </div>

            <?php foreach ($categorized_products as $category => $products): ?>
                <div id="<?php echo htmlspecialchars($category); ?>" class="category-section">
                    <h2 style="margin-bottom: 15px; color: var(--primary); font-size: 18px;"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="product-grid">
                        <?php foreach ($products as $product): 
                            $stockClass = '';
                            $stockTextClass = '';
                            $disabled = false;
                            
                            if ($product['quantity'] <= 0) {
                                $stockClass = 'out-of-stock';
                                $stockTextClass = 'stock-out';
                                $disabled = true;
                            } elseif ($product['quantity'] <= 5) {
                                $stockClass = 'low-stock';
                                $stockTextClass = 'stock-low';
                            } else {
                                $stockTextClass = 'stock-high';
                            }
                        ?>
                            <div class="product-card <?php echo $stockClass; ?>">
                                <div class="product-image-container">
                                    <?php if (!empty($product['file_name'])): ?>
                                        <img src="uploads/products/<?php echo $product['file_name']; ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <img src="uploads/no_image.png" alt="No Image" class="product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo remove_junk($product['name']); ?></h3>
                                    <div class="product-stock <?php echo $stockTextClass; ?>">
                                        Stock: <?php echo (int)$product['quantity']; ?>
                                    </div>
                                    <div class="product-price">₱<?php echo remove_junk($product['sale_price']); ?></div>
                                    <div class="product-actions">
                                        <button class="btn <?php echo $disabled ? 'btn-disabled' : 'btn-primary'; ?>" 
                                                <?php echo $disabled ? 'disabled' : ''; ?>
                                                onclick="<?php echo $disabled ? '' : "addToCart('{$product['id']}', '".remove_junk($product['name'])."', {$product['sale_price']}, '".(!empty($product['file_name']) ? $product['file_name'] : 'no_image.png')."', {$product['quantity']})"; ?>">
                                            <i class="fas fa-cart-plus"></i> Add
                                        </button>
                                        <button class="btn <?php echo $disabled ? 'btn-disabled' : 'btn-success'; ?>" 
                                                <?php echo $disabled ? 'disabled' : ''; ?>
                                                onclick="<?php echo $disabled ? '' : "directBuy('{$product['id']}', '".remove_junk($product['name'])."', {$product['sale_price']}, {$product['quantity']})"; ?>">
                                            <i class="fas fa-shopping-bag"></i> Buy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Cart Sidebar -->
    <div class="overlay" id="cartOverlay"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Add Sale List (<span id="sidebarCartCount">0</span>)</h3>
            <button class="close-cart" onclick="toggleCart()">&times;</button>
        </div>
        <div class="cart-body" id="cartItems">
            <div class="cart-empty">
                <i class="fa-solid fa-boxes-stacked" style="font-size: 40px; margin-bottom: 15px; color: #ccc;"></i>
                <p> empty...</p>
            </div>
        </div>
        <div class="cart-footer">
            <div class="cart-summary">
                <span>Total:</span>
                <span class="cart-total">₱<span id="cartTotal">0.00</span></span>
            </div>
            <button class="checkout-btn" onclick="checkoutCart()">CHECKOUT</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    let cart = [];

    if (localStorage.getItem('cart')) {
        cart = JSON.parse(localStorage.getItem('cart'));
        updateCartCount();
    }

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function addToCart(id, name, price, image, stock) {
        // Check if product is already in cart
        const existingItem = cart.find(item => item.id === id);
        const currentQty = existingItem ? existingItem.quantity : 0;
        
        // Check if adding would exceed stock
        if (currentQty >= stock) {
            showNotification(`Cannot add more. Only ${stock} available in stock!`, 'error');
            return;
        }
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: parseFloat(price),
                quantity: 1,
                image: image,
                stock: stock
            });
        }
        saveCart();
        updateCartCount();
        showNotification('Product added to cart!');
    }

    function directBuy(id, name, price, stock) {
        const maxQty = stock;
        const quantity = prompt(`How many ${name} would you like to buy? (Max: ${maxQty})`, '1');
        
        if (quantity === null) return; // User cancelled
        
        const qty = parseInt(quantity);
        
        if (isNaN(qty)) {
            showNotification('Please enter a valid number!', 'error');
            return;
        }
        
        if (qty <= 0) {
            showNotification('Quantity must be at least 1!', 'error');
            return;
        }
        
        if (qty > stock) {
            showNotification(`Cannot order ${qty}. Only ${stock} available in stock!`, 'error');
            return;
        }
        
        // Proceed with purchase
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_sale.php';
        
        const fields = {
            'p_id': id,
            'quantity': qty,
            'price': price,
            'buy_now': 'true'
        };
        
        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    function updateCartCount() {
        const count = cart.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cartCount').textContent = count;
        document.getElementById('sidebarCartCount').textContent = count;
    }

    function toggleCart() {
        const cartSidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartOverlay');
        
        cartSidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        if (cartSidebar.classList.contains('active')) {
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        let cartHtml = '';
        let total = 0;
        let hasInvalidItems = false;
        
        if (cart.length === 0) {
            cartHtml = `
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart" style="font-size: 40px; margin-bottom: 15px; color: #ccc;"></i>
                    <p>Your cart is empty</p>
                </div>
            `;
        } else {
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                const exceedsStock = item.quantity > item.stock;
                if (exceedsStock) hasInvalidItems = true;
                
                cartHtml += `
                    <div class="cart-item ${exceedsStock ? 'invalid' : ''}">
                        <img src="uploads/products/${item.image}" class="cart-item-image" alt="${item.name}">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                            <div class="cart-item-stock" style="font-size: 12px; color: ${exceedsStock ? 'red' : 'green'}; margin-bottom: 5px;">
                                Stock: ${item.stock} | Ordered: ${item.quantity}
                                ${exceedsStock ? ' (Not enough stock!)' : ''}
                            </div>
                            <div class="cart-item-controls">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" 
                                       onchange="updateQuantity(${index}, this.value)" min="1">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                                <button class="remove-item" onclick="removeItem(${index})">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        document.getElementById('cartItems').innerHTML = cartHtml;
        document.getElementById('cartTotal').textContent = total.toFixed(2);
        
        // Disable checkout button if any items exceed stock
        const checkoutBtn = document.querySelector('.checkout-btn');
        checkoutBtn.disabled = hasInvalidItems;
        checkoutBtn.style.opacity = hasInvalidItems ? 0.6 : 1;
        checkoutBtn.style.cursor = hasInvalidItems ? 'not-allowed' : 'pointer';
        
        if (hasInvalidItems) {
            checkoutBtn.title = "Cannot checkout - some items exceed available stock";
        } else {
            checkoutBtn.title = "";
        }
    }

    function updateQuantity(index, qty) {
        qty = parseInt(qty);
        const item = cart[index];
        
        if (isNaN(qty) || qty < 1) {
            showNotification('Quantity must be at least 1!', 'error');
            return;
        }
        
        if (qty > item.stock) {
            showNotification(`Cannot order ${qty}. Only ${item.stock} available in stock!`, 'error');
            return;
        }
        
        item.quantity = qty;
        saveCart();
        updateCartCount();
        updateCartDisplay();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        saveCart();
        updateCartCount();
        updateCartDisplay();
        showNotification('Item removed from cart');
    }

    function checkoutCart() {
        if (cart.length === 0) {
            showNotification('Your cart is empty!', 'error');
            return;
        }
        
        // Check if any items exceed stock
        const invalidItems = cart.filter(item => item.quantity > item.stock);
        if (invalidItems.length > 0) {
            showNotification('Cannot checkout - some items exceed available stock!', 'error');
            return;
        }
        
        if (confirm('Are you sure you want to checkout all items in your cart?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'add_sale.php';
            
            cart.forEach((item, index) => {
                const fields = {
                    [`s_id[${index}]`]: item.id,
                    [`quantity[${index}]`]: item.quantity,
                    [`price[${index}]`]: item.price
                };
                
                for (const [key, value] of Object.entries(fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                                        input.value = value;
                    form.appendChild(input);
                }
            });
            
            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'confirm_sale';
            confirmInput.value = 'true';
            form.appendChild(confirmInput);
            
            document.body.appendChild(form);
            form.submit();
            
            cart = [];
            saveCart();
            updateCartCount();
            toggleCart();
        }
    }

    function searchProducts() {
        const input = document.getElementById('productSearch');
        const filter = input.value.toUpperCase();
        const categories = document.querySelectorAll('.category-section');
        
        categories.forEach(category => {
            const products = category.querySelectorAll('.product-name');
            let hasVisibleProducts = false;
            
            products.forEach(product => {
                const text = product.textContent || product.innerText;
                const productCard = product.closest('.product-card');
                
                if (text.toUpperCase().indexOf(filter) > -1) {
                    productCard.style.display = "";
                    hasVisibleProducts = true;
                } else {
                    productCard.style.display = "none";
                }
            });
            
            category.style.display = hasVisibleProducts ? "" : "none";
        });
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type === 'error' ? 'error' : ''}`;
        notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000);
    }

    // Mobile menu toggle functionality
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    // Close cart when clicking on overlay
    document.getElementById('cartOverlay').addEventListener('click', toggleCart);

    // Initialize cart display
    updateCartDisplay();

    // Smooth scrolling for category navigation
    document.querySelectorAll('.category-nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Highlight active category on scroll
    window.addEventListener('scroll', function() {
        const categoryLinks = document.querySelectorAll('.category-nav a');
        const scrollPosition = window.scrollY + 100;
        
        document.querySelectorAll('.category-section').forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                const categoryId = section.getAttribute('id');
                
                categoryLinks.forEach(link => {
                    if (link.getAttribute('href') === `#${categoryId}`) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
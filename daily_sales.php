<?php
$page_title = 'Daily Sales';
require_once('includes/load.php');
page_require_level(3);

$year  = date('Y');
$month = date('m');
$sales = dailySales($year, $month);

// Initialize cart if not exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add to cart functionality
if(isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Check if product exists in inventory
    $product = find_by_id('products', $product_id);
    if($product) {
        // Check if product already in cart
        $found = false;
        foreach($_SESSION['cart'] as &$item) {
            if($item['id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if(!$found) {
            $_SESSION['cart'][] = array(
                'id' => $product_id,
                'name' => $product['name'],
                'quantity' => $quantity,
                'sale_price' => $product['sale_price']
            );
        }
        
        $session->msg('s', "Product added to cart");
    } else {
        $session->msg('d', "Product not found");
    }
}

// Remove from cart functionality
if(isset($_GET['remove_from_cart'])) {
    $product_id = (int)$_GET['remove_from_cart'];
    
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Reindex array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    $session->msg('s', "Product removed from cart");
}

// Calculate cart total
$cart_total = 0;
foreach($_SESSION['cart'] as $item) {
    $cart_total += $item['quantity'] * $item['sale_price'];
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
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 100;
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
            margin-left: 240px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        /* Cart Sidebar Styles */
        .cart-sidebar {
            width: 320px;
            background: white;
            box-shadow: -5px 0 15px rgba(0,0,0,0.05);
            padding: 20px;
            height: 100vh;
            position: fixed;
            right: -320px;
            top: 0;
            transition: right 0.3s;
            z-index: 99;
            overflow-y: auto;
        }
        
        .cart-sidebar.active {
            right: 0;
        }
        
        .cart-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .cart-sidebar-header h3 {
            color: var(--primary);
            font-size: 18px;
        }
        
        .close-cart {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
        }
        
        .cart-items {
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: var(--gray);
            font-size: 14px;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }
        
        .cart-item-quantity input {
            width: 40px;
            text-align: center;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            padding: 5px;
        }
        
        .remove-item {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .cart-total {
            padding: 15px 0;
            border-top: 1px solid var(--light-gray);
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 15px;
        }
        
        .cart-total h4 {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
        }
        
        .cart-total-amount {
            color: var(--primary);
            font-weight: 600;
        }
        
        .cart-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-checkout {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-checkout:hover {
            background-color: var(--secondary);
        }
        
        .btn-continue {
            background-color: var(--light-gray);
            color: var(--dark);
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-continue:hover {
            background-color: #d1d7e0;
        }
        
        .cart-toggle {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background-color: var(--primary);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 98;
        }
        
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
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
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        /* Sales Chart Section */
        .chart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            font-size: 18px;
            color: var(--dark);
        }
        
        /* Total Sales Display */
        .total-sales {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .total-sales h3 {
            color: var(--gray);
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .total-sales h2 {
            color: var(--primary);
            font-size: 28px;
            font-weight: 600;
        }
        
        /* Search Bar Styles */
        .search-container {
            position: relative;
            width: 250px;
            margin-left: auto;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        /* Category Navigation */
        .category-nav {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 15px 0;
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .category-nav::-webkit-scrollbar {
            display: none;
        }
        
        .category-nav a {
            display: block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            color: var(--gray);
            border-radius: 20px;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .category-nav a:hover, 
        .category-nav a.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        /* Checkout Modal */
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 20px;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            border-top: 1px solid var(--light-gray);
            padding: 15px 20px;
        }
        
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 180px;
            background-color: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .product-image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-price {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid var(--light-gray);
            border-radius: 5px;
            text-align: center;
        }
        
        .add-to-cart {
            flex: 1;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .add-to-cart:hover {
            background-color: var(--secondary);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h3, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu a {
                justify-content: center;
                padding: 15px 0;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .chart-container {
                height: 300px;
            }
            
            .search-container {
                width: 100%;
                margin: 15px 0;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .cart-sidebar {
                width: 280px;
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
                    <a href="add_sale.php">
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
                    <a href="daily_sales.php" class="active">
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
                <h1>Daily Sales Report</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name'] ?? 'Staff'); ?>&background=4361ee&color=fff" alt="User">
                    <span><?php echo $user['name'] ?? 'Staff User'; ?></span>
                </div>
            </div>

            <?php echo display_msg($msg); ?>

            <!-- Search Bar -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search products..." id="productSearch">
            </div>

            <!-- Category Navigation -->
            <div class="category-nav" id="categoryNav">
                <a href="#demo" class="active">Demo</a>
                <a href="#external">External</a>
                <a href="#finished-goods">Finished Goods</a>
                <a href="#raw-materials">Raw Materials</a>
                <a href="#spare-parts">Spare Parts</a>
            </div>

            <!-- Sales Chart -->
            <div class="chart-section">
                <div class="section-header">
                    <h2>Daily Sales for <?php echo date('F Y'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="total-sales">
                <h3>Total Sales This Month</h3>
                <h2>₱<?php echo number_format(array_sum(array_column($sales, 'total_saleing_price')), 2); ?></h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#checkoutModal" style="margin-top: 15px;">
                    <i class="fas fa-shopping-cart"></i> Checkout
                </button>
            </div>
        </main>

        <!-- Cart Sidebar -->
        <aside class="cart-sidebar" id="cartSidebar">
            <div class="cart-sidebar-header">
                <h3>Your Cart</h3>
                <button class="close-cart" id="closeCart">&times;</button>
            </div>
            
            <div class="cart-items">
                <?php if(empty($_SESSION['cart'])): ?>
                    <p>Your cart is empty</p>
                <?php else: ?>
                    <?php foreach($_SESSION['cart'] as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?php echo $item['name']; ?></div>
                                <div class="cart-item-price">₱<?php echo number_format($item['sale_price'], 2); ?></div>
                            </div>
                            <div class="cart-item-quantity">
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" 
                                    onchange="updateCartQuantity(<?php echo $item['id']; ?>, this.value)">
                            </div>
                            <button class="remove-item" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="cart-total">
                <h4>Total: <span class="cart-total-amount">₱<?php echo number_format($cart_total, 2); ?></span></h4>
            </div>
            
            <div class="cart-actions">
                <button class="btn-checkout" data-toggle="modal" data-target="#checkoutModal" onclick="closeCart()">
                    Proceed to Checkout
                </button>
                <button class="btn-continue" onclick="closeCart()">
                    Continue Shopping
                </button>
            </div>
        </aside>

        <!-- Cart Toggle Button -->
        <div class="cart-toggle" id="cartToggle">
            <i class="fas fa-shopping-cart"></i>
            <?php if(count($_SESSION['cart']) > 0): ?>
                <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Confirm Checkout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to proceed with checkout?</p>
                    <p>Total Amount: ₱<?php echo number_format($cart_total, 2); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processCheckout()">Confirm Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('dailySalesChart').getContext('2d');
            const chartContainer = document.querySelector('.chart-container');
            
            // Set fixed dimensions
            chartContainer.style.height = '400px';
            chartContainer.style.width = '100%';
            
            // Create chart
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [<?php foreach ($sales as $sale) { echo "'" . $sale['date'] . "',"; } ?>],
                    datasets: [{
                        label: 'Daily Sales (₱)',
                        data: [<?php foreach ($sales as $sale) { echo $sale['total_saleing_price'] . ","; } ?>],
                        borderColor: 'rgba(67, 97, 238, 1)',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Poppins',
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleFont: {
                                size: 14,
                                family: 'Poppins'
                            },
                            bodyFont: {
                                size: 12,
                                family: 'Poppins'
                            },
                            padding: 12,
                            cornerRadius: 4,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    family: 'Poppins',
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Sales Amount (₱)',
                                font: {
                                    family: 'Poppins',
                                    size: 12
                                }
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0,0,0,0.05)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Cart functionality
            const cartSidebar = document.getElementById('cartSidebar');
            const cartToggle = document.getElementById('cartToggle');
            const closeCartBtn = document.getElementById('closeCart');
            
            cartToggle.addEventListener('click', function() {
                cartSidebar.classList.add('active');
            });
            
            closeCartBtn.addEventListener('click', function() {
                cartSidebar.classList.remove('active');
            });
            
            // Smooth category navigation
            document.querySelectorAll('.category-nav a').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Update active state
                    document.querySelectorAll('.category-nav a').forEach(a => {
                        a.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Scroll to section
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

            // Search functionality
            document.getElementById('productSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                // Implement your search logic here
                console.log('Searching for:', searchTerm);
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                chart.resize();
            });
        });

        function openCart() {
            document.getElementById('cartSidebar').classList.add('active');
        }
        
        function closeCart() {
            document.getElementById('cartSidebar').classList.remove('active');
        }
        
        function removeFromCart(productId) {
            window.location.href = '?remove_from_cart=' + productId;
        }
        
        function updateCartQuantity(productId, quantity) {
            if(quantity < 1) quantity = 1;
            
            // Submit form to update quantity
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'product_id';
            inputId.value = productId;
            form.appendChild(inputId);
            
            const inputQty = document.createElement('input');
            inputQty.type = 'hidden';
            inputQty.name = 'quantity';
            inputQty.value = quantity;
            form.appendChild(inputQty);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function processCheckout() {
            // Implement your checkout logic here
            alert('Checkout completed successfully!');
            
            // Clear the cart after checkout
            fetch('clear_cart.php')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        window.location.reload();
                    }
                });
            
            $('#checkoutModal').modal('hide');
        }
    </script>
</body>
</html>
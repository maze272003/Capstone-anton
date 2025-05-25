<?php
$page_title = 'Add Sale';
require_once('includes/load.php');
page_require_level(3);

// Handle form submission
if (isset($_POST['confirm_sale'])) {
    $success = true;
    
    // Loop through all items in the cart
    foreach ($_POST['s_id'] as $index => $p_id) {
        $p_id = $db->escape((int)$p_id);
        $s_qty = $db->escape((int)$_POST['quantity'][$index]);
        $s_price = $db->escape($_POST['price'][$index]);
        $s_total = $s_qty * $s_price;
        $s_date = make_date();

        $sql = "INSERT INTO sales (product_id, qty, price, date, user_id) 
                VALUES ('{$p_id}', '{$s_qty}', '{$s_price}', '{$s_date}', '{$user_id}')";        

        if ($db->query($sql)) {
            update_product_qty($s_qty, $p_id);
        } else {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $session->msg('s', "Products Bought.");
    } else {
        $session->msg('d', 'Sorry, failed to add some items!');
    }
    redirect('add_sale.php', false);
}

// Fetch products along with images and categories
$sql = "SELECT p.id, p.name, p.sale_price, m.file_name, c.name AS category 
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
            margin-left: auto;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            width: 200px;
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
        
        /* Product Grid - Shopee Style */
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
            padding-top: 100%; /* 1:1 Aspect Ratio */
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
        
        /* Floating Cart Sidebar */
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
        
        .checkout-btn:hover {
            background: var(--secondary);
        }
        
        /* Overlay when cart is open */
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
            
            .search-cart-container {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .cart-btn {
                width: 100%;
                justify-content: center;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
            
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
        }
        
        @media (max-width: 576px) {
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
                        Cart <span class="cart-badge" id="cartCount">0</span>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <?php foreach ($categorized_products as $category => $products): ?>
                <div id="<?php echo htmlspecialchars($category); ?>" class="category-section">
                    <h2 style="margin-bottom: 15px; color: var(--primary); font-size: 18px;"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <?php if (!empty($product['file_name'])): ?>
                                        <img src="uploads/products/<?php echo $product['file_name']; ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <img src="uploads/no_image.png" alt="No Image" class="product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo remove_junk($product['name']); ?></h3>
                                    <div class="product-price">₱<?php echo remove_junk($product['sale_price']); ?></div>
                                    <div class="product-actions">
                                        <button class="btn btn-primary" onclick="addToCart('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>, '<?php echo !empty($product['file_name']) ? $product['file_name'] : 'no_image.png'; ?>')">
                                            <i class="fas fa-cart-plus"></i> Add
                                        </button>
                                        <button class="btn btn-success" onclick="buyNow('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>)">
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
            <h3>Shopping Cart (<span id="sidebarCartCount">0</span>)</h3>
            <button class="close-cart" onclick="toggleCart()">&times;</button>
        </div>
        <div class="cart-body" id="cartItems">
            <div class="cart-empty">
                <i class="fas fa-shopping-cart" style="font-size: 40px; margin-bottom: 15px; color: #ccc;"></i>
                <p>Your cart is empty</p>
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

    <script>
    let cart = [];

    // Load cart from localStorage if available
    if (localStorage.getItem('cart')) {
        cart = JSON.parse(localStorage.getItem('cart'));
        updateCartCount();
    }

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function addToCart(id, name, price, image) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: parseFloat(price),
                quantity: 1,
                image: image
            });
        }
        saveCart();
        updateCartCount();
        updateCartDisplay();
        showNotification('Product added to cart!');
        
        // Show cart automatically if it's hidden
        if (!document.getElementById('cartSidebar').classList.contains('active')) {
            toggleCart();
        }
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
                cartHtml += `
                    <div class="cart-item">
                        <img src="uploads/products/${item.image}" class="cart-item-image" alt="${item.name}">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
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
    }

    function updateQuantity(index, qty) {
        qty = parseInt(qty);
        if (qty < 1) {
            removeItem(index);
            return;
        }
        cart[index].quantity = qty;
        saveCart();
        updateCartCount();
        updateCartDisplay();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        saveCart();
        updateCartCount();
        updateCartDisplay();
    }

    function clearCart() {
        if (confirm('Are you sure you want to clear your cart?')) {
            cart = [];
            saveCart();
            updateCartCount();
            updateCartDisplay();
        }
    }

    function checkoutCart() {
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        
        if (confirm('Are you sure you want to checkout all items in your cart?')) {
            // Create a single form for all items
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'add_sale.php';
            
            // Add each item as an array to the form
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
            
            // Add the confirm_sale field
            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'confirm_sale';
            confirmInput.value = 'true';
            form.appendChild(confirmInput);
            
            // Submit the form and clear the cart
            document.body.appendChild(form);
            form.submit();
            
            // Clear the cart and show success message
            cart = [];
            saveCart();
            updateCartCount();
            toggleCart();
        }
    }

    function buyNow(id, name, price) {
        // Clear cart first
        cart = [];
        // Add the single item
        addToCart(id, name, price, '');
        // Immediately checkout
        checkoutCart();
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
            
            // Show/hide category based on visible products
            category.style.display = hasVisibleProducts ? "" : "none";
        });
    }

    // Highlight active category in navigation when scrolling
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

    // Smooth scroll for category links
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

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.padding = '15px 20px';
        notification.style.backgroundColor = '#4BB543';
        notification.style.color = 'white';
        notification.style.borderRadius = '4px';
        notification.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
        notification.style.display = 'flex';
        notification.style.alignItems = 'center';
        notification.style.gap = '10px';
        notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000);
    }

    // Close cart when clicking overlay
    document.getElementById('cartOverlay').addEventListener('click', toggleCart);
    </script>
</body>
</html>
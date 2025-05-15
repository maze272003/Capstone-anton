<?php
$page_title = 'All Products';
require_once('includes/load.php');
page_require_level(1);

// Initialize shopping cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if (array_key_exists($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $session->msg('s', "Product added to cart");
    redirect('products.php', false);
}

// Handle remove from cart action
if (isset($_GET['remove_from_cart'])) {
    $product_id = (int)$_GET['remove_from_cart'];
    if (array_key_exists($product_id, $_SESSION['cart'])) {
        unset($_SESSION['cart'][$product_id]);
        $session->msg('s', "Product removed from cart");
    }
    redirect('products.php', false);
}

// Handle clear cart action
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = array();
    $session->msg('s', "Cart cleared");
    redirect('products.php', false);
}

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

include_once('layouts/header.php');
?>

<style>
    .category-nav {
        position: fixed;
        top: 80px;
        width: 60%;
        background: #f8f9fa;
        z-index: 1000;
        padding: 5px 0;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }
    .category-nav ul {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
    }
    .category-nav li {
        list-style: none;
        margin: 0 10px;
    }
    .category-nav a {
        font-size: 14px;
        padding: 5px 10px;
        text-decoration: none;
        color: #333;
    }
    .category-nav a:hover {
        text-decoration: underline;
    }
    .content-wrapper {
        margin-top: 50px;
    }
    .new-product {
        background-color: #fffde7 !important;
        border-left: 4px solid #ffc107 !important;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { background-color: #fffde7; }
        50% { background-color: #fff9c4; }
        100% { background-color: #fffde7; }
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
    .search-filter-container {
        background: #f8f9fa;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .cart-container {
        position: fixed;
        right: 20px;
        top: 100px;
        width: 300px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 500px;
        overflow-y: auto;
    }
    .cart-item {
        border-bottom: 1px solid #eee;
        padding: 8px 0;
    }
    .cart-total {
        font-weight: bold;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
    }
    .quantity-input {
        width: 60px;
        display: inline-block;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>

    <!-- Shopping Cart -->
    <div class="cart-container">
        <h4>Shopping Cart <small>(<?php echo count($_SESSION['cart']); ?> items)</small></h4>
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity): 
                $product = find_by_id('products', $product_id);
                $subtotal = $product['sale_price'] * $quantity;
                $total += $subtotal;
            ?>
                <div class="cart-item">
                    <strong><?php echo $product['name']; ?></strong><br>
                    Qty: <?php echo $quantity; ?> × ₱<?php echo $product['sale_price']; ?> = ₱<?php echo number_format($subtotal, 2); ?>
                    <a href="products.php?remove_from_cart=<?php echo $product_id; ?>" class="btn btn-xs btn-danger pull-right" title="Remove">
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>
                </div>
            <?php endforeach; ?>
            <div class="cart-total">
                Total: ₱<?php echo number_format($total, 2); ?>
            </div>
            <div class="text-center" style="margin-top: 10px;">
                <a href="checkout.php" class="btn btn-success btn-sm">Checkout</a>
                <a href="products.php?clear_cart" class="btn btn-danger btn-sm">Clear Cart</a>
            </div>
        <?php else: ?>
            <p class="text-center">Your cart is empty</p>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Section -->
    <div class="col-md-12 search-filter-container">
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
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <ul class="nav navbar-nav">
            <?php foreach ($categorized_products as $category => $products): ?>
                <li><a href="#<?php echo htmlspecialchars($category); ?>"> <?php echo htmlspecialchars($category); ?> </a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="col-md-12 content-wrapper">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="text-center">All Products</h3>
            </div>

            <div class="panel-body">
                <?php if (!empty($categorized_products)): ?>
                    <?php foreach ($categorized_products as $category => $products): ?>
                        <h3 id="<?php echo htmlspecialchars($category); ?>"> <?php echo htmlspecialchars($category); ?> </h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Photo</th>
                                    <th>Product Title</th>
                                    <th class="text-center" style="width: 10%;">In-Stock</th>
                                    <th class="text-center" style="width: 15%;">Stock Status</th>
                                    <th class="text-center" style="width: 10%;">Buying Price</th>
                                    <th class="text-center" style="width: 10%;">Selling Price</th>
                                    <th class="text-center" style="width: 10%;">Product Added</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
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
                                        <td class="text-center"> <?php echo $count++; ?> </td>
                                        <td>
                                            <?php if ($product['media_id'] === '0'): ?>
                                                <img class="img-avatar img-circle" src="uploads/products/no_image.png" alt="">
                                            <?php else: ?>
                                                <img class="img-avatar img-circle" src="uploads/products/<?php echo $product['image']; ?>" alt="">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($product['name']); ?>
                                            <?php if ($isNew): ?>
                                                <span class="new-badge">NEW TODAY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"> <?php echo max(0, remove_junk($product['quantity'])); ?> </td>
                                        <td class="text-center">
                                            <?php if ($product['quantity'] < 10): ?>
                                                <span class="label label-danger">⚠️ Needed Re-stock</span>
                                            <?php else: ?>
                                                <span class="label label-success">✔️ In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">₱<?php echo remove_junk($product['buy_price']); ?></td>
                                        <td class="text-center">₱<?php echo remove_junk($product['sale_price']); ?></td>
                                        <td class="text-center"> <?php echo read_date($product['date']); ?> </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <form method="post" action="products.php" style="display: inline;">
                                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="quantity-input">
                                                    <button type="submit" name="add_to_cart" class="btn btn-success btn-xs" title="Add to Cart">
                                                        <span class="glyphicon glyphicon-shopping-cart"></span>
                                                    </button>
                                                </form>
                                                <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-info btn-xs" title="Edit">
                                                    <span class="glyphicon glyphicon-edit"></span>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-danger btn-xs" title="Delete">
                                                    <span class="glyphicon glyphicon-trash"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No products found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.category-nav a').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            let targetId = this.getAttribute('href').substring(1);
            let targetElement = document.getElementById(targetId);
            let navbarHeight = document.querySelector('.category-nav').offsetHeight;

            window.scrollTo({
                top: targetElement.offsetTop - navbarHeight - -70,
                behavior: 'smooth'
            });
        });
    });
</script>

<?php include_once('layouts/footer.php'); ?>
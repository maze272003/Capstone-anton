<?php
$page_title = 'All Products';
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
    .action-icons {
        font-size: 1.2em;
    }
    .action-icons a {
        margin: 0 3px;
        padding: 5px 8px;
        border-radius: 4px;
    }
    .action-icons a:hover {
        opacity: 0.8;
    }
    .btn-edit {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    .btn-delete {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
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
                                    <th class="text-center" style="width: 120px;">Actions</th>
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
                                        <td class="text-center action-icons">
                                            <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn-delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
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
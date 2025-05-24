<?php
$page_title = 'All Products';
require_once('includes/load.php');
page_require_level(2);

$products = join_product_table();

// Group products by category
$categorized_products = [];
foreach ($products as $product) {
    $categorized_products[$product['categorie']][] = $product;
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
        
        /* Search and Filter */
        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
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
        
        /* Product Table */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        th {
            background-color: var(--light);
            color: var(--gray);
            font-weight: 500;
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        /* Status Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .badge-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        /* Action Buttons */
        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #3d8b40;
            transform: translateY(-2px);
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
            
            .filter-container {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
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
                    <a href="product_staff.php" class="active">
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
                <h1>Product Inventory</h1>
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
                        <a href="#<?php echo htmlspecialchars($category); ?>" class="<?php echo $category === array_key_first($categorized_products) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="productSearch" placeholder="Search products...">
                </div>
            </div>

            <!-- Product Tables by Category -->
            <?php foreach ($categorized_products as $category => $products): ?>
                <div class="table-container" id="<?php echo htmlspecialchars($category); ?>">
                    <div class="section-header">
                        <h2><?php echo htmlspecialchars($category); ?></h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Buy Price</th>
                                <th>Sell Price</th>
                                <!-- <th>Actions</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo count_id(); ?></td>
                                    <td>
                                        <?php if ($product['media_id'] === '0'): ?>
                                            <img class="product-img" src="uploads/products/no_image.png" alt="No Image">
                                        <?php else: ?>
                                            <img class="product-img" src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo remove_junk($product['name']); ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo remove_junk($product['name']); ?></td>
                                    <td><?php echo remove_junk($product['quantity']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($product['quantity'] < 10) ? 'badge-danger' : 'badge-success'; ?>">
                                            <?php echo ($product['quantity'] < 10) ? 'Low Stock' : 'In Stock'; ?>
                                        </span>
                                    </td>
                                    <td>₱<?php echo remove_junk($product['buy_price']); ?></td>
                                    <td>₱<?php echo remove_junk($product['sale_price']); ?></td>
                                    <!-- <td>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                        <button class="btn btn-success">
                                            <i class="fas fa-shopping-cart"></i> Buy
                                        </button>
                                    </td> -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </main>
    </div>

    <script>
    $(document).ready(function() {
        // Category navigation smooth scrolling
        $('.category-nav a').on('click', function(e) {
            e.preventDefault();
            var target = $(this.hash);
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
                $('.category-nav a').removeClass('active');
                $(this).addClass('active');
            }
        });
        
        // Product search functionality
        $('#productSearch').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $('.table-container tbody tr').each(function() {
                var productName = $(this).find('td:nth-child(3)').text().toLowerCase();
                if (productName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Hide empty categories
            $('.table-container').each(function() {
                var visibleRows = $(this).find('tbody tr:visible').length;
                if (visibleRows > 0) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Highlight active category on scroll
        $(window).on('scroll', function() {
            var scrollPosition = $(window).scrollTop();
            
            $('.table-container').each(function() {
                var sectionTop = $(this).offset().top - 100;
                var sectionBottom = sectionTop + $(this).outerHeight();
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    var id = $(this).attr('id');
                    $('.category-nav a').removeClass('active');
                    $('.category-nav a[href="#' + id + '"]').addClass('active');
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// [Previous PHP code remains exactly the same until the style section]
?>

<?php include_once('layouts/header.php'); ?>
<style>
    :root {
        --primary: #4a6bff;
        --primary-dark: #3a5bef;
        --secondary: #f8f9fa;
        --accent: #ff6b6b;
        --text: #333;
        --light-text: #777;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f7ff;
        color: var(--text);
    }
    
    /* Modern sidebar */
    #sidebar {
        background: linear-gradient(180deg, #2c3e50, #1a2634);
        color: white;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    #sidebar .sidebar-header {
        background: rgba(0, 0, 0, 0.2);
        padding: 20px;
        text-align: center;
    }
    
    #sidebar ul.components {
        padding: 20px 0;
    }
    
    #sidebar ul li a {
        padding: 12px 25px;
        color: rgba(255, 255, 255, 0.8);
        display: block;
        transition: var(--transition);
        border-left: 3px solid transparent;
    }
    
    #sidebar ul li a:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
        border-left: 3px solid var(--primary);
    }
    
    /* Main content area */
    .content-wrapper {
        margin-left: 250px;
        padding: 25px;
        transition: var(--transition);
    }
    
    /* Modern navigation bar */
    .nav-container {
        background: white;
        border-radius: 12px;
        padding: 15px 25px;
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .category-links {
        display: flex;
        overflow-x: auto;
        scrollbar-width: none;
        gap: 8px;
    }
    
    .category-links::-webkit-scrollbar {
        display: none;
    }
    
    .category-links a {
        white-space: nowrap;
        padding: 8px 16px;
        background: #f0f2ff;
        color: var(--primary);
        border-radius: 20px;
        font-weight: 500;
        font-size: 14px;
        transition: var(--transition);
    }
    
    .category-links a:hover, 
    .category-links a.active {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }
    
    /* Search and cart area - now properly circled and not overlapping */
    .nav-tools {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .search-container {
        position: relative;
    }
    
    .search-container input {
        padding: 8px 15px 8px 35px;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        width: 220px;
        outline: none;
        transition: var(--transition);
    }
    
    .search-container input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.2);
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--light-text);
    }
    
    .cart-btn {
        display: flex;
        align-items: center;
        background: var(--primary);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
    }
    
    .cart-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .cart-btn i {
        margin-right: 8px;
    }
    
    .cart-badge {
        background: var(--accent);
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
    
    /* Product cards */
    .category-section {
        scroll-margin-top: 100px;
        margin-bottom: 40px;
    }
    
    .section-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--text);
        position: relative;
        padding-bottom: 10px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: var(--primary);
    }
    
    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        margin-bottom: 25px;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .product-header {
        background: var(--primary);
        color: white;
        padding: 15px;
        font-weight: 600;
    }
    
    .product-body {
        padding: 20px;
        text-align: center;
    }
    
    .product-image {
        width: 100%;
        height: 180px;
        object-fit: contain;
        margin-bottom: 15px;
        border-radius: 8px;
    }
    
    .product-price {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin: 15px 0;
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn {
        border: none;
        border-radius: 8px;
        padding: 8px 15px;
        font-weight: 500;
        transition: var(--transition);
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
    }
    
    .btn-primary {
        background: var(--primary);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-success:hover {
        background: #218838;
    }
    
    /* Cart modal */
    #cartModal .modal-dialog {
        max-width: 550px;
    }
    
    #cartModal .modal-content {
        border-radius: 12px;
        overflow: hidden;
        border: none;
    }
    
    #cartModal .modal-header {
        background: var(--primary);
        color: white;
        border: none;
    }
    
    #cartModal .modal-body {
        max-height: 400px;
        overflow-y: auto;
        padding: 0;
    }
    
    .cart-item {
        display: grid;
        grid-template-columns: 80px 1fr auto;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        gap: 15px;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .cart-item-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        color: var(--primary);
        font-weight: 500;
    }
    
    .cart-quantity-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cart-quantity {
        width: 50px;
        text-align: center;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-top: 1px solid #eee;
    }
    
    .cart-total {
        font-size: 1.2em;
        font-weight: 600;
        color: var(--primary);
    }
    
    /* Responsive design */
    @media (max-width: 992px) {
        .content-wrapper {
            margin-left: 0;
            padding: 15px;
        }
        
        #sidebar {
            margin-left: -250px;
        }
        
        .nav-container {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }
        
        .category-links {
            order: 1;
        }
        
        .nav-tools {
            order: 2;
            width: 100%;
        }
        
        .search-container {
            flex-grow: 1;
        }
        
        .search-container input {
            width: 100%;
        }
    }
</style>

<!-- Modern Navigation Bar with Search and Cart -->
<div class="nav-container">
    <div class="category-links" id="categoryLinks">
        <?php foreach ($categorized_products as $category => $products): ?>
            <a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a>
        <?php endforeach; ?>
    </div>
    
    <div class="nav-tools">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="productSearch" placeholder="Search products..." onkeyup="searchProducts()">
        </div>
        <div class="cart-btn" onclick="showCart()">
            <i class="fas fa-shopping-cart"></i>
            Cart <span class="cart-badge" id="cartCount">0</span>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<div id="cartModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shopping Cart</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cartItems">
                <!-- Cart items will be displayed here -->
            </div>
            <div class="modal-footer">
                <div class="cart-total">
                    Total: ₱<span id="cartTotal">0.00</span>
                </div>
                <button type="button" class="btn btn-danger" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Clear
                </button>
                <button type="button" class="btn btn-success" onclick="checkoutCart()">
                    <i class="fas fa-check"></i> Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="content-wrapper">
    <?php foreach ($categorized_products as $category => $products): ?>
        <div id="<?php echo htmlspecialchars($category); ?>" class="category-section">
            <h3 class="section-title"><?php echo htmlspecialchars($category); ?></h3>
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-4">No products available in this category</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="product-card">
                                <div class="product-header">
                                    <strong><?php echo remove_junk($product['name']); ?></strong>
                                </div>
                                <div class="product-body">
                                    <?php if (!empty($product['file_name'])): ?>
                                        <img src="uploads/products/<?php echo $product['file_name']; ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <img src="uploads/no_image.png" alt="No Image" class="product-image">
                                    <?php endif; ?>
                                    <div class="product-price">₱<?php echo remove_junk($product['sale_price']); ?></div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" onclick="addToCart('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>, '<?php echo !empty($product['file_name']) ? $product['file_name'] : 'no_image.png'; ?>')">
                                            <i class="fas fa-cart-plus"></i> Add
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="buyNow('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>)">
                                            <i class="fas fa-bolt"></i> Buy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// [JavaScript code remains exactly the same as in your original]
// Added smooth scrolling for category navigation
document.querySelectorAll('.category-links a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
            
            // Update active category
            document.querySelectorAll('.category-links a').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

// Highlight active category when scrolling
window.addEventListener('scroll', function() {
    const scrollPosition = window.scrollY + 100;
    
    document.querySelectorAll('.category-section').forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        
        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            const categoryId = section.getAttribute('id');
            
            document.querySelectorAll('.category-links a').forEach(link => {
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

<?php include_once('layouts/footer.php'); ?>
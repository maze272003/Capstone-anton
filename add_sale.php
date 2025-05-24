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

<?php include_once('layouts/header.php'); ?>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }
    .category-section {
    scroll-margin-top: 20px; /* Binawasan ang margin para hindi masyadong malayo */
    }
    .category-nav {
        width: 100%;
        background: #f8f9fa;
        padding: 15px 0;
        margin-bottom: 20px;
        box-shadow: 0px 2px 5px rgba(41, 90, 174, 0.1);
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .category-nav a {
        font-size: 14px;
        padding: 8px 15px;
        text-decoration: none;
        color: #333;
        font-weight: bold;
        background: white;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .category-nav a:hover {
        background: #4361ee;
        color: white;
        text-decoration: none;
    }
    .category-links {
        display: flex;
        overflow-x: auto;
        padding: 5px 0;
        scrollbar-width: none;
        flex-grow: 1;
    }
    .category-links::-webkit-scrollbar {
        display: none;
    }
    .category-links a {
        white-space: nowrap;
        font-size: 14px;
        padding: 8px 15px;
        margin-right: 5px;
        text-decoration: none;
        color: #333;
        font-weight: 600;
        background: #f5f5f5;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .category-links a:hover {
        background: #4361ee;
        color: white;
    }
    .category-links a.active {
        background: #4361ee;
        color: white;
    }
    .nav-right-section {
        display: flex;
        align-items: center;
    }
    .search-container {
        display: flex;
        align-items: center;
        position: relative;
        margin-right: 15px;
    }
    .search-container input {
        padding: 8px 15px 8px 35px;
        border: 1px solid #ddd;
        border-radius: 20px;
        width: 200px;
        outline: none;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        color: #777;
    }
    .header-cart {
        display: flex;
        align-items: center;
        background: #4361ee;
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .header-cart:hover {
        background: #3a56d4;
    }
    .header-cart i {
        margin-right: 8px;
    }
    .cart-badge {
        background: #f44336;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 12px;
        font-weight: bold;
        margin-left: 5px;
    }
    .content-wrapper {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }
    .product-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
    }
    /* Cart Modal Styles */
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
        border-radius: 4px;
    }
    .cart-item-details {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .cart-item-name {
        font-weight: bold;
        margin-bottom: 8px;
    }
    .cart-item-price {
        color: #4361ee;
        font-size: 0.95em;
        margin-bottom: 12px;
    }
    .cart-quantity-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .cart-quantity {
        width: 50px;
        text-align: center;
        padding: 4px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .cart-total {
        font-size: 1.2em;
        font-weight: bold;
        color: #4361ee;
        padding: 10px 15px;
    }
    #cartModal .modal-dialog {
        max-width: 500px;
    }
    #cartModal .modal-body {
        max-height: 400px;
        overflow-y: auto;
        padding: 0;
    }
    #cartModal .modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
    }
    .cart-total {
        font-size: 1.2em;
        font-weight: bold;
        margin-right: 15px;
    }
    #cartModal .modal-dialog {
        max-width: 500px;
    }
    #cartModal .modal-body {
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<div class="category-nav text-center">
    <?php foreach ($categorized_products as $category => $products): ?>
        <a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a>
    <?php endforeach; ?>
</div>
<!-- Cart Icon -->
<div class="cart-icon" onclick="showCart()">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-badge" id="cartCount">0</span>
</div>

<!-- Cart Modal -->
<div id="cartModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Shopping Cart</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="cartItems">
                <!-- Cart items will be displayed here -->
            </div>
            <div class="modal-footer">
                <div class="cart-total">
                    Total: ₱<span id="cartTotal">0.00</span>
                </div>
                <button type="button" class="btn btn-danger" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Clear Cart
                </button>
                <button type="button" class="btn btn-success" onclick="checkoutCart()">
                    <i class="fas fa-check"></i> Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Product Panel -->
<div class="panel-body">
    <?php foreach ($categorized_products as $category => $products): ?>
        <div id="<?php echo htmlspecialchars($category); ?>" class="category-section">
            <h3><?php echo htmlspecialchars($category); ?></h3>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 text-center" style="margin-bottom: 20px;">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <strong><?php echo remove_junk($product['name']); ?></strong>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($product['file_name'])): ?>
                                    <img src="uploads/products/<?php echo $product['file_name']; ?>" alt="Product Image" class="img-thumbnail product-image">
                                <?php else: ?>
                                    <img src="uploads/no_image.png" alt="No Image" class="img-thumbnail product-image">
                                <?php endif; ?>
                                <p>Price: ₱<?php echo remove_junk($product['sale_price']); ?></p>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" onclick="addToCart('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>, '<?php echo !empty($product['file_name']) ? $product['file_name'] : 'no_image.png'; ?>')">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="buyNow('<?php echo $product['id']; ?>', '<?php echo remove_junk($product['name']); ?>', <?php echo $product['sale_price']; ?>)">
                                        <i class="fas fa-shopping-bag"></i> Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
let cart = [];

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
    updateCartCount();
    showNotification('Product added to cart!');
}

function updateCartCount() {
    document.getElementById('cartCount').textContent = cart.reduce((total, item) => total + item.quantity, 0);
}

function showCart() {
    let cartHtml = '';
    let total = 0;
    
    if (cart.length === 0) {
        cartHtml = '<p class="text-center">Walang laman ang iyong cart</p>';
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
                        <div class="cart-quantity-controls">
                            <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                            <input type="number" class="cart-quantity" value="${item.quantity}" 
                                   onchange="updateQuantity(${index}, this.value)" min="1">
                            <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                            <button class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    document.getElementById('cartItems').innerHTML = cartHtml;
    document.getElementById('cartTotal').textContent = total.toFixed(2);
    $('#cartModal').modal('show');
}

function updateQuantity(index, qty) {
    qty = parseInt(qty);
    if (qty < 1) {
        removeItem(index);
        return;
    }
    cart[index].quantity = qty;
    updateCartCount();
    showCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartCount();
    showCart();
}

function clearCart() {
    if (confirm('Sigurado ka bang gusto mong i-clear ang cart?')) {
        cart = [];
        updateCartCount();
        showCart();
    }
}

function checkoutCart() {
    if (cart.length === 0) {
        alert('Walang laman ang iyong cart!');
        return;
    }
    
    if (confirm('Sigurado ka bang gusto mong i-checkout ang lahat ng items sa cart?')) {
        // Gumawa ng iisang form para sa lahat ng items
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_sale.php';
        
        // Idagdag ang bawat item bilang array sa form
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
        
        // Idagdag ang confirm_sale field
        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirm_sale';
        confirmInput.value = 'true';
        form.appendChild(confirmInput);
        
        // I-submit ang form at i-clear ang cart
        document.body.appendChild(form);
        form.submit();
        
        // I-clear ang cart at ipakita ang success message
        cart = [];
        updateCartCount();
        $('#cartModal').modal('hide');
        showNotification('Matagumpay na na-checkout ang mga produkto!');
    }
}

function addSelectedToCart() {
    const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
    if (selectedProducts.length === 0) {
        alert('Walang napiling produkto!');
        return;
    }
    
    selectedProducts.forEach(checkbox => {
        const productData = JSON.parse(checkbox.value);
        addToCart(productData.id, productData.name, productData.price, productData.image);
        checkbox.checked = false;
    });
    
    showNotification(`${selectedProducts.length} (na) produkto ang idinagdag sa cart!`);
}

function selectAll(checked) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = checked);
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.padding = '15px 20px';
    notification.style.borderRadius = '4px';
    notification.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php include_once('layouts/footer.php'); ?>
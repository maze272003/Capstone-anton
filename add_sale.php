<?php
$page_title = 'ssAdd Sale';
require_once('includes/load.php');
page_require_level(3);

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
        redirect('add_sale.php', false);
    } else {
        $session->msg('d', 'Sorry, failed to add!');
        redirect('add_sale.php', false);
    }
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
    .category-section {
    scroll-margin-top: 120px; /* Adjust this value based on the nav height */
}
    .category-nav {
        position: fixed;
        top: 70px;
        width: 50%;
        background: #f8f9fa;
        z-index: 1000;
        padding: 10px 0;
        box-shadow: 0px 2px 5px rgba(41, 90, 174, 0.1);
    }
    .category-nav ul {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
    }
    .category-nav li {
        list-style: none;
        margin: 0 15px;
    }
    .category-nav a {
        font-size: 14px;
        padding: 8px 12px;
        text-decoration: none;
        color: #333;
        font-weight: bold;
    }
    .category-nav a:hover {
        text-decoration: underline;
    }
    .content-wrapper {
        margin-top: 60px;
    }
    .product-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
    }
</style>

<div class="category-nav text-center">
    <?php foreach ($categorized_products as $category => $products): ?>
        <a href="#<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></a>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Products</span>
                </strong>
            </div>
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
                                            <p>Price: <?php echo remove_junk($product['sale_price']); ?> PHP</p>
                                            <form onsubmit="return showConfirmation(this);">
                                                <input type="hidden" name="s_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="price" value="<?php echo $product['sale_price']; ?>">
                                                <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
                                                <button type="submit" class="btn btn-success" style="margin-top: 10px;">Bought</button>
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

<script>
function showConfirmation(form) {
    var productName = form.parentElement.parentElement.querySelector('strong').innerText;
    var quantity = form.querySelector('input[name="quantity"]').value;
    var price = form.querySelector('input[name="price"]').value;
    var total = quantity * price;

    document.getElementById('confirmDetails').innerHTML = "Product: " + productName + "<br>Quantity: " + quantity + "<br>Price: " + price + " PHP<br>Total: " + total + " PHP";
    document.getElementById('confirm_s_id').value = form.querySelector('input[name="s_id"]').value;
    document.getElementById('confirm_quantity').value = quantity;
    document.getElementById('confirm_price').value = price;

    $('#confirmationModal').modal('show');
    return false;
}
</script>

<?php include_once('layouts/footer.php'); ?>






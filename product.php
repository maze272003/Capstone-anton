<?php
$page_title = 'All Products';
require_once('includes/load.php');
page_require_level(1);

$products = join_product_table();

// Group products by category
$categorized_products = [];
foreach ($products as $product) {
    $categorized_products[$product['categorie']][] = $product;
}

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

</style>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <ul class="nav navbar-nav">
            <?php foreach ($categorized_products as $category => $products) : ?>
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
                <?php if (!empty($categorized_products)) : ?>
                    <?php foreach ($categorized_products as $category => $products) : ?>
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
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product) : ?>
                                    <tr>
                                        <td class="text-center"> <?php echo count_id(); ?> </td>
                                        <td>
                                            <?php if ($product['media_id'] === '0') : ?>
                                                <img class="img-avatar img-circle" src="uploads/products/no_image.png" alt="">
                                            <?php else : ?>
                                                <img class="img-avatar img-circle" src="uploads/products/<?php echo $product['image']; ?>" alt="">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo remove_junk($product['name']); ?></td>
                                        <td class="text-center"> <?php echo max(0, remove_junk($product['quantity'])); ?> </td>
                                        <td class="text-center">
                                            <?php if ($product['quantity'] < 10) : ?>
                                                <span class="label label-danger">⚠️ Needed Re-stock</span>
                                            <?php else : ?>
                                                <span class="label label-success">✔️ In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">₱<?php echo remove_junk($product['buy_price']); ?></td>
                                        <td class="text-center">₱<?php echo remove_junk($product['sale_price']); ?></td>
                                        <td class="text-center"> <?php echo read_date($product['date']); ?> </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-info btn-xs" title="Edit" data-toggle="tooltip">
                                                    <span class="glyphicon glyphicon-edit"></span>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-danger btn-xs" title="Delete" data-toggle="tooltip">
                                                    <span class="glyphicon glyphicon-trash"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="text-center">No products available.</p>
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
                top: targetElement.offsetTop - navbarHeight - -70, // Offset to prevent overlap
                behavior: 'smooth'
            });
        });
    });
</script>

<?php include_once('layouts/footer.php'); ?>






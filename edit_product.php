<?php
$page_title = 'Edit Product';
require_once('includes/load.php');

// Check user permission
page_require_level(2);

// Fetch product and related data
$product = find_by_id('products', (int)$_GET['id']);
$all_categories = find_all('categories');

if (!$product) {
    $session->msg("d", "Missing product ID.");
    redirect('product.php');
}

if (isset($_POST['product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-quantity', 'buying-price', 'saleing-price');
    validate_fields($req_fields);

    if (empty($errors)) {
        $p_name = remove_junk($db->escape($_POST['product-title']));
        $p_cat = (int)$_POST['product-categorie'];
        $p_qty = remove_junk($db->escape($_POST['product-quantity']));
        $p_buy = remove_junk($db->escape($_POST['buying-price']));
        $p_sale = remove_junk($db->escape($_POST['saleing-price']));
        $media_id = $product['media_id']; // Keep existing image unless new one is uploaded

        // Handle Image Upload
        if (!empty($_FILES['product-photo-upload']['name'])) {
            $photo_name = time() . "_" . basename($_FILES['product-photo-upload']['name']);
            $upload_path = "uploads/products/" . $photo_name;

            if (move_uploaded_file($_FILES['product-photo-upload']['tmp_name'], $upload_path)) {
                $query = "INSERT INTO media (file_name) VALUES ('{$photo_name}')";
                if ($db->query($query)) {
                    $media_id = $db->insert_id();
                } else {
                    $session->msg('d', 'Image upload failed!');
                    redirect('edit_product.php?id=' . $product['id'], false);
                }
            }
        }

        // Update Product
        $query = "UPDATE products SET ";
        $query .= "name ='{$p_name}', quantity ='{$p_qty}', ";
        $query .= "buy_price ='{$p_buy}', sale_price ='{$p_sale}', ";
        $query .= "categorie_id ='{$p_cat}', media_id='{$media_id}' ";
        $query .= "WHERE id ='{$product['id']}'";

        $result = $db->query($query);
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Product updated successfully!");
            redirect('product.php', false);
        } else {
            $session->msg('d', 'Failed to update product.');
            redirect('edit_product.php?id=' . $product['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_product.php?id=' . $product['id'], false);
    }
}

include_once('layouts/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong>
                <span class="glyphicon glyphicon-th"></span>
                <span>Edit Product</span>
            </strong>
        </div>
        <div class="panel-body">
            <div class="col-md-7">
                <form method="post" action="edit_product.php?id=<?php echo (int)$product['id']; ?>" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-th-large"></i></span>
                            <input type="text" class="form-control" name="product-title" value="<?php echo remove_junk($product['name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product-categorie">Category</label>
                        <select class="form-control" name="product-categorie" required>
                            <option value="">Select a category</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo (int)$cat['id']; ?>" <?php if ($product['categorie_id'] == $cat['id']) echo "selected"; ?>>
                                    <?php echo remove_junk($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Image Upload with Preview -->
                    <div class="form-group text-center">
                        <label for="product-photo-upload">Upload New Product Photo</label>
                        <br>
                        <img id="image-preview" src="<?php echo $product['media_id'] ? 'uploads/products/' . find_by_id('media', $product['media_id'])['file_name'] : 'uploads/products/placeholder.png'; ?>" 
                            alt="Product Image" width="150" height="150" style="border: 1px solid #ccc; padding: 5px; margin-bottom: 10px;">
                        <input type="file" class="form-control" name="product-photo-upload" id="product-photo-upload" accept="image/*">
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="product-quantity">Quantity</label>
                                <input type="number" class="form-control" name="product-quantity" value="<?php echo remove_junk($product['quantity']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="buying-price">Buying Price</label>
                                <input type="number" class="form-control" name="buying-price" value="<?php echo remove_junk($product['buy_price']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="saleing-price">Selling Price</label>
                                <input type="number" class="form-control" name="saleing-price" value="<?php echo remove_junk($product['sale_price']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="product" class="btn btn-danger">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Image Preview -->
<script>
document.getElementById("product-photo-upload").addEventListener("change", function(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById("image-preview").src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
});
</script>

<?php include_once('layouts/footer.php'); ?>



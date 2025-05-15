<?php
$page_title = 'Add Product';
require_once('includes/load.php');

// Check user permission level
page_require_level(2);

// Fetch categories
$all_categories = find_all('categories');

$photo_name = ''; // Initialize variable

if (isset($_POST['add_product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-quantity', 'buying-price', 'saleing-price');
    validate_fields($req_fields);

    if (empty($errors)) {
        $p_name = remove_junk($db->escape($_POST['product-title']));
        $p_cat = remove_junk($db->escape($_POST['product-categorie']));
        $p_qty = remove_junk($db->escape($_POST['product-quantity']));
        $p_buy = remove_junk($db->escape($_POST['buying-price']));
        $p_sale = remove_junk($db->escape($_POST['saleing-price']));
        $date = make_date();

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
                    redirect('add_product.php', false);
                }
            }
        } else {
            $media_id = '0'; // No image uploaded
        }

        // Insert Product
        $query = "INSERT INTO products (name, quantity, buy_price, sale_price, categorie_id, media_id, date) ";
        $query .= "VALUES ('{$p_name}', '{$p_qty}', '{$p_buy}', '{$p_sale}', '{$p_cat}', '{$media_id}', '{$date}') ";
        $query .= "ON DUPLICATE KEY UPDATE name='{$p_name}'";

        if ($db->query($query)) {
            $session->msg('s', "Product added successfully!");
            redirect('add_product.php', false);
        } else {
            $session->msg('d', 'Failed to add product.');
            redirect('product.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_product.php', false);
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
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Add New Product</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="col-md-12">
                    <form method="post" action="add_product.php" enctype="multipart/form-data" class="clearfix">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-th-large"></i></span>
                                <input type="text" class="form-control" name="product-title" placeholder="Product Title" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control" name="product-categorie" required>
                                        <option value="">Select Product Category</option>
                                        <?php foreach ($all_categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id'] ?>"><?php echo $cat['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="product-photo-upload">Upload New Product Photo</label>
                            <input type="file" class="form-control" name="product-photo-upload" onchange="previewImage(event)">
                            <br>
                            <img id="image-preview" src="" style="max-width: 200px; display: none;">
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-shopping-cart"></i></span>
                                        <input type="number" class="form-control" name="product-quantity" placeholder="Product Quantity" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">₱</span>
                                        <input type="number" class="form-control" name="buying-price" placeholder="Buying Price" required>
                                        <span class="input-group-addon">.00</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">₱</span>
                                        <input type="number" class="form-control" name="saleing-price" placeholder="Selling Price" required>
                                        <span class="input-group-addon">.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_product" class="btn btn-danger">Add Product</button>
                    </form>
                    
                    <!-- Display uploaded image -->
                    <?php if (!empty($photo_name)): ?>
                        <div style="margin-top: 20px;">
                            <h4>Uploaded Image:</h4>
                            <img src="uploads/products/<?php echo $photo_name; ?>" width="200">
                            <p><strong>Filename:</strong> <?php echo $photo_name; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('image-preview');
            output.src = reader.result;
            output.style.display = "block";
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

<?php include_once('layouts/footer.php'); ?>





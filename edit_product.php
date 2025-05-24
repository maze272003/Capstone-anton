<?php
$page_title = 'Edit Product';
require_once('includes/load.php');

// Check user permission
page_require_level(2);

// Check if user is logged in properly
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

// Get current user data
$current_user = current_user();

// Verify user exists and has required level
if (empty($current_user) || !isset($current_user['user_level'])) {
    $session->msg('d', 'User data not found!');
    redirect('index.php', false);
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Edit Product</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #3a56d4 0%, #2a3eb1 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 25px;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
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
        
        .user-profile .user-info {
            margin-right: 15px;
            text-align: right;
        }
        
        .user-profile .user-info .name {
            font-weight: 500;
            font-size: 14px;
        }
        
        .user-profile .user-info .role {
            font-size: 12px;
            color: var(--gray);
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: none;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            color: var(--dark);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #e01a6f;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        
        .col-md-7 {
            width: 58.3333%;
            padding: 0 15px;
        }
        
        .col-md-4 {
            width: 33.3333%;
            padding: 0 15px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #52c41a;
        }
        
        .alert-danger {
            background-color: #fff1f0;
            border: 1px solid #ffa39e;
            color: #f5222d;
        }
        
        .input-group {
            display: flex;
            margin-bottom: 20px;
        }
        
        .input-group-addon {
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 6px 0 0 6px;
            display: flex;
            align-items: center;
        }
        
        .input-group .form-control {
            border-radius: 0 6px 6px 0;
        }
        
        #image-preview {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 5px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            object-fit: cover;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
<div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-bullseye"></i> Spring Bullbars</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                
                        <!-- Admin Menu Links -->
                        <li><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                        
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                        <li><a href="sales_report.php"><i class="fa-solid fa-calendar-days"></i>Selacted date Sales</a></li>
                        <!-- <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li> -->
                    
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-box"></i> Edit Product</h1>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="name"><?php echo isset($current_user['name']) ? remove_junk(ucfirst($current_user['name'])) : 'Guest'; ?></div>
                        <div class="role"><?php echo isset($current_user['group_name']) ? remove_junk(ucfirst($current_user['group_name'])) : 'Unknown'; ?></div>
                    </div>
                    <img src="uploads/users/<?php echo isset($current_user['image']) ? $current_user['image'] : 'default.jpg'; ?>" alt="User Image">
                </div>
            </div>
            
            <?php echo display_msg($msg); ?>
            
            <div class="row">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Product Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-md-7">
                            <form method="post" action="edit_product.php?id=<?php echo (int)$product['id']; ?>" enctype="multipart/form-data">
                                
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fas fa-box"></i></span>
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
                                    <label for="product-photo-upload">Product Photo</label>
                                    <br>
                                    <img id="image-preview" src="<?php echo $product['media_id'] ? 'uploads/products/' . find_by_id('media', $product['media_id'])['file_name'] : 'uploads/products/placeholder.png'; ?>" 
                                        alt="Product Image" width="200" height="200">
                                    <input type="file" class="form-control" name="product-photo-upload" id="product-photo-upload" accept="image/*" style="margin-top: 10px;">
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
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Highlight current page in sidebar
            $('.sidebar-menu a').each(function() {
                if (window.location.href.indexOf($(this).attr('href')) > -1) {
                    $(this).addClass('active');
                }
            });
            
            // Image preview functionality
            document.getElementById("product-photo-upload").addEventListener("change", function(event) {
                const reader = new FileReader();
                reader.onload = function(){
                    document.getElementById("image-preview").src = reader.result;
                };
                if (event.target.files[0]) {
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        });
    </script>
</body>
</html>
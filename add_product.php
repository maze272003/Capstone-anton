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

// Get current user data
$user = current_user();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Add Product</title>
    
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
        
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
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
        
        .btn {
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #e61a6b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .input-group {
            display: flex;
            align-items: center;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .input-group-addon {
            padding: 12px 15px;
            background-color: #f5f7fb;
            border: 1px solid #ddd;
            border-radius: 6px 0 0 6px;
            font-size: 14px;
        }
        
        .input-group .form-control {
            border-radius: 0 6px 6px 0;
            border-left: none;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 15px;
        }
        
        .col-md-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
            padding: 0 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        #image-preview {
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-top: 10px;
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
                    <?php if(isset($user['user_level']) && $user['user_level'] === '1'): ?>
                        <!-- Admin Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                        <li><a href="categorie.php"><i class="fas fa-tags"></i> Categories</a></li>
                        <li><a href="product.php" class="active"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                        <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '2'): ?>
                        <!-- Special User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php" class="active"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '3'): ?>
                        <!-- User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php" class="active"><i class="fas fa-box-open"></i> Products</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-box-open"></i> Add New Product</h1>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="name"><?php echo isset($user['name']) ? remove_junk(ucfirst($user['name'])) : 'Guest'; ?></div>
                        <div class="role"><?php echo isset($user['group_name']) ? remove_junk(ucfirst($user['group_name'])) : 'Unknown'; ?></div>
                    </div>
                    <img src="uploads/users/<?php echo isset($user['image']) ? $user['image'] : 'default.jpg'; ?>" alt="User Image">
                </div>
            </div>
            
            <?php echo display_msg($msg); ?>
            
            <!-- Add Product Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Product Information</h3>
                </div>
                <div class="card-body" style="padding: 30px;">
                    <form method="post" action="add_product.php" enctype="multipart/form-data" class="clearfix">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product-title">Product Name</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fas fa-tag"></i></span>
                                        <input type="text" class="form-control" name="product-title" placeholder="Product Title" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="product-categorie">Category</label>
                                    <select class="form-control" name="product-categorie" required>
                                        <option value="">Select Product Category</option>
                                        <?php foreach ($all_categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id'] ?>"><?php echo $cat['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="product-photo-upload">Product Image</label>
                                    <input type="file" class="form-control" name="product-photo-upload" onchange="previewImage(event)">
                                    <img id="image-preview" src="" style="max-width: 200px; display: none; margin-top: 10px;">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Details</label>
                                    <div class="row">
                                        <div class="col-md-12" style="margin-bottom: 15px;">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fas fa-cubes"></i></span>
                                                <input type="number" class="form-control" name="product-quantity" placeholder="Product Quantity" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="margin-bottom: 15px;">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fas fa-money-bill-wave"></i></span>
                                                <input type="number" class="form-control" name="buying-price" placeholder="Buying Price" required>
                                                <span class="input-group-addon">.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fas fa-money-bill-wave"></i></span>
                                                <input type="number" class="form-control" name="saleing-price" placeholder="Selling Price" required>
                                                <span class="input-group-addon">.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" name="add_product" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                            <a href="product.php" class="btn btn-danger" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                    
                    <!-- Display uploaded image -->
                    <?php if (!empty($photo_name)): ?>
                        <div style="margin-top: 20px;">
                            <h4>Uploaded Image:</h4>
                            <img src="uploads/products/<?php echo $photo_name; ?>" width="200" style="border-radius: 6px;">
                            <p><strong>Filename:</strong> <?php echo $photo_name; ?></p>
                        </div>
                    <?php endif; ?>
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
        });
        
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
</body>
</html>
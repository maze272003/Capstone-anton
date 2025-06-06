<?php
$page_title = 'Edit User';
require_once('includes/load.php');

// Check if user is logged in properly
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

// Get current user data
$user = current_user();

// Verify user exists and has required level
if (empty($user) || !isset($user['user_level'])) {
    $session->msg('d', 'User data not found!');
    redirect('index.php', false);
}

page_require_level(1);

$e_user = find_by_id('users', (int)$_GET['id']);
$groups = find_all('user_groups');
if (!$e_user) {
    $session->msg("d", "Missing user ID.");
    redirect('users.php');
}
?>

<?php
// Update User basic info
if (isset($_POST['update'])) {
    $req_fields = array('name', 'username', 'email', 'level');
    validate_fields($req_fields);
    if (empty($errors)) {
        $id = (int)$e_user['id'];
        $name = remove_junk($db->escape($_POST['name']));
        $username = remove_junk($db->escape($_POST['username']));
        $email = remove_junk($db->escape($_POST['email']));
        $level = (int)$db->escape($_POST['level']);
        $status = remove_junk($db->escape($_POST['status']));
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->msg('d', 'Invalid email format');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        }
        
        // Check if email exists (other than current user)
        $sql = "SELECT * FROM users WHERE email='{$email}' AND id!='{$id}'";
        $result = $db->query($sql);
        if($db->num_rows($result) > 0) {
            $session->msg('d', 'Email already exists');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        }
        
        $sql = "UPDATE users SET name='{$name}', username='{$username}', email='{$email}', user_level='{$level}', status='{$status}' WHERE id='{$id}'";
        $result = $db->query($sql);
        
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Account Updated");
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_user.php?id='.(int)$e_user['id'], false);
    }
}
?>

<?php
// Update user password
if (isset($_POST['update-pass'])) {
    $req_fields = array('password');
    validate_fields($req_fields);
    if (empty($errors)) {
        $id = (int)$e_user['id'];
        $password = remove_junk($db->escape($_POST['password']));
        $h_pass = sha1($password);

        $sql = "UPDATE users SET password='{$h_pass}' WHERE id='{$id}'";
        $result = $db->query($sql);

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "User password has been updated");
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        } else {
            $session->msg('d', 'Sorry, failed to update user password!');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_user.php?id='.(int)$e_user['id'], false);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Edit User</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    
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
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #3a56d4 0%, #2a3eb1 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-250px);
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
        
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }
        
        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
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
            flex-wrap: wrap;
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
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--gray);
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .col {
            flex: 1;
            min-width: 250px;
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
        
        #password-requirements {
            margin: 10px 0 20px;
            padding-left: 20px;
            font-size: 13px;
        }
        
        #password-requirements li {
            margin-bottom: 5px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-active {
            background-color: #52c41a;
        }
        
        .status-inactive {
            background-color: #f5222d;
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-250px);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .card-header h3 {
                margin-bottom: 10px;
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .user-profile .user-info {
                text-align: left;
                margin-right: 0;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .page-title h1 {
                font-size: 20px;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i> Menu
        </button>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-bullseye"></i> Spring Bullbars</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="transaction_history.php"><i class="fas fa-history"></i> Transaction History</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1><i class="fas fa-user-edit"></i> Edit User</h1>
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
            
            <div class="row">
                <!-- Update User Info -->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3>Update <?php echo remove_junk(ucwords($e_user['name'])); ?> Account</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="edit_user.php?id=<?php echo (int)$e_user['id']; ?>" class="clearfix">
                                <div class="form-group">
                                    <label for="name" class="control-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo remove_junk(ucwords($e_user['name'])); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="username" class="control-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo remove_junk(ucwords($e_user['username'])); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email" class="control-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo isset($e_user['email']) ? remove_junk($e_user['email']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="level">User Role</label>
                                    <select class="form-control" name="level">
                                        <?php foreach ($groups as $group) : ?>
                                            <option <?php if ($group['group_level'] === $e_user['user_level']) echo 'selected="selected"'; ?> value="<?php echo $group['group_level']; ?>">
                                                <?php echo ucwords($group['group_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" name="status">
                                        <option <?php if ($e_user['status'] === '1') echo 'selected="selected"'; ?> value="1">
                                            <span class="status-indicator status-active"></span> Active
                                        </option>
                                        <option <?php if ($e_user['status'] === '0') echo 'selected="selected"'; ?> value="0">
                                            <span class="status-indicator status-inactive"></span> Inactive
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group clearfix">
                                    <button type="submit" name="update" class="btn btn-info">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3>Change <?php echo remove_junk(ucwords($e_user['name'])); ?> Password</h3>
                        </div>
                        <div class="card-body">
                            <form action="edit_user.php?id=<?php echo (int)$e_user['id']; ?>" method="post" class="clearfix">
                                <div class="form-group">
                                    <label for="password" class="control-label">Password</label>
                                    <input type="password" id="password" class="form-control" name="password" placeholder="Enter new password">
                                </div>

                                <!-- Password Requirements -->
                                <ul id="password-requirements">
                                    <li id="length-check">❌ At least 8 characters</li>
                                    <li id="special-char-check">❌ At least one special character (!@#$%^&*)</li>
                                    <li id="number-check">❌ At least one number (0-9)</li>
                                </ul>

                                <div class="form-group clearfix">
                                    <button type="submit" name="update-pass" id="update-pass-btn" class="btn btn-danger" disabled>Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('expanded');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(event.target) && event.target !== mobileMenuToggle) {
                        sidebar.classList.remove('show');
                        mainContent.classList.remove('expanded');
                    }
                }
            });
            
            // Highlight current page in sidebar
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Password validation
            const passwordInput = document.getElementById("password");
            const updateButton = document.getElementById("update-pass-btn");
            const lengthCheck = document.getElementById("length-check");
            const specialCharCheck = document.getElementById("special-char-check");
            const numberCheck = document.getElementById("number-check");

            passwordInput.addEventListener("input", function() {
                const password = passwordInput.value;
                let validLength = password.length >= 8;
                let hasSpecialChar = /[!@#$%^&*]/.test(password);
                let hasNumber = /\d/.test(password);

                lengthCheck.innerHTML = validLength ? "✅ At least 8 characters" : "❌ At least 8 characters";
                specialCharCheck.innerHTML = hasSpecialChar ? "✅ At least one special character (!@#$%^&*)" : "❌ At least one special character (!@#$%^&*)";
                numberCheck.innerHTML = hasNumber ? "✅ At least one number (0-9)" : "❌ At least one number (0-9)";

                updateButton.disabled = !(validLength && hasSpecialChar && hasNumber);
            });
        });
    </script>
</body>
</html>
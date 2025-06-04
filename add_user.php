<?php
$page_title = 'Add User';
require_once('includes/load.php');
page_require_level(1);
$groups = find_all('user_groups');

// Get current user data
$current_user = current_user();

function is_valid_password($password) {
    return preg_match('/^(?=.*[!@#$%^&*])(?=.*\d).{8,}$/', $password);
}

if(isset($_POST['add_user'])){
    $req_fields = array('full-name','username','email','password','level');
    validate_fields($req_fields);

    if(empty($errors)){
        $name = remove_junk($db->escape($_POST['full-name']));
        $username = remove_junk($db->escape($_POST['username']));
        $email = remove_junk($db->escape($_POST['email']));
        $password = remove_junk($db->escape($_POST['password']));
        $user_level = (int)$db->escape($_POST['level']);

        if (is_username_exist($username)) {
            $session->msg('d', 'Username already exists. Please choose another.');
            redirect('add_user.php', false);
        }

        if (is_email_exist($email)) {
            $session->msg('d', 'Email already exists. Please use a different email address.');
            redirect('add_user.php', false);
        }

        $password = sha1($password);
        $query = "INSERT INTO users (";
        $query .="name,username,email,password,user_level,status,image";
        $query .=") VALUES (";
        $query .="'{$name}', '{$username}', '{$email}', '{$password}', '{$user_level}', '1', ''";
        $query .=")";

        if($db->query($query)){
            $session->msg('s', "User account has been created!");
            redirect('add_user.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to create account!');
            redirect('add_user.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_user.php', false);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Add User</title>
    
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
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
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
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .password-requirements {
            margin-top: 10px;
            padding-left: 20px;
            color: var(--gray);
            font-size: 13px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            list-style-type: none;
            position: relative;
            padding-left: 25px;
        }
        
        .password-requirements li:before {
            content: "❌";
            position: absolute;
            left: 0;
        }
        
        .password-requirements li.valid:before {
            content: "✔️";
            color: #52c41a;
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
            
            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .page-title h1 {
                font-size: 20px;
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
                    <h1><i class="fas fa-user-plus"></i> Add New User</h1>
                </div>
                <?php if(isset($current_user)): ?>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="name"><?php echo remove_junk(ucfirst($current_user['name'])); ?></div>
                        <div class="role"><?php echo isset($current_user['group_name']) ? remove_junk(ucfirst($current_user['group_name'])) : 'Unknown'; ?></div>
                    </div>
                    <img src="uploads/users/<?php echo isset($current_user['image']) ? $current_user['image'] : 'default.jpg'; ?>" alt="User Image">
                </div>
                <?php endif; ?>
            </div>
            
            <?php echo display_msg($msg); ?>
            
            <!-- Add User Card -->
            <div class="card">
                <div class="card-header">
                    <h3>User Information</h3>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
                <div class="card-body">
                    <form method="post" action="add_user.php" id="user-form">
                        <div class="form-group">
                            <label for="full-name">Full Name</label>
                            <input type="text" class="form-control" name="full-name" placeholder="Enter full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Enter username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Enter password" required>
                            <ul class="password-requirements" id="password-requirements">
                                <li id="length-check">At least 8 characters</li>
                                <li id="special-char-check">Contains at least one special character (!@#$%^&*)</li>
                                <li id="number-check">Contains at least one number (0-9)</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <label for="level">User Role</label>
                            <select class="form-control" name="level" required>
                                <?php foreach ($groups as $group ):?>
                                <option value="<?php echo $group['group_level'];?>"><?php echo ucwords($group['group_name']);?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="add_user" id="add-user-btn" class="btn btn-primary" disabled>
                                <i class="fas fa-save"></i> Create User
                            </button>
                        </div>
                    </form>
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
            const passwordInput = document.getElementById('password');
            const addUserBtn = document.getElementById('add-user-btn');
            
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    // Check password requirements
                    const lengthValid = password.length >= 8;
                    const specialCharValid = /[!@#$%^&*]/.test(password);
                    const numberValid = /\d/.test(password);
                    
                    // Update requirement indicators
                    document.getElementById('length-check').classList.toggle('valid', lengthValid);
                    document.getElementById('special-char-check').classList.toggle('valid', specialCharValid);
                    document.getElementById('number-check').classList.toggle('valid', numberValid);
                    
                    // Enable/disable submit button
                    if (lengthValid && specialCharValid && numberValid) {
                        addUserBtn.disabled = false;
                    } else {
                        addUserBtn.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>
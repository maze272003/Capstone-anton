<?php
    $page_title = 'All Users';
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
    $all_users = find_all_user();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel - User</title>
        
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
            
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .table th, .table td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .table th {
                font-weight: 500;
                color: var(--gray);
                text-transform: uppercase;
                font-size: 12px;
            }
            
            .table tr:hover {
                background-color: #f9f9f9;
            }
            
            .badge {
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 500;
            }
            
            .badge-success {
                background-color: #e6f7f0;
                color: #00a854;
            }
            
            .badge-danger {
                background-color: #fff1f0;
                color: #f5222d;
            }
            
            .action-buttons .btn {
                padding: 5px 10px;
                font-size: 12px;
                margin-right: 5px;
            }
            
            .btn-warning {
                background-color: #fff7e6;
                color: #fa8c16;
                border: 1px solid #ffd591;
            }
            
            .btn-danger {
                background-color: #fff1f0;
                color: #f5222d;
                border: 1px solid #ffa39e;
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
        <?php include_once('sidebar.php'); ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Top Bar -->
                <div class="top-bar">
                    <div class="page-title">
                        <h1><i class="fas fa-users"></i> Users</h1>
                    </div>
                    <div class="user-profile">
                        <div class="user-info">
                            <div class="name"><?php echo isset($user['name']) ? remove_junk(ucfirst($user['name'])) : 'Guest'; ?></div>
                            
                        </div>
                        <img src="uploads/users/<?php echo isset($user['image']) ? $user['image'] : 'default.jpg'; ?>" alt="User Image">
                    </div>
                </div>
                
                <?php echo display_msg($msg); ?>
                
                <!-- Users Table Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Users</h3>
                        <a href="add_user.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($all_users as $a_user): ?>
                                <tr>
                                    <td><?php echo count_id();?></td>
                                    <td><?php echo remove_junk(ucwords($a_user['name']))?></td>
                                    <td><?php echo remove_junk(ucwords($a_user['username']))?></td>
                                    <td><?php echo remove_junk(strtolower($a_user['email']))?></td>
                                    <td><?php echo remove_junk(ucwords($a_user['group_name']))?></td>
                                    <td>
                                        <?php if($a_user['status'] === '1'): ?>
                                            <span class="badge badge-success">
                                                <span class="status-indicator status-active"></span> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <span class="status-indicator status-inactive"></span> Inactive
                                            </span>
                                        <?php endif;?>
                                    </td>
                                    <td><?php echo read_date($a_user['last_login'])?></td>
                                    <td class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo (int)$a_user['id'];?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_user.php?id=<?php echo (int)$a_user['id'];?>" class="btn btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
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
        </script>
    </body>
    </html>
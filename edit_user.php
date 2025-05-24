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
    $req_fields = array('name', 'username', 'level');
    validate_fields($req_fields);
    if (empty($errors)) {
        $id = (int)$e_user['id'];
        $name = remove_junk($db->escape($_POST['name']));
        $username = remove_junk($db->escape($_POST['username']));
        $level = (int)$db->escape($_POST['level']);
        $status = remove_junk($db->escape($_POST['status']));
        
        $sql = "UPDATE users SET name ='{$name}', username ='{$username}', user_level='{$level}', status='{$status}' WHERE id='{$db->escape($id)}'";
        $result = $db->query($sql);
        
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Account Updated");
            redirect('edit_user.php?id=' . (int)$e_user['id'], false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('edit_user.php?id=' . (int)$e_user['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_user.php?id=' . (int)$e_user['id'], false);
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

        $sql = "UPDATE users SET password='{$h_pass}' WHERE id='{$db->escape($id)}'";
        $result = $db->query($sql);

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "User password has been updated");
            redirect('edit_user.php?id=' . (int)$e_user['id'], false);
        } else {
            $session->msg('d', 'Sorry, failed to update user password!');
            redirect('edit_user.php?id=' . (int)$e_user['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_user.php?id=' . (int)$e_user['id'], false);
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
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #3a7bd5;
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
        
        .col-md-6 {
            width: 50%;
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
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Update <?php echo remove_junk(ucwords($e_user['name'])); ?> Account</h3>
                        </div>
                        <div class="card-body" style="padding: 20px;">
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
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Change <?php echo remove_junk(ucwords($e_user['name'])); ?> Password</h3>
                        </div>
                        <div class="card-body" style="padding: 20px;">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Highlight current page in sidebar
            $('.sidebar-menu a').each(function() {
                if (window.location.href.indexOf($(this).attr('href')) > -1) {
                    $(this).addClass('active');
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
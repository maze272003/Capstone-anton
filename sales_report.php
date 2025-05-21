<?php
$page_title = 'Sales Report';
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

page_require_level(3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sales Report</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    
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
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .input-group {
            display: flex;
            align-items: center;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .input-group-addon {
            padding: 0 15px;
            background-color: #f5f7fb;
            border: 1px solid #ddd;
            border-left: none;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            color: var(--gray);
        }
        
        .datepicker {
            z-index: 1000 !important;
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
        
        .report-preview {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group-addon {
                width: 100%;
                border-left: 1px solid #ddd;
                border-top: none;
            }
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
                    <h1><i class="fas fa-chart-bar"></i> Sales Report</h1>
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
            
            <!-- Report Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Generate Sales Report</h3>
                </div>
                <div class="card-body">
                    <form class="clearfix" method="post" action="sale_report_process.php">
                        <div class="form-group">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="text" class="datepicker form-control" name="start-date" placeholder="From Date" autocomplete="off">
                                <span class="input-group-addon"><i class="fas fa-arrow-right"></i></span>
                                <input type="text" class="datepicker form-control" name="end-date" placeholder="To Date" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Preview Section (optional) -->
            <?php if(isset($_GET['start_date']) && isset($_GET['end_date'])): ?>
            <div class="report-preview">
                <h3>Report Preview</h3>
                <p>Showing results from <?php echo htmlspecialchars($_GET['start_date']); ?> to <?php echo htmlspecialchars($_GET['end_date']); ?></p>
                <!-- You can add actual report preview content here -->
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            // Highlight current page in sidebar
            $('.sidebar-menu a').each(function() {
                if (window.location.href.indexOf($(this).attr('href')) > -1) {
                    $(this).addClass('active');
                }
            });
            
            // Initialize datepicker
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
            
            // Set default dates (optional)
            $('input[name="start-date"]').datepicker('setDate', '-7d');
            $('input[name="end-date"]').datepicker('setDate', new Date());
        });
    </script>
</body>
</html>
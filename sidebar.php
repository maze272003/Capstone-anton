<style>
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
</style>
<!-- Sidebar -->
<div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-bullseye"></i> Spring Bullbars</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                <?php if(isset($user['user_level']) && $user['user_level'] === '1'): ?>
                        <!-- Admin Menu Links -->
                        <li><a href="admin.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                        
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> Add New Products</a></li>
                    
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                        <li><a href="sales_report.php"><i class="fa-solid fa-calendar-days"></i>Selacted date Sales</a></li>
                        <!-- <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li> -->
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '2'): ?>
                        <!-- Special User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '3'): ?>
                        <!-- User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
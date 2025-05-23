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
                        <li><a href="transaction_history.php"><i class="fas fa-history"></i> Transaction History</a></li>
                        <!-- <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li> -->
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '2'): ?>
                        <!-- Special User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                        <li><a href="transaction_history.php"><i class="fas fa-history"></i> Transaction History</a></li>
                    <?php elseif(isset($user['user_level']) && $user['user_level'] === '3'): ?>
                        <!-- User Menu Links -->
                        <li><a href="home.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="product.php"><i class="fas fa-box-open"></i> Products</a></li>
                        <li><a href="transaction_history.php"><i class="fas fa-history"></i> Transaction History</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
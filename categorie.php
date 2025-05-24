<?php
$page_title = 'Admin Categorie';
require_once('includes/load.php');

// Permission check
page_require_level(1);

// Get current user data
$user = current_user();

// Fetch all categories for the dropdown filter
$all_categories_for_filter = find_all('categories');

// Find all categories or filter by search query or dropdown
$sql = "SELECT * FROM categories";
$conditions = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = remove_junk($_GET['search']);
    $conditions[] = "name LIKE '%" . $db->escape($search_term) . "%'";
}

if (isset($_GET['category_filter']) && !empty($_GET['category_filter']) && $_GET['category_filter'] !== 'all') {
    $category_filter_id = remove_junk($_GET['category_filter']);
    $conditions[] = "id = '" . $db->escape($category_filter_id) . "'";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$all_categories = find_by_sql($sql);

?>
<?php
  if(isset($_POST['add_cat'])){ // <-- This checks for 'add_cat'
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($db->escape($_POST['categorie-name']));
    if(empty($errors)){
       $sql  = "INSERT INTO categories (name)";
       $sql .= " VALUES ('{$cat_name}')";
       if($db->query($sql)){
         $session->msg("s", "Successfully Added New Category");
         redirect('categorie.php',false);
       } else {
         $session->msg("d", "Sorry Failed to insert.");
         redirect('categorie.php',false);
       }
    } else {
      $session->msg("d", $errors);
      redirect('categorie.php',false);
    }
  }
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  <title>Categories</title>
</head>
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

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card a {
    text-decoration: none;
    color: inherit;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 15px;
}

.stat-icon.users {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary);
}

.stat-icon.categories {
    background-color: rgba(248, 37, 133, 0.1);
    color: var(--danger);
}

.stat-icon.products {
    background-color: rgba(76, 201, 240, 0.1);
    color: var(--success);
}

.stat-icon.sales {
    background-color: rgba(72, 149, 239, 0.1);
    color: var(--info);
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: var(--gray);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    margin-bottom: 20px;
}

.filter-container {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.filter-form {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-group {
    display: flex;
    align-items: center;
}

.filter-group label {
    margin-right: 10px;
    font-size: 14px;
    font-weight: 500;
}

.filter-select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 14px;
}

.date-range-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-input {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 14px;
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

.table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    overflow: hidden;
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
    background-color: #f9f9f9;
}

.table tr:hover {
    background-color: #f9f9f9;
}

.text-success {
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

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.inventory-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.inventory-value {
    font-size: 24px;
    font-weight: 600;
    color: var(--primary);
    margin-top: 10px;
}
.modal-dialog {
    margin-top: 50px; /* Adjust this value to control distance from the top */
    transform: translate(0, 0) !important; /* Override Bootstrap's default transform */
}
.modal-backdrop {
   background-color: rgba(0, 0, 0, 0.7);
}
.modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
}

.modal-header {
    background-color: var(--primary);
    color: white;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    border-bottom: none;
}

.modal-header .close {
    color: white;
    opacity: 1;
}

.modal-title {
    font-weight: 600;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    border-top: 1px solid #e5e5e5;
    padding: 15px 20px;
}

.modal-footer .btn {
    margin-left: 10px;
}

/* Adjust form control styling within modal if needed */
.modal-body .form-control {
    width: 100%;
}
</style>
<body>
  <div class="admin-container">
  <?php include_once('sidebar.php'); ?>
  <!-- Main Content -->
  <div class="main-content">
      
      <div class="top-bar">
          <div class="page-title">
              <h1><i class="fas fa-folder" style="margin-right: 10px;"></i>Categories</h1>
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

      <!-- Add Search and Filter here later -->
      <div class="filter-container">
          <form action="categorie.php" method="GET" class="filter-form">
              <div class="filter-group" style="flex-grow: 1;">
                  <input type="text" id="search" name="search" class="date-input" placeholder="Enter category name" style="width: 100%;" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
              </div>
              <div class="filter-group">
                  <label for="category_filter">Filter by Category:</label>
                  <select id="category_filter" name="category_filter" class="filter-select">
                      <option value="all">All Categories</option>
                      <?php foreach ($all_categories_for_filter as $cat): ?>
                          <option value="<?php echo remove_junk($cat['id']); ?>" <?php echo (isset($_GET['category_filter']) && $_GET['category_filter'] == $cat['id']) ? 'selected' : ''; ?>><?php echo remove_junk(ucfirst($cat['name'])); ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <button type="submit" class="btn btn-primary" title="Search">
                  <span class="fas fa-search"></span>
              </button>
          </form>
      </div>

      <div class="card">
          <div class="card-header">
              <h3>Category List</h3>
              <a href="#" class="btn btn-primary btn-sm" title="Add Category" data-toggle="modal" data-target="#addCategoryModal">
                <span class="fas fa-plus"></span>
              </a>
          </div>
          <div class="card-body">
              <div class="table-container">
                  <table class="table table-striped table-bordered">
                      <thead>
                          <tr>
                              <th>#</th>
                              <th>Category Name</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($all_categories as $category): ?>
                          <tr>
                              <td><?php echo remove_junk(ucfirst($category['id'])); ?></td>
                              <td><?php echo remove_junk(ucfirst($category['name'])); ?></td>
                              <td>
                                  <!-- Add Edit and Delete buttons here later -->
                                  <a href="edit_categorie.php?id=<?php echo (int)$category['id'];?>" class="btn btn-info btn-xs"  title="Edit" data-toggle="tooltip">
                                    <span class="fas fa-pencil-alt"></span>
                                  </a>
                                  <a href="delete_categorie.php?id=<?php echo (int)$category['id'];?>" class="btn btn-danger btn-xs"  title="Delete" data-toggle="tooltip">
                                    <span class="fas fa-trash"></span>
                                  </a>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>

      <!-- Add Category Modal -->
      <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="addCategoryModalLabel">Add New Category</h4>
            </div>
            <form action="categorie.php" method="post">
              <div class="modal-body">
                <div class="form-group">
                  <label for="category-name">Category Name</label>
                  <input type="text" class="form-control" id="category-name" name="categorie-name" placeholder="Enter category name" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" name="add_cat" class="btn btn-primary">Add Category</button> <!-- <-- Changed name to 'add_cat' -->
              </div>
            </form>
          </div>
        </div>
      </div>
  </div>
  </div>


</body>
</html>
<?php
  $page_title = 'Edit categorie';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);
?>
<?php
  //Display all catgories.
  $categorie = find_by_id('categories',(int)$_GET['id']);
  if(!$categorie){
    $session->msg("d","Missing categorie id.");
    redirect('categorie.php');
  }
?>

<?php
if(isset($_POST['edit_cat'])){
  $req_field = array('categorie-name');
  validate_fields($req_field);
  $cat_name = remove_junk($db->escape($_POST['categorie-name']));
  if(empty($errors)){
        $sql = "UPDATE categories SET name='{$cat_name}'";
       $sql .= " WHERE id='{$categorie['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Successfully updated Categorie");
       redirect('categorie.php',false);
     } else {
       $session->msg("d", "Sorry! Failed to Update");
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
  <title>Edit Category</title>
  <style>
    /* All the same styles from your main page */
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

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 10px;
        color: var(--dark);
        display: block;
    }

    .input-group {
        display: flex;
    }

    .input-group-prepend {
        margin-right: -1px;
    }

    .input-group-text {
        border-right: none;
        background-color: #f8f9fa !important;
        border-radius: 6px 0 0 6px !important;
        height: 50px;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-control {
        height: 50px;
        border-radius: 0 6px 6px 0;
        border: 1px solid #ddd;
        font-size: 16px;
        padding: 15px;
        width: 100%;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.2);
        border-color: #4361ee;
    }

    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
    }

    .btn-light {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
    }

    .btn-light:hover {
        background-color: #f1f3f9;
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

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
        
        .sidebar {
            width: 0;
            overflow: hidden;
            transition: width 0.3s;
        }
        
        .sidebar.active {
            width: 250px;
        }
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <?php include_once('sidebar.php'); ?>
    
    <!-- Main Content -->
    <div class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1><i class="fas fa-edit" style="margin-right: 10px;"></i>Edit Category</h1>
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

      <div class="card">
        <div class="card-header">
          <h3>Editing <?php echo remove_junk(ucfirst($categorie['name'])); ?></h3>
        </div>
        <div class="card-body">
          <form method="post" action="edit_categorie.php?id=<?php echo (int)$categorie['id']; ?>">
            <div class="form-group">
              <label for="categorie-name">
                <i class="fas fa-tag mr-2"></i>Category Name
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="fas fa-folder" style="color: #4361ee;"></i>
                  </span>
                </div>
                <input type="text" class="form-control" id="categorie-name" name="categorie-name" 
                       value="<?php echo remove_junk(ucfirst($categorie['name'])); ?>" required>
              </div>
            </div>
            <button type="submit" name="edit_cat" class="btn btn-primary">
              <i class="fas fa-save mr-2"></i>Update Category
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  $(document).ready(function(){
    // Initialize tooltips if any
    $('[data-toggle="tooltip"]').tooltip();
    
    // Mobile menu toggle functionality
    $('.menu-toggle').click(function(){
      $('.sidebar').toggleClass('active');
    });
  });
  </script>
</body>
</html>
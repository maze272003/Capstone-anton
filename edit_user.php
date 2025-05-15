<?php
  $page_title = 'Edit User';
  require_once('includes/load.php');
  page_require_level(1);
?>

<?php
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

<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-12"> <?php echo display_msg($msg); ?> </div>
    
    <!-- Update User Info -->
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    Update <?php echo remove_junk(ucwords($e_user['name'])); ?> Account
                </strong>
            </div>
            <div class="panel-body">
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
                            <option <?php if ($e_user['status'] === '1') echo 'selected="selected"'; ?> value="1">Active</option>
                            <option <?php if ($e_user['status'] === '0') echo 'selected="selected"'; ?> value="0">Inactive</option>
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
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    Change <?php echo remove_junk(ucwords($e_user['name'])); ?> Password
                </strong>
            </div>
            <div class="panel-body">
                <form action="edit_user.php?id=<?php echo (int)$e_user['id']; ?>" method="post" class="clearfix">
                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" id="password" class="form-control" name="password" placeholder="Enter new password">
                    </div>

                    <!-- Password Requirements -->
                    <ul id="password-requirements" style="color: black;">
                        <li id="length-check">❌ At least 8 characters</li>
                        <li id="special-char-check">❌ At least one special character (!@#$%^&*)</li>
                        <li id="number-check">❌ At least one number (0-9)</li>
                    </ul>

                    <div class="form-group clearfix">
                        <button type="submit" name="update-pass" id="update-pass-btn" class="btn btn-danger pull-right" disabled>Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Password Validation -->
<script>
document.addEventListener("DOMContentLoaded", function() {
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

<?php include_once('layouts/footer.php'); ?>


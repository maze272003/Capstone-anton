<?php
  $page_title = 'Add User';
  require_once('includes/load.php');
  page_require_level(1);
  $groups = find_all('user_groups');

  function is_valid_password($password) {
      return preg_match('/^(?=.*[!@#$%^&*])(?=.*\d).{8,}$/', $password);
  }

  if(isset($_POST['add_user'])){
      $req_fields = array('full-name','username','password','level');
      validate_fields($req_fields);

      if(empty($errors)){
          $name = remove_junk($db->escape($_POST['full-name']));
          $username = remove_junk($db->escape($_POST['username']));
          $password = remove_junk($db->escape($_POST['password']));
          $user_level = (int)$db->escape($_POST['level']);

          if (!is_valid_password($password)) {
              $session->msg('d', 'Password must be at least 8 characters long, contain at least one special character, and one number.');
              redirect('add_user.php', false);
          }

          $password = sha1($password);
          $query = "INSERT INTO users (name, username, password, user_level, status) VALUES ('{$name}', '{$username}', '{$password}', '{$user_level}', '1')";

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

<?php include_once('layouts/header.php'); ?>
  <?php echo display_msg($msg); ?>
  <div class="row">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Add New User</span>
       </strong>
      </div>
      <div class="panel-body">
        <div class="col-md-6">
          <form method="post" action="add_user.php" id="user-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="full-name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <ul id="password-requirements" style="color: gray;">
                    <li id="length-check">❌ Containt at least 8 characters</li>
                    <li id="special-char-check">❌ Contains at least one special character (!@#$%^&*)</li>
                    <li id="number-check">❌ Contains at least one number (0-9)</li>
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
            <div class="form-group clearfix">
              <button type="submit" name="add_user" id="add-user-btn" class="btn btn-primary" disabled>Add User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
  document.getElementById("password").addEventListener("input", function() {
      let password = this.value;
      let addUserBtn = document.getElementById("add-user-btn");

      let lengthCheck = document.getElementById("length-check");
      let specialCharCheck = document.getElementById("special-char-check");
      let numberCheck = document.getElementById("number-check");

      let lengthValid = password.length >= 8;
      let specialCharValid = /[!@#$%^&*]/.test(password);
      let numberValid = /\d/.test(password);

      lengthCheck.innerHTML = lengthValid ? "✔️ At least 8 characters" : "❌ At least 8 characters";
      specialCharCheck.innerHTML = specialCharValid ? "✔️ At least one special character (!@#$%^&*)" : "❌ At least one special character (!@#$%^&*)";
      numberCheck.innerHTML = numberValid ? "✔️ At least one number (0-9)" : "❌ At least one number (0-9)";

      if (lengthValid && specialCharValid && numberValid) {
          addUserBtn.disabled = false;
      } else {
          addUserBtn.disabled = true;
      }
  });
</script>

<?php include_once('layouts/footer.php'); ?>



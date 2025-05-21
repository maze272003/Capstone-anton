<?php
  ob_start();
  require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
  
  // Initialize variables
  $name = $username = $password = '';
  $errors = array();
  
  // Process form when submitted
  if(isset($_POST['signup'])) {
    $required_fields = array('name', 'username', 'password', 'confirm_password');
    validate_fields($required_fields);
    
    $name = remove_junk($db->escape($_POST['name']));
    $username = remove_junk($db->escape($_POST['username']));
    $password = remove_junk($db->escape($_POST['password']));
    $confirm_password = remove_junk($db->escape($_POST['confirm_password']));
    
    // Validate password match
    if($password !== $confirm_password) {
      $errors[] = "Passwords do not match";
    }
    
    // Check if username already exists
    $user = find_by_username($username);
    if($user) {
      $errors[] = "Username already exists. Please choose another.";
    }
    
    if(empty($errors)) {
      // Hash the password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      
      // Set default values
      $user_level = 2; // Default user level
      $status = 1;     // Active status
      
      // Create new user
      $query = "INSERT INTO users (";
      $query .="name, username, password, user_level, status, last_login";
      $query .=") VALUES (";
      $query .="'{$name}', '{$username}', '{$hashed_password}', '{$user_level}', '{$status}', NULL";
      $query .=")";
      
      if($db->query($query)) {
        $session->msg('s', "Account created successfully. You can now login.");
        redirect('index.php', false);
      } else {
        $session->msg('d', "Sorry, failed to create account.");
        redirect('signup.php', false);
      }
    } else {
      $session->msg('d', $errors);
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ShopSphere</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .signup-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            margin: 2rem 0;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .signup-header h2 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .signup-header p {
            color: var(--gray);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.3rem;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
        }
        
        .signup-btn {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
        }
        
        .signup-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <a href="index.php" class="logo">Shop<span>Sphere</span></a>
        
        <div class="signup-header">
            <h2>Create Your Account</h2>
            <p>Join thousands of businesses using ShopSphere</p>
        </div>
        
        <?php echo display_msg($msg); ?>
        
        <form method="post" action="signup.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name" required value="<?php echo htmlentities($name); ?>">
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Choose a username" required value="<?php echo htmlentities($username); ?>">
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Create a password" required>
                <i class="bi bi-eye password-toggle-icon" id="togglePassword"></i>
            </div>
            
            <div class="form-group password-toggle">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                <i class="bi bi-eye password-toggle-icon" id="toggleConfirmPassword"></i>
            </div>
            
            <button type="submit" name="signup" class="signup-btn">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="index.php">Log in</a>
        </div>
    </div>

    <script>
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('confirm_password');
        
        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the eye icon
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            
            // Toggle the eye icon
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
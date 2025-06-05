<?php

require_once 'loadEnv.php';
loadEnv(__DIR__ . '/.env');
ob_start();
require_once('includes/load.php');
if($session->isUserLoggedIn(true)) { redirect('home.php', false);}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Custom functions
function find_by_username($username) {
    global $db;
    $username = $db->escape($username);
    $sql = "SELECT * FROM users WHERE username = '{$username}' LIMIT 1";
    $result = $db->query($sql);
    return $db->fetch_assoc($result);
}

function find_by_email($email) {
    global $db;
    $email = $db->escape($email);
    $sql = "SELECT * FROM users WHERE email = '{$email}' LIMIT 1";
    $result = $db->query($sql);
    return $db->fetch_assoc($result);
}

function generate_otp() {
    return strval(rand(100000, 999999));
}

function send_otp_email($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST'); // Use Gmail SMTP
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email content
        $mail->setFrom('rueda.antonl@gmail.com', 'SpringBullbars');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "
            <h2>SpringBullbars Account Verification</h2>
            <p>Your OTP code is: <strong>{$otp}</strong></p>
            <p>This code will expire in 10 minutes.</p>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Initialize variables
$name = $username = $email = $password = '';
$errors = array();

// Process form submission
if(isset($_POST['signup'])) {
    $required_fields = array('name', 'username', 'email', 'password', 'confirm_password');
    validate_fields($required_fields);
    
    $name = remove_junk($db->escape($_POST['name']));
    $username = remove_junk($db->escape($_POST['username']));
    $email = remove_junk($db->escape($_POST['email']));
    $password = remove_junk($db->escape($_POST['password']));
    $confirm_password = remove_junk($db->escape($_POST['confirm_password']));
    
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if(find_by_username($username)) {
        $errors[] = "Username already exists";
    }
    
    if(find_by_email($email)) {
        $errors[] = "Email already registered";
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if(empty($errors)) {
        $otp = generate_otp();
        $hashed_password = sha1($password);
        $user_level = 2; // Default user level
        $status = 0; // Inactive until verified
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $query = "INSERT INTO users (name, username, email, password, user_level, status, otp_code, otp_expiry) 
                 VALUES ('{$name}', '{$username}', '{$email}', '{$hashed_password}', '{$user_level}', '{$status}', '{$otp}', '{$otp_expiry}')";
        
        if($db->query($query)) {
            if(send_otp_email($email, $otp)) {
                $_SESSION['verify_email'] = $email;
                redirect('verify.php', false);
            } else {
                // Delete the user if email failed to send
                $db->query("DELETE FROM users WHERE email = '{$email}'");
                $session->msg('d', "Failed to send OTP. Please try again.");
            }
        } else {
            $session->msg('d', "Sorry, failed to create account.");
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
    <title>Signss Up - SpringBullbars</title>
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
        
        .email-validation {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
            display: none;
        }
        
        .otp-section {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background-color: #f8fafc;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .otp-section.active {
            display: block;
        }
        
        .otp-input-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .otp-input {
            flex: 1;
            text-align: center;
            font-size: 1.2rem;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.3rem;
        }
        
        .resend-otp {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .resend-otp a {
            color: var(--primary);
            cursor: pointer;
            text-decoration: none;
        }
        
        .resend-otp a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="signup-container">
        <a href="index.php" class="logo">Spring<span>BullBars</span></a>
        
        <div class="signup-header">
            <h2>Create Your Account</h2>
            <p>Join thousands of businesses using SpringBullbars</p>
        </div>
        
        <?php 
        if(isset($msg) && is_array($msg)) {
            foreach($msg as $message) {
                echo display_msg($message);
            }
        } else {
            echo display_msg($msg);
        }
        ?>
        
        <form method="post" action="signup.php" id="signupForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name" required value="<?php echo htmlentities($name); ?>">
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Choose a username" required value="<?php echo htmlentities($username); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required value="<?php echo htmlentities($email); ?>">
                <div class="email-validation" id="emailValidation">Please enter a valid email address</div>
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
            
            <button type="submit" name="signup" class="signup-btn">Sign Up</button>
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
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Email validation
        const emailInput = document.getElementById('emailInput');
        const emailValidation = document.getElementById('emailValidation');
        
        emailInput.addEventListener('input', function() {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            if (!isValid && this.value.length > 0) {
                emailValidation.style.display = 'block';
            } else {
                emailValidation.style.display = 'none';
            }
        });

        // OTP Section Handling
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const otpSection = document.getElementById('otpSection');
        const formEmail = document.getElementById('formEmail');
        const emailInputField = document.getElementById('emailInput');
        const resendOtp = document.getElementById('resendOtp');
        
        sendOtpBtn.addEventListener('click', function() {
            // Validate email first
            const email = emailInputField.value;
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            if (!isValid) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Set the hidden email field value
            formEmail.value = email;
            
            // Show OTP section
            otpSection.classList.add('active');
            
            // Disable email field
            emailInputField.readOnly = true;
            
            // Focus on first OTP input
            document.getElementById('otp1').focus();
            
            // Send OTP via AJAX
            sendOtpRequest(email);
        });
        
        resendOtp.addEventListener('click', function() {
            const email = emailInputField.value;
            sendOtpRequest(email);
        });
        
        function sendOtpRequest(email) {
            // You would typically make an AJAX request here to send OTP
            // For now, we'll just simulate it
            console.log('Sending OTP to:', email);
            
            // In a real implementation, you would use fetch or XMLHttpRequest
            // Example:
            /*
            fetch('send_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('OTP sent successfully!');
                } else {
                    alert('Failed to send OTP: ' + data.message);
                }
            });
            */
        }
        
        // OTP input auto-focus
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
                
                // Update the full OTP value
                updateFullOtp();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0) {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });
        });
        
        function updateFullOtp() {
            let fullOtp = '';
            otpInputs.forEach(input => {
                fullOtp += input.value;
            });
            document.getElementById('fullOtp').value = fullOtp;
        }
        
        // Show OTP section if OTP was already sent (PHP side)
        <?php if($otp_sent): ?>
            otpSection.classList.add('active');
            emailInputField.readOnly = true;
        <?php endif; ?>
    </script>
</body>
</html>
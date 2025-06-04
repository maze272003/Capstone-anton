<?php
session_start();
ob_start();

// Load Composer's autoloader
require 'vendor/autoload.php';

// // Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// // Database configuration
// $db_host = 'localhost';
// $db_user = 'root';
// $db_pass = '';
// $db_name = 'imssb';
// $db = new mysqli($db_host, $db_user, $db_pass, $db_name);

// if ($db->connect_error) {
//     die("Database connection failed: " . $db->connect_error);
// }
include('dbconnection.php'); // Include database configuration
include('smtpconfig.php'); // Include SMTP configuration

// Helper functions
function sanitize($input) {
    global $db;
    return $db->real_escape_string(htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8'));
}

function display_msg() {
    if (isset($_SESSION['msg'])) {
        $message = $_SESSION['msg'];
        $type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : 'info';
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        return '<div class="message ' . $type . '">' . $message . '</div>';
    }
    return '';
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Password Reset Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_otp'])) {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $_SESSION['msg'] = "Invalid CSRF token";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php");
            exit();
        }

        $username_or_email = sanitize($_POST['username_or_email']);
        
        // Check if input is email or username
        $field = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE $field = ? LIMIT 1");
        $stmt->bind_param("s", $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate OTP (6 digits)
            $otp_code = sprintf("%06d", random_int(0, 999999));
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            
            // Update user record with OTP
            $update_stmt = $db->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $otp_code, $otp_expiry, $user['id']);
            $update_stmt->execute();
            
            // Send OTP email using PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $smtp_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp_username;
                $mail->Password   = $smtp_password;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom($from_email, $from_name);
                $mail->addAddress($user['email'], $user['name']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP Code';
                $mail->Body    = "
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user['name']},</p>
                    <p>You have requested to reset your password. Here is your OTP code:</p>
                    <h3>$otp_code</h3>
                    <p>This code will expire in 15 minutes.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                ";
                
                $mail->send();
                
                $_SESSION['msg'] = "OTP has been sent to your email. It will expire in 15 minutes.";
                $_SESSION['msg_type'] = 'success';
                header("Location: reset_password.php?step=verify&user_id={$user['id']}");
                exit();
            } catch (Exception $e) {
                $_SESSION['msg'] = "Failed to send OTP email. Please try again later.";
                $_SESSION['msg_type'] = 'error';
                header("Location: reset_password.php");
                exit();
            }
        } else {
            $_SESSION['msg'] = "Username/Email not found.";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php");
            exit();
        }
    }

    if (isset($_POST['verify_otp'])) {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $_SESSION['msg'] = "Invalid CSRF token";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php");
            exit();
        }

        $user_id = (int)$_POST['user_id'];
        $otp_code = sanitize($_POST['otp_code']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $_SESSION['msg'] = "Passwords do not match.";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php?step=verify&user_id=$user_id");
            exit();
        }
        
        if (strlen($new_password) < 8) {
            $_SESSION['msg'] = "Password must be at least 8 characters long.";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php?step=verify&user_id=$user_id");
            exit();
        }
        
        $current_time = date("Y-m-d H:i:s");
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND otp_code = ? AND otp_expiry > ? LIMIT 1");
        $stmt->bind_param("iss", $user_id, $otp_code, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            // Update password and clear OTP
            $hashed_password = sha1($new_password);
            $update_stmt = $db->prepare("UPDATE users SET password = ?, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            $update_stmt->execute();
            
            $_SESSION['msg'] = "Password updated successfully. You can now login with your new password.";
            $_SESSION['msg_type'] = 'success';
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['msg'] = "Invalid or expired OTP code.";
            $_SESSION['msg_type'] = 'error';
            header("Location: reset_password.php?step=verify&user_id=$user_id");
            exit();
        }
    }
}

// Determine which step to show
$step = isset($_GET['step']) && in_array($_GET['step'], ['request', 'verify']) ? $_GET['step'] : 'request';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .reset-header h2 {
            color: #6366f1;
            margin-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.3rem;
            font-family: inherit;
        }
        .btn {
            width: 100%;
            padding: 0.8rem;
            background-color: #6366f1;
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #4f46e5;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #6366f1;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if ($step === 'request'): ?>
            <div class="reset-header">
                <h2>Reset Password</h2>
                <p>Enter your username or email to receive OTP</p>
                <?php echo display_msg(); ?>
            </div>
            <form method="post" action="reset_password.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label for="username_or_email">Username or Email</label>
                    <input type="text" class="form-control" name="username_or_email" id="username_or_email" required autofocus>
                </div>
                <button type="submit" name="request_otp" class="btn">Send OTP</button>
            </form>
            <div class="back-link">
                <a href="edit_account.php">Back to profile</a>
            </div>
        
        <?php elseif ($step === 'verify' && $user_id > 0): ?>
            <div class="reset-header">
                <h2>Verify OTP</h2>
                <p>Enter the OTP sent to your email and set a new password</p>
                <?php echo display_msg(); ?>
            </div>
            <form method="post" action="reset_password.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                    <label for="otp_code">OTP Code</label>
                    <input type="text" class="form-control" name="otp_code" id="otp_code" required autofocus maxlength="6" pattern="\d{6}" title="Please enter a 6-digit OTP">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" name="new_password" id="new_password" required minlength="8">
                    <div class="password-strength">Minimum 8 characters</div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required minlength="8">
                </div>
                <button type="submit" name="verify_otp" class="btn">Reset Password</button>
            </form>
            <div class="back-link">
                <a href="edit_account.php">Back to profile</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple password match validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password');
            const confirm = document.getElementById('confirm_password');
            
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirm.focus();
            }
        });
    </script>
</body>
</html>
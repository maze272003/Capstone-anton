<?php
ob_start();
require_once('includes/load.php');

// Define the find_by_email function if it doesn't exist
if(!function_exists('find_by_email')) {
    function find_by_email($email) {
        global $db;
        $email = $db->escape($email);
        $sql = "SELECT * FROM users WHERE email='{$email}' LIMIT 1";
        $result = $db->query($sql);
        return $db->fetch_assoc($result);
    }
}

// Define generate_otp function if it doesn't exist
if(!function_exists('generate_otp')) {
    function generate_otp($length = 6) {
        return str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
    }
}

// Define send_otp_email function if it doesn't exist
if(!function_exists('send_otp_email')) {
    function send_otp_email($email, $otp) {
        $subject = "Your OTP Verification Code";
        $message = "Your verification code is: $otp\n\nThis code will expire in 10 minutes.";
        $headers = "From: no-reply@yourdomain.com";
        
        return mail($email, $subject, $message, $headers);
    }
}

if(!isset($_SESSION['verify_email'])) {
    redirect('signup.php', false);
}

$email = $_SESSION['verify_email'];
$user = find_by_email($email);

if(!$user) {
    $session->msg('d', "User not found.");
    redirect('signup.php', false);
}

if($user['status'] == 1) {
    $session->msg('s', "Account already verified. You can now login.");
    redirect('index.php', false);
}

// Process OTP verification
if(isset($_POST['verify_otp'])) {
    $otp = remove_junk($db->escape($_POST['otp']));
    $current_time = date('Y-m-d H:i:s');
    
    if($user['otp_code'] == $otp && $current_time <= $user['otp_expiry']) {
        // Update user status to active
        $db->query("UPDATE users SET status = 1, otp_code = NULL, otp_expiry = NULL WHERE email = '{$email}'");
        
        $session->msg('s', "Account verified successfully! You can now login.");
        unset($_SESSION['verify_email']);
        redirect('index.php', false);
    } else {
        $session->msg('d', "Invalid OTP or OTP has expired.");
    }
}

// Resend OTP
if(isset($_POST['resend_otp'])) {
    $new_otp = generate_otp();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    if(send_otp_email($email, $new_otp)) {
        $db->query("UPDATE users SET otp_code = '{$new_otp}', otp_expiry = '{$otp_expiry}' WHERE email = '{$email}'");
        $session->msg('s', "New OTP sent to your email.");
        // Refresh user data
        $user = find_by_email($email);
    } else {
        $session->msg('d', "Failed to resend OTP. Please try again.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - SpringBullBars</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            color: var(--dark);
        }
        
        .verify-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            margin: 2rem auto;
            text-align: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .logo span {
            color: var(--dark);
        }
        
        .signup-header {
            margin-bottom: 1.5rem;
        }
        
        .signup-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .signup-header p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .otp-input-group {
            display: flex;
            gap: 0.5rem;
            margin: 1.5rem 0;
            justify-content: center;
        }
        
        .otp-input {
            width: 3rem;
            height: 3rem;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.3rem;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        .signup-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .signup-btn:hover {
            background-color: var(--primary-light);
        }
        
        .resend-otp {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .resend-otp button {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-weight: 500;
        }
        
        .resend-otp button:hover {
            text-decoration: underline;
        }
        
        .msg {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.3rem;
            font-size: 0.9rem;
        }
        
        .msg.msg-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .msg.msg-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <a href="index.php" class="logo">Spring<span>Bullbars</span></a>
        
        <div class="signup-header">
            <h2>Verify Your Account</h2>
            <p>We've sent a 6-digit code to <?php echo htmlentities($email); ?></p>
        </div>
        
        <?php echo display_msg($msg); ?>
        
        <form method="post" action="verify.php">
            <div class="otp-input-group">
                <input type="text" class="otp-input" name="otp1" id="otp1" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" name="otp2" id="otp2" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" name="otp3" id="otp3" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" name="otp4" id="otp4" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" name="otp5" id="otp5" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" name="otp6" id="otp6" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>
            
            <input type="hidden" name="otp" id="fullOtp">
            
            <button type="submit" name="verify_otp" class="signup-btn">Verify Account</button>
            
            <div class="resend-otp">
                Didn't receive code? 
                <button type="submit" name="resend_otp">Resend</button>
            </div>
        </form>
    </div>

    <script>
        // OTP input auto-focus
        const otpInputs = document.querySelectorAll('.otp-input');
        const fullOtpInput = document.getElementById('fullOtp');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
                
                updateFullOtp();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0) {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });
            
            // Prevent non-numeric input
            input.addEventListener('keypress', function(e) {
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });
        });
        
        function updateFullOtp() {
            let fullOtp = '';
            otpInputs.forEach(input => {
                fullOtp += input.value;
            });
            fullOtpInput.value = fullOtp;
        }
        
        // Focus on first OTP input on page load
        document.getElementById('otp1').focus();
    </script>
</body>
</html>
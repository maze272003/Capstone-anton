<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'imssb');

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'warpgate27@gmail.com');
define('SMTP_PASSWORD', 'kiai dqav srik yqvd');
define('FROM_EMAIL', 'warpgate27@gmail.com');
define('FROM_NAME', 'SpringBullbars');

// Stock threshold (adjust as needed)
define('LOW_STOCK_THRESHOLD', 5);
// Email interval in seconds
define('EMAIL_INTERVAL', 20);
// Total runtime in seconds (24 hours)
define('TOTAL_RUNTIME', 86400);

// Connect to database
try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to send email using SMTP
function sendLowStockEmail($toEmail, $toName, $lowStockProducts) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Low Stock Alert - SpringBullbars';
        
        // Build email body
        $emailBody = '<h2>Low Stock Alert</h2>';
        $emailBody .= '<p>The following products are running low on stock (as of '.date('Y-m-d H:i:s').'):</p>';
        $emailBody .= '<table border="1" cellpadding="5" cellspacing="0">';
        $emailBody .= '<tr><th>Product Name</th><th>Remaining Quantity</th></tr>';
        
        foreach ($lowStockProducts as $product) {
            $emailBody .= '<tr>';
            $emailBody .= '<td>' . htmlspecialchars($product['name']) . '</td>';
            $emailBody .= '<td>' . $product['quantity'] . '</td>';
            $emailBody .= '</tr>';
        }
        
        $emailBody .= '</table>';
        $emailBody .= '<p>Please restock these items as soon as possible.</p>';
        
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Get all admin users (user_level = 1)
function getAdminUsers($db) {
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE user_level = 1 AND status = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get products with low stock
function getLowStockProducts($db) {
    $stmt = $db->prepare("SELECT id, name, quantity FROM products WHERE quantity <= :threshold");
    $stmt->bindValue(':threshold', LOW_STOCK_THRESHOLD, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Main execution with 24-hour runtime limit
try {
    $startTime = time();
    $endTime = $startTime + TOTAL_RUNTIME;
    
    echo "Starting low stock alert system at ".date('Y-m-d H:i:s', $startTime)."\n";
    echo "Will run until ".date('Y-m-d H:i:s', $endTime)." (24 hours)\n";
    echo "Checking every ".EMAIL_INTERVAL." seconds...\n";
    
    while (time() < $endTime) {
        $currentTime = time();
        $remainingTime = $endTime - $currentTime;
        $timestamp = date('Y-m-d H:i:s');
        
        echo "\n[{$timestamp}] Checking stock levels (".gmdate('H:i:s', $remainingTime)." remaining)...\n";
        
        $lowStockProducts = getLowStockProducts($db);

        if (!empty($lowStockProducts)) {
            $adminUsers = getAdminUsers($db);
            
            if (empty($adminUsers)) {
                error_log("[$timestamp] No admin users found to receive alerts.");
                echo "[$timestamp] No admin users found to receive alerts.\n";
            } else {
                foreach ($adminUsers as $admin) {
                    $sendResult = sendLowStockEmail($admin['email'], $admin['name'], $lowStockProducts);
                    
                    if ($sendResult) {
                        echo "[$timestamp] Low stock alert sent to: " . $admin['email'] . "\n";
                        file_put_contents('low_stock_alerts.log', 
                            "[$timestamp] Sent to " . $admin['email'] . PHP_EOL, 
                            FILE_APPEND);
                    } else {
                        echo "[$timestamp] Failed to send alert to: " . $admin['email'] . "\n";
                    }
                }
            }
        } else {
            echo "[$timestamp] No products are currently low in stock.\n";
        }
        
        // Calculate sleep time, adjusting for the last iteration
        $sleepTime = min(EMAIL_INTERVAL, $endTime - time());
        if ($sleepTime > 0) {
            sleep($sleepTime);
        }
    }
    
    echo "\n".date('Y-m-d H:i:s')." - 24-hour runtime completed. Script stopping.\n";
    
} catch (Exception $e) {
    error_log("Error in low stock alert system: " . $e->getMessage());
    echo "An error occurred: " . $e->getMessage() . "\n";
}
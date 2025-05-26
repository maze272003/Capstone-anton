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
        
        // Build email body with enhanced styling
        $emailBody = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                h2 {
                    color: #d9534f;
                    margin-bottom: 20px;
                }
                .alert-message {
                    margin-bottom: 20px;
                }
                .product-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .product-table th {
                    background-color: #d9534f;
                    color: white;
                    padding: 12px;
                    text-align: left;
                }
                .product-table td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #ddd;
                }
                .product-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .product-table tr:hover {
                    background-color: #f1f1f1;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #777;
                }
                .highlight {
                    font-weight: bold;
                    color: #d9534f;
                }
            </style>
        </head>
        <body>
            <h2>Low Stock Alert</h2>
            <div class="alert-message">
                <p>The following products are running <span class="highlight">low on stock</span> (as of '.date('Y-m-d H:i:s').'):</p>
            </div>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Remaining Quantity</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($lowStockProducts as $product) {
            $emailBody .= '
                    <tr>
                        <td>' . htmlspecialchars($product['name']) . '</td>
                        <td>' . $product['quantity'] . '</td>
                    </tr>';
        }
        
        $emailBody .= '
                </tbody>
            </table>
            <div class="footer">
                <p>Please restock these items as soon as possible.</p>
                <p>This is an automated message from SpringBullbars Inventory System.</p>
            </div>
        </body>
        </html>';
        
        $mail->Body = $emailBody;
        $mail->AltBody = "Low Stock Alert\n\n" .
                         "The following products are running low on stock (as of ".date('Y-m-d H:i:s')."):\n\n" .
                         implode("\n", array_map(function($p) {
                             return $p['name'] . ": " . $p['quantity'] . " remaining";
                         }, $lowStockProducts)) .
                         "\n\nPlease restock these items as soon as possible.";
        
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
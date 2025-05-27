<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'imssb');

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'rueda.antonl@gmail.com');
define('SMTP_PASSWORD', 'qwjd dfzt hmra abct');
define('FROM_EMAIL', 'warpgate27@gmail.com');
define('FROM_NAME', 'SpringBullbars');

// Stock threshold (adjust as needed)
define('LOW_STOCK_THRESHOLD', 5);

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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_alerts'])) {
    $timestamp = date('Y-m-d H:i:s');
    $message = '';
    
    try {
        $lowStockProducts = getLowStockProducts($db);

        if (!empty($lowStockProducts)) {
            $adminUsers = getAdminUsers($db);
            
            if (empty($adminUsers)) {
                $message = "[$timestamp] No admin users found to receive alerts.";
                error_log($message);
            } else {
                $sentCount = 0;
                foreach ($adminUsers as $admin) {
                    $sendResult = sendLowStockEmail($admin['email'], $admin['name'], $lowStockProducts);
                    
                    if ($sendResult) {
                        $message .= "[$timestamp] Low stock alert sent to: " . $admin['email'] . "<br>";
                        file_put_contents('low_stock_alerts.log', 
                            "[$timestamp] Sent to " . $admin['email'] . PHP_EOL, 
                            FILE_APPEND);
                        $sentCount++;
                    } else {
                        $message .= "[$timestamp] Failed to send alert to: " . $admin['email'] . "<br>";
                    }
                }
                
                if ($sentCount > 0) {
                    $message = "<div class='alert alert-success'>$message</div>";
                } else {
                    $message = "<div class='alert alert-danger'>$message</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-info'>[$timestamp] No products are currently low in stock.</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
        error_log("Error in low stock alert system: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .btn-primary {
            background-color: #d9534f;
            border-color: #d9534f;
        }
        .btn-primary:hover {
            background-color: #c9302c;
            border-color: #c12e2a;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #d9534f;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center">Low Stock Alert System</h2>
            </div>
            <div class="card-body">
                <p class="card-text">Click the button below to manually check for low stock items and send alerts to administrators.</p>
                
                <form method="POST" action="">
                    <button type="submit" name="trigger_alerts" class="btn btn-primary btn-lg w-100">
                        Check Stock and Send Alerts
                    </button>
                </form>
                
                <?php if (!empty($message)): ?>
                    <div class="mt-4">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-muted">
                Last checked: <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
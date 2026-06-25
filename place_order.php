<?php
// place_order.php - Updated with cash tax support
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wrapped_by_vee');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Content-Type: application/json');

$pdo = null;

function getDB() {
    global $pdo;
    
    try {
        if ($pdo !== null) {
            try {
                $pdo->query('SELECT 1');
                return $pdo;
            } catch (PDOException $e) {
                $pdo = null;
            }
        }
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Get payment settings from database
 */
function getPaymentSettings() {
    try {
        $db = getDB();
        if (!$db) return null;
        
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'payment'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'mpesa_enabled' => '1',
            'mpesa_phone' => '',
            'mpesa_tax' => '0',
            'bank_enabled' => '1',
            'bank_name' => '',
            'bank_account' => '',
            'bank_account_name' => '',
            'bank_tax' => '0',
            'default_tax' => '0'
        ], $settings);
    } catch (PDOException $e) {
        error_log("Error in getPaymentSettings: " . $e->getMessage());
        return [
            'mpesa_enabled' => '1',
            'mpesa_phone' => '',
            'mpesa_tax' => '0',
            'bank_enabled' => '1',
            'bank_name' => '',
            'bank_account' => '',
            'bank_account_name' => '',
            'bank_tax' => '0',
            'default_tax' => '0'
        ];
    }
}

/**
 * Get tax amount in cash for a specific payment method
 */
function getTaxAmount($paymentMethod) {
    $settings = getPaymentSettings();
    $method = trim($paymentMethod);
    
    if (stripos($method, 'M-Pesa') !== false || stripos($method, 'Mpesa') !== false) {
        return floatval($settings['mpesa_tax'] ?? 0);
    } elseif (stripos($method, 'Bank') !== false || stripos($method, 'Transfer') !== false) {
        return floatval($settings['bank_tax'] ?? 0);
    }
    return floatval($settings['default_tax'] ?? 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get order data
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $isGift = isset($_POST['is_gift']) && $_POST['is_gift'] === '1';
        $recipientName = isset($_POST['recipient_name']) ? trim($_POST['recipient_name']) : '';
        $recipientPhone = isset($_POST['recipient_phone']) ? trim($_POST['recipient_phone']) : '';
        $recipientAddress = isset($_POST['recipient_address']) ? trim($_POST['recipient_address']) : '';
        $giftMessage = isset($_POST['gift_message']) ? trim($_POST['gift_message']) : '';
        $payment = isset($_POST['payment']) ? trim($_POST['payment']) : '';
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        $delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 0;
        $delivery_zone_id = isset($_POST['delivery_zone_id']) ? $_POST['delivery_zone_id'] : null;
        $delivery_zone_name = isset($_POST['delivery_zone_name']) ? trim($_POST['delivery_zone_name']) : '';
        $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
        $paymentProof = isset($_FILES['payment_proof']) ? $_FILES['payment_proof'] : null;
        
        // ============================================
        // CALCULATE TAX AS CASH AMOUNT
        // ============================================
        $tax = getTaxAmount($payment); // Get fixed tax amount in TZS
        $total = $subtotal + $tax + $delivery_fee; // Total = subtotal + tax + delivery
        
        // Log incoming data for debugging
        error_log("=== place_order.php called ===");
        error_log("Name: $name, Phone: $phone");
        error_log("Payment: $payment, Subtotal: $subtotal, Tax: $tax, Delivery: $delivery_fee, Total: $total");
        
        // Validate
        $errors = array();
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($items)) $errors[] = 'Cart is empty';
        if (empty($payment)) $errors[] = 'Payment method is required';
        
        if ($isGift) {
            if (empty($recipientName)) $errors[] = 'Recipient name is required';
            if (empty($recipientPhone)) $errors[] = 'Recipient phone is required';
            if (empty($recipientAddress)) $errors[] = 'Recipient address is required';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
            exit;
        }
        
        // Upload payment proof
        $proofPath = null;
        if ($paymentProof && isset($paymentProof['error']) && $paymentProof['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/proofs/';
            $fullUploadDir = __DIR__ . '/' . $uploadDir;
            
            if (!file_exists($fullUploadDir)) {
                mkdir($fullUploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($paymentProof['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
            
            if (!in_array($fileExt, $allowedTypes)) {
                echo json_encode(['success' => false, 'error' => 'Invalid file type. Please upload JPG, PNG, GIF, WEBP or PDF.']);
                exit;
            }
            
            if ($paymentProof['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'error' => 'File is too large. Maximum size is 5MB.']);
                exit;
            }
            
            $filename = time() . '_' . uniqid() . '.' . $fileExt;
            $targetFile = $fullUploadDir . $filename;
            
            if (move_uploaded_file($paymentProof['tmp_name'], $targetFile)) {
                $proofPath = $uploadDir . $filename;
                error_log("Proof uploaded: " . $proofPath);
            } else {
                error_log("Failed to move uploaded file");
                echo json_encode(['success' => false, 'error' => 'Failed to upload payment proof']);
                exit;
            }
        } else {
            // For testing - allow orders without proof
            error_log("No payment proof uploaded - continuing for testing");
            // Uncomment for production:
            // echo json_encode(['success' => false, 'error' => 'Payment proof is required']);
            // exit;
        }
        
        // Build items string
        $itemsString = '';
        foreach ($items as $item) {
            $itemName = isset($item['name']) ? $item['name'] : 'Unknown';
            $itemQty = isset($item['qty']) ? $item['qty'] : 0;
            $itemPrice = isset($item['price']) ? $item['price'] : 0;
            $itemsString .= $itemName . ' x' . $itemQty . ' (TZS ' . number_format($itemPrice * $itemQty, 0) . '), ';
        }
        $itemsString = rtrim($itemsString, ', ');
        
        // Build delivery notes
        $deliveryNotes = "Delivery Zone: " . $delivery_zone_name . "\n";
        $deliveryNotes .= "Zone ID: " . $delivery_zone_id . "\n";
        $deliveryNotes .= "Delivery Fee: TZS " . number_format($delivery_fee, 0) . "\n";
        $deliveryNotes .= "Payment Method: " . $payment . "\n";
        $deliveryNotes .= "Tax Amount: TZS " . number_format($tax, 0) . "\n";
        
        if ($isGift) {
            $deliveryNotes .= "\n--- Gift Information ---\n";
            $deliveryNotes .= "Recipient Name: " . $recipientName . "\n";
            $deliveryNotes .= "Recipient Phone: " . $recipientPhone . "\n";
            $deliveryNotes .= "Recipient Address: " . $recipientAddress . "\n";
            if ($giftMessage) $deliveryNotes .= "Gift Message: " . $giftMessage . "\n";
        }
        
        // Save order
        try {
            $db = getDB();
            if (!$db) {
                echo json_encode(['success' => false, 'error' => 'Database connection failed']);
                exit;
            }
            
            // Get actual column names from the orders table
            $columns = $db->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
            error_log("Available columns: " . print_r($columns, true));
            
            // Build insert query based on existing columns
            $sql = "INSERT INTO orders (";
            $fields = [];
            $placeholders = [];
            $params = [];
            
            // Map of our data to database columns
            $columnMap = [
                'client_name' => $name,
                'phone' => $phone,
                'address' => $isGift ? $recipientAddress : '',
                'total_amount' => $total,
                'subtotal' => $subtotal,
                'tax' => $tax, // Now using cash tax amount
                'delivery_fee' => $delivery_fee,
                'delivery_zone_id' => $delivery_zone_id,
                'delivery_zone_name' => $delivery_zone_name,
                'payment_method' => $payment,
                'payment_proof' => $proofPath,
                'items' => $itemsString,
                'delivery_notes' => $deliveryNotes,
                'gift_wrap_selected' => $isGift ? 1 : 0,
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_address' => $recipientAddress,
                'gift_message' => $giftMessage,
                'tracking_status' => 'Pending',
                'payment_status' => 'Pending'
            ];
            
            // Only include columns that exist in the table
            foreach ($columnMap as $column => $value) {
                if (in_array($column, $columns)) {
                    $fields[] = $column;
                    $placeholders[] = '?';
                    $params[] = $value;
                }
            }
            
            // Add created_at if it exists
            if (in_array('created_at', $columns)) {
                $fields[] = 'created_at';
                $placeholders[] = 'NOW()';
            }
            
            $sql .= implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute($params)) {
                $orderId = $db->lastInsertId();
                error_log("Order created successfully with ID: " . $orderId);
                echo json_encode(['success' => true, 'orderId' => $orderId]);
            } else {
                error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
                echo json_encode(['success' => false, 'error' => 'Failed to save order. SQL Error.']);
            }
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    } catch (Exception $e) {
        error_log("place_order.php error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
?>
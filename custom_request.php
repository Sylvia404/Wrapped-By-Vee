<?php
// custom_request.php
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

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If no JSON data, check for form data
if (!$data) {
    // Try to get from $_POST if it's a form submission
    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request data']);
        exit;
    }
}

// Log the request
error_log("=== custom_request.php called ===");
error_log("Data: " . print_r($data, true));

try {
    // Extract data (support both JSON and form data)
    $name = isset($data['name']) ? trim($data['name']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $requestType = isset($data['requestType']) ? trim($data['requestType']) : '';
    $location = isset($data['location']) ? trim($data['location']) : '';
    $vision = isset($data['vision']) ? trim($data['vision']) : '';
    $isGift = isset($data['isGift']) ? (bool)$data['isGift'] : false;
    $recipientName = isset($data['recipientName']) ? trim($data['recipientName']) : '';
    $giftMessage = isset($data['giftMessage']) ? trim($data['giftMessage']) : '';
    $contactMethod = isset($data['contactMethod']) ? trim($data['contactMethod']) : 'Phone Call';
    $images = isset($data['images']) ? $data['images'] : [];
    
    // Validate
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($requestType)) $errors[] = 'Request type is required';
    if (empty($location)) $errors[] = 'Location is required';
    if (empty($vision)) $errors[] = 'Vision description is required';
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
        exit;
    }
    
    // Save images if any
    $imagePaths = [];
    if (!empty($images) && is_array($images)) {
        $uploadDir = 'uploads/custom/';
        $fullUploadDir = __DIR__ . '/' . $uploadDir;
        
        if (!file_exists($fullUploadDir)) {
            mkdir($fullUploadDir, 0777, true);
        }
        
        foreach ($images as $index => $imageData) {
            // Handle base64 images
            if (is_string($imageData) && strpos($imageData, 'data:image') === 0) {
                // Extract the base64 data
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                
                $decoded = base64_decode($imageData);
                if ($decoded !== false && strlen($decoded) > 100) {
                    $filename = time() . '_' . uniqid() . '_' . $index . '.jpg';
                    $filepath = $fullUploadDir . $filename;
                    
                    if (file_put_contents($filepath, $decoded)) {
                        $imagePaths[] = $uploadDir . $filename;
                    }
                }
            }
        }
    }
    
    $imagesJson = !empty($imagePaths) ? json_encode($imagePaths) : null;
    
    // Connect to database
    $db = getDB();
    if (!$db) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    
    // Insert the custom request
    $sql = "INSERT INTO custom_requests (
        name, phone, request_type, location, vision,
        is_gift, recipient_name, gift_message, contact_method, images, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $params = [
        $name,
        $phone,
        $requestType,
        $location,
        $vision,
        $isGift ? 1 : 0,
        $recipientName,
        $giftMessage,
        $contactMethod,
        $imagesJson,
        'Pending'
    ];
    
    error_log("SQL Params: " . print_r($params, true));
    
    if ($stmt->execute($params)) {
        $requestId = $db->lastInsertId();
        error_log("Custom request created with ID: " . $requestId);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Custom request submitted successfully!',
            'requestId' => $requestId
        ]);
    } else {
        error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
        echo json_encode(['success' => false, 'error' => 'Failed to save custom request']);
    }
    
} catch (Exception $e) {
    error_log("custom_request.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
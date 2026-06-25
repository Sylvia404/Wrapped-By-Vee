<?php
// functions.php - COMPLETE with all functions (NO DUPLICATES)
require_once 'db.php';

// ============================================
// SESSION MANAGEMENT
// ============================================

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0);
        session_start();
    }
}

function isAdminLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// ============================================
// ADMIN AUTHENTICATION FUNCTIONS
// ============================================

function getAdminByUsername($username) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email, full_name, password_hash, is_active FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getAdminByUsername: " . $e->getMessage());
        return null;
    }
}

function getAdminByEmail($email) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email, full_name, password_hash FROM admin_users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getAdminByEmail: " . $e->getMessage());
        return null;
    }
}

function verifyAdminLogin($username, $password) {
    try {
        $admin = getAdminByUsername($username);
        if ($admin && password_verify($password, $admin['password_hash'])) {
            return $admin;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in verifyAdminLogin: " . $e->getMessage());
        return false;
    }
}

function getAdminById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email, full_name FROM admin_users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getAdminById: " . $e->getMessage());
        return null;
    }
}

function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return getAdminById($_SESSION['admin_id'] ?? 0);
}

function updateAdminLastLogin($adminId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$adminId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateAdminLastLogin: " . $e->getMessage());
        return false;
    }
}

function logAdminActivity($adminIdentifier, $action, $details = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO admin_activity_logs (admin_email, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $adminIdentifier,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in logAdminActivity: " . $e->getMessage());
        return false;
    }
}

// ============================================
// PASSWORD RESET FUNCTIONS
// ============================================

function createPasswordReset($email, $token) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
        $stmt->execute();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ? AND used = 0");
        $stmt->execute([$email]);
        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmt->execute([$email, $token]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in createPasswordReset: " . $e->getMessage());
        return false;
    }
}

function verifyPasswordResetToken($token) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$token]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in verifyPasswordResetToken: " . $e->getMessage());
        return null;
    }
}

function completePasswordReset($token, $newPassword) {
    try {
        $db = getDB();
        $reset = verifyPasswordResetToken($token);
        if (!$reset) {
            return false;
        }
        $email = $reset['email'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->beginTransaction();
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
        $db->commit();
        return true;
    } catch (PDOException $e) {
        if (isset($db)) $db->rollBack();
        error_log("Error in completePasswordReset: " . $e->getMessage());
        return false;
    }
}

// ============================================
// EMAIL FUNCTIONS
// ============================================

function sendPasswordResetEmail($email, $token) {
    $settings = getGeneralSettings();
    $siteName = $settings['site_name'] ?? 'Wrapped by Vee';
    $siteEmail = $settings['site_email'] ?? 'info@wrappedbyvee.com';
    $admin = getAdminByEmail($email);
    $username = $admin['username'] ?? 'Admin';
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
    $subject = "Password Reset Request - " . $siteName;
    
    $htmlMessage = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Password Reset Request</h2>
        <p>Hello <strong>{$username}</strong>,</p>
        <p>We received a request to reset your password.</p>
        <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;background:#C2697E;color:white;text-decoration:none;border-radius:4px;'>Reset Password</a></p>
        <p>This link expires in 1 hour.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <p>Regards,<br>{$siteName}</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$siteName} <{$siteEmail}>\r\n";
    return mail($email, $subject, $htmlMessage, $headers);
}

// ============================================
// PRODUCT FUNCTIONS - FIXED WITH TAX FIELDS
// ============================================

function getAllProducts() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getAllProducts: " . $e->getMessage());
        return [];
    }
}

function getAllProductsAdmin() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getAllProductsAdmin: " . $e->getMessage());
        return [];
    }
}

function getProductById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getProductById: " . $e->getMessage());
        return null;
    }
}

function addProduct($productData) {
    try {
        $db = getDB();
        $sql = "INSERT INTO products (
            name, main_category, sub_category, price, 
            tax_mpesa, tax_bank,
            image_url, description, is_active, featured
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $productData['name'],
            $productData['main_category'] ?? 'Flowers',
            $productData['sub_category'] ?? null,
            $productData['price'],
            $productData['tax_mpesa'] ?? 0,
            $productData['tax_bank'] ?? 0,
            $productData['image_url'] ?? '',
            $productData['description'] ?? '',
            $productData['is_active'] ?? 1,
            $productData['featured'] ?? 0
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error in addProduct: " . $e->getMessage());
        return false;
    }
}

function updateProduct($id, $productData) {
    try {
        $db = getDB();
        $sql = "UPDATE products SET 
                name = ?, 
                main_category = ?, 
                sub_category = ?, 
                price = ?,
                tax_mpesa = ?,
                tax_bank = ?,
                image_url = ?, 
                description = ?,
                is_active = ?,
                featured = ?,
                updated_at = NOW()
                WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $productData['name'],
            $productData['main_category'] ?? 'Flowers',
            $productData['sub_category'] ?? null,
            $productData['price'],
            $productData['tax_mpesa'] ?? 0,
            $productData['tax_bank'] ?? 0,
            $productData['image_url'] ?? '',
            $productData['description'] ?? '',
            $productData['is_active'] ?? 1,
            $productData['featured'] ?? 0,
            $id
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateProduct: " . $e->getMessage());
        return false;
    }
}

function deleteProduct($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in deleteProduct: " . $e->getMessage());
        return false;
    }
}

function toggleProductStatus($id, $status) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in toggleProductStatus: " . $e->getMessage());
        return false;
    }
}

function uploadProductImage($file) {
    try {
        $target_dir = "uploads/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $newFilename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageFileType;
        $target_file = $target_dir . $newFilename;
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in uploadProductImage: " . $e->getMessage());
        return false;
    }
}

// ============================================
// ORDER FUNCTIONS
// ============================================

function getAllOrders() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getAllOrders: " . $e->getMessage());
        return [];
    }
}

function getOrderById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getOrderById: " . $e->getMessage());
        return null;
    }
}

function addOrder($orderData) {
    try {
        $db = getDB();
        $sql = "INSERT INTO orders (
            order_number, client_name, phone, address, total_amount, 
            payment_method, items, delivery_notes, gift_wrap_selected,
            recipient_name, recipient_phone, recipient_address, gift_message,
            tracking_status, payment_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        $stmt->execute([
            $orderNumber,
            $orderData['client_name'],
            $orderData['phone'],
            $orderData['address'] ?? '',
            $orderData['total_amount'],
            $orderData['payment_method'] ?? 'M-Pesa',
            $orderData['items'] ?? '',
            $orderData['delivery_notes'] ?? '',
            $orderData['gift_wrap_selected'] ?? 0,
            $orderData['recipient_name'] ?? '',
            $orderData['recipient_phone'] ?? '',
            $orderData['recipient_address'] ?? '',
            $orderData['gift_message'] ?? '',
            $orderData['tracking_status'] ?? 'Pending',
            $orderData['payment_status'] ?? 'Pending'
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error in addOrder: " . $e->getMessage());
        return false;
    }
}

function updateTrackingStatus($orderId, $status) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE orders SET tracking_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateTrackingStatus: " . $e->getMessage());
        return false;
    }
}

function updatePaymentStatus($orderId, $status) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in updatePaymentStatus: " . $e->getMessage());
        return false;
    }
}

function getOrdersByPhone($phone) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE phone = ? OR recipient_phone = ? ORDER BY created_at DESC");
        $stmt->execute([$phone, $phone]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getOrdersByPhone: " . $e->getMessage());
        return [];
    }
}

function getRecentOrders($limit = 10) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getRecentOrders: " . $e->getMessage());
        return [];
    }
}

// ============================================
// CUSTOM REQUEST FUNCTIONS
// ============================================

function getCustomRequests() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM custom_requests ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getCustomRequests: " . $e->getMessage());
        return [];
    }
}

function getCustomRequestById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM custom_requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getCustomRequestById: " . $e->getMessage());
        return null;
    }
}

function updateCustomRequestStatus($id, $status) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE custom_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateCustomRequestStatus: " . $e->getMessage());
        return false;
    }
}

function addCustomRequest($data) {
    try {
        $db = getDB();
        $sql = "INSERT INTO custom_requests (
            name, phone, request_type, location, vision,
            is_gift, recipient_name, recipient_phone, recipient_address, gift_message,
            contact_method, images, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['request_type'],
            $data['location'],
            $data['vision'],
            $data['is_gift'] ?? 0,
            $data['recipient_name'] ?? '',
            $data['recipient_phone'] ?? '',
            $data['recipient_address'] ?? '',
            $data['gift_message'] ?? '',
            $data['contact_method'] ?? 'Phone Call',
            $data['images'] ?? null,
            'Pending'
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error in addCustomRequest: " . $e->getMessage());
        return false;
    }
}

// ============================================
// DELIVERY FUNCTIONS
// ============================================

function getDeliveryZones() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY zone_name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getDeliveryZones: " . $e->getMessage());
        return [];
    }
}

function getDeliveryFee($zoneId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT delivery_fee FROM delivery_zones WHERE id = ?");
        $stmt->execute([$zoneId]);
        $result = $stmt->fetch();
        return $result ? $result['delivery_fee'] : 0;
    } catch (PDOException $e) {
        error_log("Error in getDeliveryFee: " . $e->getMessage());
        return 0;
    }
}

function getDeliverySettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'delivery'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'free_delivery_threshold' => 50000,
            'delivery_base_fee' => 5000,
            'delivery_time' => '2-3 business days'
        ], $settings);
    } catch (PDOException $e) {
        error_log("Error in getDeliverySettings: " . $e->getMessage());
        return [
            'free_delivery_threshold' => 50000,
            'delivery_base_fee' => 5000,
            'delivery_time' => '2-3 business days'
        ];
    }
}

// ============================================
// SETTINGS FUNCTIONS
// ============================================

function getGeneralSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'general'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'site_name' => 'Wrapped by Vee',
            'site_email' => 'info@wrappedbyvee.com',
            'site_phone' => '+255 755 555 555',
            'site_address' => 'Dodoma, Tanzania'
        ], $settings);
    } catch (PDOException $e) {
        error_log("Error in getGeneralSettings: " . $e->getMessage());
        return [
            'site_name' => 'Wrapped by Vee',
            'site_email' => 'info@wrappedbyvee.com',
            'site_phone' => '+255 755 555 555',
            'site_address' => 'Dodoma, Tanzania'
        ];
    }
}

function updateGeneralSettings($data) {
    try {
        $db = getDB();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                VALUES (?, ?, 'general') 
                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateGeneralSettings: " . $e->getMessage());
        return false;
    }
}

function updateBrandingSettings($data) {
    try {
        $db = getDB();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                VALUES (?, ?, 'branding') 
                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateBrandingSettings: " . $e->getMessage());
        return false;
    }
}

function getHomepageSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'homepage'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Error in getHomepageSettings: " . $e->getMessage());
        return [];
    }
}

function updateHomepageSettings($data) {
    try {
        $db = getDB();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                VALUES (?, ?, 'homepage') 
                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateHomepageSettings: " . $e->getMessage());
        return false;
    }
}

function getBrandingSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'branding'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'brand_name' => 'Wrapped by Vee',
            'brand_logo' => '',
            'brand_color' => '#C2697E',
            'brand_tagline' => 'Where flowers tell stories'
        ], $settings);
    } catch (PDOException $e) {
        error_log("Error in getBrandingSettings: " . $e->getMessage());
        return [
            'brand_name' => 'Wrapped by Vee',
            'brand_logo' => '',
            'brand_color' => '#C2697E',
            'brand_tagline' => 'Where flowers tell stories'
        ];
    }
}

function getSocialSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_group = 'social'");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'pinterest' => '',
            'youtube' => '',
            'tiktok' => '',
            'whatsapp' => ''
        ], $settings);
    } catch (PDOException $e) {
        error_log("Error in getSocialSettings: " . $e->getMessage());
        return [
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'pinterest' => '',
            'youtube' => '',
            'tiktok' => '',
            'whatsapp' => ''
        ];
    }
}

function updateSocialSettings($data) {
    try {
        $db = getDB();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                VALUES (?, ?, 'social') 
                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error in updateSocialSettings: " . $e->getMessage());
        return false;
    }
}

// ============================================
// PAYMENT FUNCTIONS
// ============================================

function getPaymentMethods() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY id");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getPaymentMethods: " . $e->getMessage());
        return [];
    }
}

function getPaymentInstructions($paymentMethod) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE method = ? AND is_active = 1");
        $stmt->execute([$paymentMethod]);
        $result = $stmt->fetch();
        if ($result) {
            return [
                'success' => true,
                'instruction_text' => $result['instruction_text'] ?? '',
                'phone_number' => $result['phone_number'] ?? '',
                'account_number' => $result['account_number'] ?? '',
                'account_name' => $result['account_name'] ?? '',
                'bank_name' => $result['bank_name'] ?? ''
            ];
        }
        return [
            'success' => true,
            'instruction_text' => 'Please complete payment and upload proof.',
            'phone_number' => '',
            'account_number' => '',
            'account_name' => '',
            'bank_name' => ''
        ];
    } catch (PDOException $e) {
        error_log("Error in getPaymentInstructions: " . $e->getMessage());
        return [
            'success' => true,
            'instruction_text' => 'Please complete payment and upload proof.',
            'phone_number' => '',
            'account_number' => '',
            'account_name' => '',
            'bank_name' => ''
        ];
    }
}

function getPaymentSettings() {
    try {
        $db = getDB();
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
            'bank_tax' => '0'
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
            'bank_tax' => '0'
        ];
    }
}

function updatePaymentSettings($data) {
    try {
        $db = getDB();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                VALUES (?, ?, 'payment') 
                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error in updatePaymentSettings: " . $e->getMessage());
        return false;
    }
}

// ============================================
// DASHBOARD STATS FUNCTIONS
// ============================================

function getTotalRevenue() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'Paid'");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalRevenue: " . $e->getMessage());
        return 0;
    }
}

function getTotalOrders() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalOrders: " . $e->getMessage());
        return 0;
    }
}

function getPendingOrders() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders WHERE tracking_status = 'Pending' OR payment_status = 'Pending'");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getPendingOrders: " . $e->getMessage());
        return 0;
    }
}

function getDeliveredOrders() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders WHERE tracking_status = 'Delivered'");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getDeliveredOrders: " . $e->getMessage());
        return 0;
    }
}

function getTodayOrders() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTodayOrders: " . $e->getMessage());
        return 0;
    }
}

function getTodayRevenue() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'Paid'");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTodayRevenue: " . $e->getMessage());
        return 0;
    }
}

function getTotalProducts() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalProducts: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// ADDITIONAL DASHBOARD & FINANCE FUNCTIONS
// ============================================

function getRecentOrdersForDashboard($limit = 5) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getRecentOrdersForDashboard: " . $e->getMessage());
        return [];
    }
}

function getTotalExpenses() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return 0;
        }
        $stmt = $db->query("SELECT SUM(amount) as total FROM expenses");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalExpenses: " . $e->getMessage());
        return 0;
    }
}

function getNetProfit() {
    $income = getTotalRevenue();
    $expenses = getTotalExpenses();
    return $income - $expenses;
}

function getTodayExpenses() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return 0;
        }
        $stmt = $db->query("SELECT SUM(amount) as total FROM expenses WHERE DATE(created_at) = CURDATE()");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTodayExpenses: " . $e->getMessage());
        return 0;
    }
}

function getRecentExpenses($limit = 5) {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return [];
        }
        $stmt = $db->prepare("SELECT * FROM expenses ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getRecentExpenses: " . $e->getMessage());
        return [];
    }
}

function getExpensesByCategory() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return [];
        }
        $stmt = $db->query("SELECT category, SUM(amount) as total FROM expenses GROUP BY category ORDER BY total DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getExpensesByCategory: " . $e->getMessage());
        return [];
    }
}

function getMonthlyRevenue($month, $year) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND payment_status = 'Paid'");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getMonthlyRevenue: " . $e->getMessage());
        return 0;
    }
}

function getMonthlyExpenses($month, $year) {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return 0;
        }
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM expenses WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getMonthlyExpenses: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// FINANCE FUNCTIONS
// ============================================

function getTotalIncome() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'income'");
        if ($stmt->rowCount() == 0) {
            return 0;
        }
        $stmt = $db->query("SELECT SUM(amount) as total FROM income");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalIncome: " . $e->getMessage());
        return 0;
    }
}

function getMonthlyIncome($month, $year) {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'income'");
        if ($stmt->rowCount() == 0) {
            return 0;
        }
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM income WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getMonthlyIncome: " . $e->getMessage());
        return 0;
    }
}

function getIncomeRecords() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'income'");
        if ($stmt->rowCount() == 0) {
            return [];
        }
        $stmt = $db->query("SELECT * FROM income ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getIncomeRecords: " . $e->getMessage());
        return [];
    }
}

function addIncome($name, $amount, $category, $source, $note = '') {
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS income (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) DEFAULT 'Other',
            source VARCHAR(50) DEFAULT 'Cash',
            note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $db->prepare("INSERT INTO income (name, amount, category, source, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $amount, $category, $source, $note]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error in addIncome: " . $e->getMessage());
        return false;
    }
}

function deleteIncome($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM income WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in deleteIncome: " . $e->getMessage());
        return false;
    }
}

function getExpenses() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'expenses'");
        if ($stmt->rowCount() == 0) {
            return [];
        }
        $stmt = $db->query("SELECT * FROM expenses ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getExpenses: " . $e->getMessage());
        return [];
    }
}

function addExpense($data) {
    try {
        $db = getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS expenses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            description VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) DEFAULT 'Other',
            note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $db->prepare("INSERT INTO expenses (description, amount, category, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['description'],
            $data['amount'],
            $data['category'] ?? 'Other',
            $data['note'] ?? ''
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error in addExpense: " . $e->getMessage());
        return false;
    }
}

function deleteExpense($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error in deleteExpense: " . $e->getMessage());
        return false;
    }
}

// ============================================
// EXECUTE QUERY FUNCTION
// ============================================

function executeQuery($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Error in executeQuery: " . $e->getMessage());
        return false;
    }
}
?>
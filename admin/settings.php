<?php
// admin/settings.php - COMPLETE with all action handlers & CASH TAX
require_once '../functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// ============================================
// HANDLE ALL POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ============================================
    // 1. HOMEPAGE SETTINGS
    // ============================================
    if ($action === 'update_homepage') {
        $data = [
            'hero_badge_text' => $_POST['hero_badge_text'] ?? '',
            'hero_title_text' => $_POST['hero_title_text'] ?? '',
            'hero_tagline' => $_POST['hero_tagline'] ?? '',
            'hero_location' => $_POST['hero_location'] ?? '',
            'features_title' => $_POST['features_title'] ?? '',
            'features_subtitle' => $_POST['features_subtitle'] ?? '',
            'quote_text' => $_POST['quote_text'] ?? '',
            'quote_author' => $_POST['quote_author'] ?? '',
            'scroll_banner_items' => $_POST['scroll_banner_items'] ?? '',
            'footer_tagline' => $_POST['footer_tagline'] ?? '',
            'footer_location' => $_POST['footer_location'] ?? '',
            'testimonial_1_text' => $_POST['testimonial_1_text'] ?? '',
            'testimonial_1_name' => $_POST['testimonial_1_name'] ?? '',
            'testimonial_1_location' => $_POST['testimonial_1_location'] ?? '',
            'testimonial_2_text' => $_POST['testimonial_2_text'] ?? '',
            'testimonial_2_name' => $_POST['testimonial_2_name'] ?? '',
            'testimonial_2_location' => $_POST['testimonial_2_location'] ?? '',
            'testimonial_3_text' => $_POST['testimonial_3_text'] ?? '',
            'testimonial_3_name' => $_POST['testimonial_3_name'] ?? '',
            'testimonial_3_location' => $_POST['testimonial_3_location'] ?? ''
        ];
        
        if (updateHomepageSettings($data)) {
            header('Location: settings.php?tab=homepage&success=1');
        } else {
            header('Location: settings.php?tab=homepage&error=Failed to save settings');
        }
        exit;
    }
    
    // ============================================
    // 2. BRANDING SETTINGS
    // ============================================
    if ($action === 'update_branding') {
        $data = [
            'brand_name' => $_POST['brand_name'] ?? 'Wrapped by Vee',
            'brand_tagline' => $_POST['brand_tagline'] ?? 'Where flowers tell stories',
            'brand_color' => $_POST['brand_color'] ?? '#C2697E'
        ];
        
        // Handle logo upload
        if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/branding/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            if (in_array($fileExt, $allowed) && $_FILES['brand_logo']['size'] <= 2 * 1024 * 1024) {
                $filename = 'logo_' . time() . '.' . $fileExt;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $target)) {
                    $data['brand_logo'] = 'uploads/branding/' . $filename;
                }
            }
        }
        
        if (updateBrandingSettings($data)) {
            header('Location: settings.php?tab=branding&success=1');
        } else {
            header('Location: settings.php?tab=branding&error=Failed to save branding');
        }
        exit;
    }
    
    // ============================================
    // 3. GENERAL SETTINGS
    // ============================================
    if ($action === 'update_general') {
        $data = [
            'site_name' => $_POST['site_name'] ?? 'Wrapped by Vee',
            'site_email' => $_POST['site_email'] ?? 'info@wrappedbyvee.com',
            'site_phone' => $_POST['site_phone'] ?? '+255 755 555 555',
            'site_address' => $_POST['site_address'] ?? 'Dodoma, Tanzania'
        ];
        
        if (updateGeneralSettings($data)) {
            header('Location: settings.php?tab=general&success=1');
        } else {
            header('Location: settings.php?tab=general&error=Failed to save settings');
        }
        exit;
    }
    
    // ============================================
    // 4. SOCIAL SETTINGS
    // ============================================
    if ($action === 'update_social') {
        $data = [
            'facebook' => $_POST['facebook'] ?? '',
            'instagram' => $_POST['instagram'] ?? '',
            'twitter' => $_POST['twitter'] ?? '',
            'pinterest' => $_POST['pinterest'] ?? '',
            'youtube' => $_POST['youtube'] ?? '',
            'tiktok' => $_POST['tiktok'] ?? '',
            'whatsapp' => $_POST['whatsapp'] ?? ''
        ];
        
        if (updateSocialSettings($data)) {
            header('Location: settings.php?tab=social&success=1');
        } else {
            header('Location: settings.php?tab=social&error=Failed to save social settings');
        }
        exit;
    }
    
    // ============================================
    // 5. PAYMENT SETTINGS - FIXED WITH CASH TAX
    // ============================================
    if ($action === 'update_payment') {
        $data = [
            'mpesa_enabled' => isset($_POST['mpesa_enabled']) ? '1' : '0',
            'mpesa_phone' => $_POST['mpesa_phone'] ?? '',
            'mpesa_tax' => $_POST['mpesa_tax'] ?? '0', // Cash amount in TZS
            'bank_enabled' => isset($_POST['bank_enabled']) ? '1' : '0',
            'bank_name' => $_POST['bank_name'] ?? '',
            'bank_account' => $_POST['bank_account'] ?? '',
            'bank_account_name' => $_POST['bank_account_name'] ?? '',
            'bank_tax' => $_POST['bank_tax'] ?? '0', // Cash amount in TZS
            'default_tax' => $_POST['default_tax'] ?? '0' // Cash amount in TZS
        ];
        
        if (updatePaymentSettings($data)) {
            header('Location: settings.php?tab=payment&success=1');
        } else {
            header('Location: settings.php?tab=payment&error=Failed to save payment settings');
        }
        exit;
    }
    
    // ============================================
    // 6. DELIVERY ZONE - ADD
    // ============================================
    if ($action === 'add_delivery_zone') {
        $sql = "INSERT INTO delivery_zones (zone_name, delivery_fee, estimated_days, is_active) VALUES (?, ?, ?, ?)";
        executeQuery($sql, [
            $_POST['zone_name'],
            $_POST['delivery_fee'],
            $_POST['estimated_days'] ?? '2-3 days',
            isset($_POST['is_active']) ? 1 : 0
        ]);
        header('Location: settings.php?tab=delivery&success=zone_added');
        exit;
    }
    
    // ============================================
    // 7. DELIVERY ZONE - UPDATE
    // ============================================
    if ($action === 'update_delivery_zone') {
        $sql = "UPDATE delivery_zones SET zone_name = ?, delivery_fee = ?, estimated_days = ?, is_active = ? WHERE id = ?";
        executeQuery($sql, [
            $_POST['zone_name'],
            $_POST['delivery_fee'],
            $_POST['estimated_days'] ?? '2-3 days',
            isset($_POST['is_active']) ? 1 : 0,
            $_POST['zone_id']
        ]);
        header('Location: settings.php?tab=delivery&success=zone_updated');
        exit;
    }
    
    // ============================================
    // 8. DELIVERY ZONE - DELETE
    // ============================================
    if ($action === 'delete_delivery_zone') {
        $sql = "DELETE FROM delivery_zones WHERE id = ?";
        executeQuery($sql, [$_POST['zone_id']]);
        header('Location: settings.php?tab=delivery&success=zone_deleted');
        exit;
    }
    
    // ============================================
    // 9. ADMIN PASSWORD UPDATE
    // ============================================
    if ($action === 'update_admin_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            header('Location: settings.php?tab=admin&error=Please fill in all fields');
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            header('Location: settings.php?tab=admin&error=New passwords do not match');
            exit;
        }
        
        if (strlen($new_password) < 6) {
            header('Location: settings.php?tab=admin&error=Password must be at least 6 characters');
            exit;
        }
        
        $admin_id = $_SESSION['admin_id'];
        $stmt = executeQuery("SELECT password_hash FROM admin_users WHERE id = ?", [$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
            header('Location: settings.php?tab=admin&error=Current password is incorrect');
            exit;
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        executeQuery("UPDATE admin_users SET password_hash = ? WHERE id = ?", [$new_hash, $admin_id]);
        
        header('Location: settings.php?tab=admin&success=password_updated');
        exit;
    }
    
    // ============================================
    // 10. ADMIN EMAIL UPDATE
    // ============================================
    if ($action === 'update_admin_email') {
        $new_email = $_POST['admin_email'] ?? '';
        
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            header('Location: settings.php?tab=admin&error=Please enter a valid email address');
            exit;
        }
        
        $admin_id = $_SESSION['admin_id'];
        executeQuery("UPDATE admin_users SET email = ? WHERE id = ?", [$new_email, $admin_id]);
        
        header('Location: settings.php?tab=admin&success=email_updated');
        exit;
    }
}

// ============================================
// GET ALL SETTINGS
// ============================================
$homepage = getHomepageSettings();
$general = getGeneralSettings();
$branding = getBrandingSettings();
$social = getSocialSettings();
$payment = getPaymentSettings();
$deliveryZones = getDeliveryZones();

// Get admin info
$admin_id = $_SESSION['admin_id'];
$stmt = executeQuery("SELECT username, email FROM admin_users WHERE id = ?", [$admin_id]);
$adminInfo = $stmt->fetch();

$activeTab = $_GET['tab'] ?? 'homepage';
$success = isset($_GET['success']);
$error = $_GET['error'] ?? '';
$editZone = null;

// If editing a zone, get the data
if (isset($_GET['edit_zone']) && is_numeric($_GET['edit_zone'])) {
    $stmt = executeQuery("SELECT * FROM delivery_zones WHERE id = ?", [$_GET['edit_zone']]);
    $editZone = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Settings | Wrapped by Vee</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Inter', sans-serif; background: #F8F4F6; padding-bottom: 80px; }
        * { transition: all 0.2s ease; }

        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: rgba(255,255,255,0.92); backdrop-filter: blur(20px);
            border-top: 0.5px solid rgba(194,105,126,0.12);
            padding: 8px 16px 12px; display: flex; justify-content: space-around;
            z-index: 50; padding-bottom: max(12px, env(safe-area-inset-bottom));
        }
        .nav-item {
            display: flex; flex-direction: column; align-items: center;
            gap: 2px; padding: 6px 12px; border-radius: 30px;
            font-size: 10px; color: #B8A0A8; font-weight: 500;
            text-decoration: none; min-width: 56px; position: relative;
        }
        .nav-item.active { color: #C2697E; }
        .nav-item.active::before {
            content: ''; position: absolute; top: -8px; left: 50%;
            transform: translateX(-50%); width: 20px; height: 3px;
            background: #C2697E; border-radius: 0 0 4px 4px;
        }
        .nav-item:active { transform: scale(0.92); }
        .nav-icon { font-size: 20px; line-height: 1; font-weight: 300; }
        .nav-label { font-size: 9px; letter-spacing: 0.3px; }
        @media (min-width: 768px) { .bottom-nav { display: none; } }

        .desktop-sidebar { display: none; }
        @media (min-width: 768px) {
            .desktop-sidebar {
                display: block; position: fixed; left: 0; top: 0;
                width: 260px; height: 100%; background: white;
                border-right: 0.5px solid rgba(194,105,126,0.08);
                overflow-y: auto; z-index: 40; padding: 32px 24px;
            }
            .main-content { margin-left: 260px; padding: 32px 40px 40px; }
            body { padding-bottom: 0; }
        }

        .sidebar-brand {
            display: flex; align-items: center; gap: 12px; margin-bottom: 40px;
        }
        .sidebar-brand-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #F5E8EC, #FDE8EE);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #C2697E; font-weight: 700;
        }
        .sidebar-brand-text { font-size: 18px; font-weight: 700; color: #3B2229; letter-spacing: -0.3px; }
        .sidebar-brand-text span { color: #C2697E; }
        .sidebar-brand-sub { font-size: 10px; color: #B8A0A8; font-weight: 400; letter-spacing: 0.5px; }

        .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            border-radius: 12px; color: #6B5A62; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.2s;
        }
        .sidebar-nav a:hover { background: #F8F4F6; color: #C2697E; }
        .sidebar-nav a.active { background: #F8F4F6; color: #C2697E; font-weight: 600; }
        .sidebar-nav a .nav-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: currentColor; opacity: 0.3;
        }
        .sidebar-nav a.active .nav-dot { opacity: 1; background: #C2697E; }

        .sidebar-footer {
            position: absolute; bottom: 32px; left: 24px; right: 24px;
            padding-top: 20px; border-top: 0.5px solid rgba(194,105,126,0.08);
        }
        .sidebar-footer .user-name { font-size: 13px; font-weight: 600; color: #3B2229; }
        .sidebar-footer .user-role { font-size: 11px; color: #B8A0A8; }
        .sidebar-footer .logout-link {
            display: inline-block; margin-top: 8px; font-size: 12px;
            color: #C2697E; text-decoration: none; font-weight: 500;
        }

        .setting-card {
            background: white; border-radius: 16px; padding: 24px;
            border: 0.5px solid rgba(194,105,126,0.06);
            box-shadow: 0 2px 12px rgba(194,105,126,0.04);
        }

        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #EDE4E8; border-radius: 12px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            background: white; transition: all 0.2s; outline: none;
        }
        .form-control:focus { border-color: #C2697E; box-shadow: 0 0 0 3px rgba(194,105,126,0.06); }
        .form-control.textarea { resize: vertical; min-height: 60px; }

        .btn-primary {
            background: #C2697E; color: white; border: none;
            padding: 12px 24px; border-radius: 12px; font-size: 13px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-primary:hover { background: #A8576A; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(194,105,126,0.25); }
        .btn-primary:active { transform: scale(0.97); }

        .btn-danger {
            background: #DC2626; color: white; border: none;
            padding: 6px 14px; border-radius: 8px; font-size: 11px;
            font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-danger:hover { background: #B91C1C; }
        .btn-danger:active { transform: scale(0.95); }

        .btn-sm {
            background: #F8F4F6; color: #6B5A62; border: none;
            padding: 6px 14px; border-radius: 8px; font-size: 11px;
            font-weight: 500; cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-sm:hover { background: #EDE4E8; }

        .tab-btn {
            padding: 8px 20px; border-radius: 40px; font-size: 12px; font-weight: 500;
            background: transparent; border: 0.5px solid rgba(194,105,126,0.15);
            color: #B8A0A8; text-decoration: none; display: inline-block;
            transition: all 0.2s;
        }
        .tab-btn.active { background: #C2697E; border-color: #C2697E; color: white; }
        .tab-btn:active { transform: scale(0.95); }

        .back-btn {
            width: 40px; height: 40px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 12px rgba(194,105,126,0.06);
            border: 0.5px solid rgba(194,105,126,0.06);
            text-decoration: none; color: #C2697E; font-size: 18px;
        }
        .back-btn:active { transform: scale(0.92); }

        .logout-fab {
            position: fixed; bottom: 80px; right: 16px; z-index: 60;
            background: linear-gradient(135deg, #C2697E, #D98E9F);
            color: white; width: 52px; height: 52px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(194,105,126,0.3);
            text-decoration: none; transition: all 0.3s ease;
        }
        .logout-fab:active { transform: scale(0.92); }
        @media (min-width: 768px) { .logout-fab { display: none; } }

        .success-msg {
            background: #D1FAE5; border-radius: 12px; padding: 12px 16px;
            color: #065F46; font-weight: 500; font-size: 13px;
            border: 0.5px solid #A7F3D0; margin-bottom: 16px;
        }

        .error-msg {
            background: #FEE2E2; border-radius: 12px; padding: 12px 16px;
            color: #DC2626; font-weight: 500; font-size: 13px;
            border: 0.5px solid #FECACA; margin-bottom: 16px;
        }

        .help-text {
            font-size: 10px; color: #B8A0A8; margin-top: 4px;
        }

        .toggle-switch {
            position: relative; display: inline-block; width: 44px; height: 24px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background: #E5E7EB; transition: .3s; border-radius: 34px;
        }
        .toggle-slider:before {
            position: absolute; content: ""; height: 18px; width: 18px;
            left: 3px; bottom: 3px; background: white; transition: .3s; border-radius: 50%;
        }
        input:checked + .toggle-slider { background: #C2697E; }
        input:checked + .toggle-slider:before { transform: translateX(20px); }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            backdrop-filter: blur(12px); z-index: 100;
            display: none; align-items: center; justify-content: center;
            padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        .modal-content {
            background: white; border-radius: 16px;
            max-width: 500px; width: 100%; max-height: 90vh;
            overflow-y: auto; padding: 24px;
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .btn-secondary {
            background: #F8F4F6; color: #6B5A62; border: none;
            padding: 10px 20px; border-radius: 12px; font-size: 13px;
            font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-secondary:hover { background: #EDE4E8; }

        @media (max-width: 480px) {
            .setting-card { padding: 16px; }
        }
    </style>
</head>
<body>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item"><span class="nav-icon">✦</span><span>Home</span></a>
    <a href="orders.php" class="nav-item"><span class="nav-icon">◌</span><span>Orders</span></a>
    <a href="products.php" class="nav-item"><span class="nav-icon">◍</span><span>Studio</span></a>
    <a href="finance.php" class="nav-item"><span class="nav-icon">◊</span><span>Finance</span></a>
    <a href="settings.php" class="nav-item active"><span class="nav-icon">◎</span><span>Settings</span></a>
</div>

<!-- Floating Logout -->
<a href="../logout.php" class="logout-fab" title="Sign out">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
    </svg>
</a>

<!-- Desktop Sidebar -->
<div class="desktop-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">✦</div>
        <div>
            <div class="sidebar-brand-text">Wrapped <span>by Vee</span></div>
            <div class="sidebar-brand-sub">Studio Admin</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php"><span class="nav-dot"></span>Dashboard</a>
        <a href="orders.php"><span class="nav-dot"></span>Orders</a>
        <a href="products.php"><span class="nav-dot"></span>Studio</a>
        <a href="finance.php"><span class="nav-dot"></span>Finance</a>
        <a href="settings.php" class="active"><span class="nav-dot"></span>Settings</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
        <div class="user-role">Administrator</div>
        <a href="../logout.php" class="logout-link">Sign out →</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <a href="index.php" class="back-btn"><span>←</span></a>
            <div>
                <h1 class="text-2xl font-bold text-rose-700 tracking-tight">Settings</h1>
                <p class="text-sm text-gray-400 mt-0.5">Manage your store configuration</p>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-msg">
                ✓ 
                <?php 
                    if ($_GET['success'] == '1') echo 'Settings updated successfully!';
                    elseif ($_GET['success'] == 'zone_added') echo 'Delivery zone added successfully!';
                    elseif ($_GET['success'] == 'zone_updated') echo 'Delivery zone updated successfully!';
                    elseif ($_GET['success'] == 'zone_deleted') echo 'Delivery zone deleted successfully!';
                    elseif ($_GET['success'] == 'password_updated') echo 'Password updated successfully!';
                    elseif ($_GET['success'] == 'email_updated') echo 'Email updated successfully!';
                ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2 flex-wrap">
            <a href="?tab=homepage" class="tab-btn <?php echo $activeTab === 'homepage' ? 'active' : ''; ?>">Homepage</a>
            <a href="?tab=branding" class="tab-btn <?php echo $activeTab === 'branding' ? 'active' : ''; ?>">Branding</a>
            <a href="?tab=general" class="tab-btn <?php echo $activeTab === 'general' ? 'active' : ''; ?>">General</a>
            <a href="?tab=social" class="tab-btn <?php echo $activeTab === 'social' ? 'active' : ''; ?>">Social</a>
            <a href="?tab=payment" class="tab-btn <?php echo $activeTab === 'payment' ? 'active' : ''; ?>">Payment</a>
            <a href="?tab=delivery" class="tab-btn <?php echo $activeTab === 'delivery' ? 'active' : ''; ?>">Delivery Zones</a>
            <a href="?tab=admin" class="tab-btn <?php echo $activeTab === 'admin' ? 'active' : ''; ?>">Admin</a>
        </div>

        <!-- ============================================ -->
        <!-- HOMEPAGE SETTINGS -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'homepage'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">Homepage Content</h2>
            <p class="text-sm text-gray-400 mb-4">Edit the content displayed on your homepage</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_homepage">
                
                <?php
                $homepageFields = [
                    'hero_badge_text' => 'Hero Badge Text',
                    'hero_title_text' => 'Hero Title',
                    'hero_tagline' => 'Hero Tagline',
                    'hero_location' => 'Hero Location',
                    'features_title' => 'Features Title',
                    'features_subtitle' => 'Features Subtitle',
                    'quote_text' => 'Quote Text',
                    'quote_author' => 'Quote Author',
                    'scroll_banner_items' => 'Scroll Banner Items',
                    'footer_tagline' => 'Footer Tagline',
                    'footer_location' => 'Footer Location'
                ];
                
                foreach ($homepageFields as $key => $label):
                    $isTextarea = in_array($key, ['quote_text']);
                ?>
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;"><?php echo $label; ?></label>
                    <?php if ($isTextarea): ?>
                        <textarea name="<?php echo $key; ?>" class="form-control textarea" rows="2"><?php echo htmlspecialchars($homepage[$key] ?? ''); ?></textarea>
                    <?php else: ?>
                        <input type="text" name="<?php echo $key; ?>" class="form-control" value="<?php echo htmlspecialchars($homepage[$key] ?? ''); ?>">
                    <?php endif; ?>
                    <?php if ($key === 'scroll_banner_items'): ?>
                        <p class="help-text">Separate items with · (dot)</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <!-- Testimonials -->
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Testimonial <?php echo $i; ?></label>
                    <textarea name="testimonial_<?php echo $i; ?>_text" class="form-control textarea" rows="2"><?php echo htmlspecialchars($homepage['testimonial_'.$i.'_text'] ?? ''); ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                    <input type="text" name="testimonial_<?php echo $i; ?>_name" class="form-control" placeholder="Name" value="<?php echo htmlspecialchars($homepage['testimonial_'.$i.'_name'] ?? ''); ?>">
                    <input type="text" name="testimonial_<?php echo $i; ?>_location" class="form-control" placeholder="Location" value="<?php echo htmlspecialchars($homepage['testimonial_'.$i.'_location'] ?? ''); ?>">
                </div>
                <?php endfor; ?>

                <button type="submit" class="btn-primary">Save Homepage Settings</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- BRANDING SETTINGS -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'branding'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">Branding</h2>
            <p class="text-sm text-gray-400 mb-4">Customize your brand identity</p>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_branding">
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Brand Name</label>
                    <input type="text" name="brand_name" class="form-control" value="<?php echo htmlspecialchars($branding['brand_name'] ?? 'Wrapped by Vee'); ?>">
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Tagline</label>
                    <input type="text" name="brand_tagline" class="form-control" value="<?php echo htmlspecialchars($branding['brand_tagline'] ?? 'Where flowers tell stories'); ?>">
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Brand Color</label>
                    <input type="color" name="brand_color" class="form-control" style="padding:4px;height:50px;" value="<?php echo htmlspecialchars($branding['brand_color'] ?? '#C2697E'); ?>">
                </div>
                
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Logo</label>
                    <?php if (!empty($branding['brand_logo']) && file_exists('../' . $branding['brand_logo'])): ?>
                        <div style="margin-bottom:8px;">
                            <img src="../<?php echo htmlspecialchars($branding['brand_logo']); ?>" style="max-width:120px;max-height:60px;border-radius:8px;border:1px solid #EDE4E8;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="brand_logo" class="form-control" accept="image/*" style="padding:8px;">
                    <p class="help-text">Recommended: PNG or JPG, max 2MB</p>
                </div>
                
                <button type="submit" class="btn-primary">Save Branding</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- GENERAL SETTINGS -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'general'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">General Settings</h2>
            <p class="text-sm text-gray-400 mb-4">Contact and business information</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_general">
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Site Name</label>
                    <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($general['site_name'] ?? 'Wrapped by Vee'); ?>">
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Contact Email</label>
                    <input type="email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($general['site_email'] ?? 'info@wrappedbyvee.com'); ?>">
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Phone Number</label>
                    <input type="text" name="site_phone" class="form-control" value="<?php echo htmlspecialchars($general['site_phone'] ?? '+255 755 555 555'); ?>">
                </div>
                
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Address</label>
                    <input type="text" name="site_address" class="form-control" value="<?php echo htmlspecialchars($general['site_address'] ?? 'Dodoma, Tanzania'); ?>">
                </div>
                
                <button type="submit" class="btn-primary">Save General Settings</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- SOCIAL SETTINGS -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'social'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">Social Media</h2>
            <p class="text-sm text-gray-400 mb-4">Connect your social media profiles</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_social">
                
                <?php
                $socialFields = [
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                    'twitter' => 'Twitter/X',
                    'pinterest' => 'Pinterest',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok',
                    'whatsapp' => 'WhatsApp'
                ];
                foreach ($socialFields as $key => $label):
                ?>
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;"><?php echo $label; ?></label>
                    <input type="text" name="<?php echo $key; ?>" class="form-control" placeholder="https://<?php echo $key; ?>.com/yourpage" value="<?php echo htmlspecialchars($social[$key] ?? ''); ?>">
                    <?php if ($key === 'whatsapp'): ?>
                        <p class="help-text">For WhatsApp chat link: +255755555555</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn-primary">Save Social Settings</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- PAYMENT SETTINGS - FIXED WITH CASH TAX -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'payment'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">Payment Settings</h2>
            <p class="text-sm text-gray-400 mb-4">Configure payment methods and tax amounts (in TZS cash)</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_payment">
                
                <!-- M-Pesa -->
                <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="mpesa_enabled" <?php echo isset($payment['mpesa_enabled']) && $payment['mpesa_enabled'] == '1' ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-weight:600;color:#3B2229;">M-Pesa</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Phone Number</label>
                            <input type="text" name="mpesa_phone" class="form-control" style="font-size:12px;" placeholder="0755 555 555" value="<?php echo htmlspecialchars($payment['mpesa_phone'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Tax Amount (TZS)</label>
                            <input type="number" name="mpesa_tax" class="form-control" style="font-size:12px;" step="100" placeholder="e.g., 500" value="<?php echo htmlspecialchars($payment['mpesa_tax'] ?? '0'); ?>">
                            <p class="help-text">Fixed tax amount in TZS for M-Pesa payments</p>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Transfer -->
                <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="bank_enabled" <?php echo isset($payment['bank_enabled']) && $payment['bank_enabled'] == '1' ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-weight:600;color:#3B2229;">Bank Transfer</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" style="font-size:12px;" placeholder="CRDB Bank" value="<?php echo htmlspecialchars($payment['bank_name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Account Number</label>
                            <input type="text" name="bank_account" class="form-control" style="font-size:12px;" placeholder="1234567890" value="<?php echo htmlspecialchars($payment['bank_account'] ?? ''); ?>">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Account Name</label>
                            <input type="text" name="bank_account_name" class="form-control" style="font-size:12px;" placeholder="Wrapped by Vee Ltd" value="<?php echo htmlspecialchars($payment['bank_account_name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Tax Amount (TZS)</label>
                            <input type="number" name="bank_tax" class="form-control" style="font-size:12px;" step="100" placeholder="e.g., 1000" value="<?php echo htmlspecialchars($payment['bank_tax'] ?? '0'); ?>">
                            <p class="help-text">Fixed tax amount in TZS for Bank Transfer payments</p>
                        </div>
                    </div>
                </div>
                
                <!-- Default Tax -->
                <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                        <span style="font-weight:600;color:#3B2229;">Default Tax Amount</span>
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:600;color:#6B5A62;display:block;margin-bottom:2px;">Default Tax (TZS)</label>
                        <input type="number" name="default_tax" class="form-control" step="100" placeholder="e.g., 0" value="<?php echo htmlspecialchars($payment['default_tax'] ?? '0'); ?>">
                        <p class="help-text">Default fixed tax amount in TZS when payment method has no specific tax</p>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Save Payment Settings</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- DELIVERY ZONES -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'delivery'): ?>
        <div class="setting-card">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="font-semibold text-gray-800 text-lg">Delivery Zones</h2>
                    <p class="text-sm text-gray-400">Manage delivery zones and fees</p>
                </div>
                <button onclick="openAddZoneModal()" class="btn-primary" style="padding:8px 16px;font-size:12px;">+ Add Zone</button>
            </div>
            
            <?php if (count($deliveryZones) > 0): ?>
                <div class="overflow-x-auto">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="background:#F8F4F6;border-bottom:1px solid #EDE4E8;">
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#6B5A62;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Zone Name</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#6B5A62;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Delivery Fee</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#6B5A62;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Est. Days</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#6B5A62;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Status</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#6B5A62;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deliveryZones as $zone): ?>
                                <tr style="border-bottom:0.5px solid #F5F0F2;">
                                    <td style="padding:10px 12px;font-weight:500;color:#3B2229;"><?php echo htmlspecialchars($zone['zone_name']); ?></td>
                                    <td style="padding:10px 12px;color:#6B5A62;">TZS <?php echo number_format($zone['delivery_fee'], 0); ?></td>
                                    <td style="padding:10px 12px;color:#6B5A62;"><?php echo htmlspecialchars($zone['estimated_days'] ?? '2-3 days'); ?></td>
                                    <td style="padding:10px 12px;">
                                        <span style="padding:2px 10px;border-radius:20px;font-size:10px;font-weight:500;background:<?php echo $zone['is_active'] ? '#D1FAE5' : '#FEF3C7'; ?>;color:<?php echo $zone['is_active'] ? '#065F46' : '#92400E'; ?>;">
                                            <?php echo $zone['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <a href="?tab=delivery&edit_zone=<?php echo $zone['id']; ?>" class="btn-sm" style="display:inline-block;margin-right:4px;">Edit</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this delivery zone?')">
                                            <input type="hidden" name="action" value="delete_delivery_zone">
                                            <input type="hidden" name="zone_id" value="<?php echo $zone['id']; ?>">
                                            <button type="submit" class="btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align:center;padding:30px 0;color:#B8A0A8;">
                    <div style="font-size:32px;margin-bottom:6px;">📍</div>
                    <p style="font-size:13px;">No delivery zones configured</p>
                    <p style="font-size:11px;margin-top:4px;">Click "Add Zone" to create your first delivery zone</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Zone Modal -->
        <div id="zone-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-lg text-stone-800" id="zone-modal-title">Add Delivery Zone</h2>
                    <button onclick="closeZoneModal()" class="text-gray-400 text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">&times;</button>
                </div>
                
                <form method="POST" id="zone-form">
                    <input type="hidden" name="action" id="zone-action" value="add_delivery_zone">
                    <input type="hidden" name="zone_id" id="zone-id" value="">
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Zone Name *</label>
                        <input type="text" name="zone_name" id="zone-name" class="form-control" placeholder="e.g., Dodoma CBD" required>
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Delivery Fee (TZS) *</label>
                        <input type="number" name="delivery_fee" id="zone-fee" class="form-control" placeholder="e.g., 5000" required>
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Estimated Days</label>
                        <input type="text" name="estimated_days" id="zone-days" class="form-control" placeholder="e.g., 2-3 days" value="2-3 days">
                    </div>
                    
                    <div style="margin-bottom:16px;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#6B5A62;">
                            <input type="checkbox" name="is_active" id="zone-active" checked style="accent-color:#C2697E;width:18px;height:18px;">
                            Active (visible to customers)
                        </label>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary flex-1" id="zone-submit-btn">Add Zone</button>
                        <button type="button" class="btn-secondary flex-1" onclick="closeZoneModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openAddZoneModal() {
                document.getElementById('zone-modal-title').textContent = 'Add Delivery Zone';
                document.getElementById('zone-action').value = 'add_delivery_zone';
                document.getElementById('zone-id').value = '';
                document.getElementById('zone-name').value = '';
                document.getElementById('zone-fee').value = '';
                document.getElementById('zone-days').value = '2-3 days';
                document.getElementById('zone-active').checked = true;
                document.getElementById('zone-submit-btn').textContent = 'Add Zone';
                document.getElementById('zone-modal').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            <?php if ($editZone): ?>
            function openEditZoneModal() {
                document.getElementById('zone-modal-title').textContent = 'Edit Delivery Zone';
                document.getElementById('zone-action').value = 'update_delivery_zone';
                document.getElementById('zone-id').value = '<?php echo $editZone['id']; ?>';
                document.getElementById('zone-name').value = '<?php echo addslashes($editZone['zone_name']); ?>';
                document.getElementById('zone-fee').value = '<?php echo $editZone['delivery_fee']; ?>';
                document.getElementById('zone-days').value = '<?php echo addslashes($editZone['estimated_days'] ?? '2-3 days'); ?>';
                document.getElementById('zone-active').checked = <?php echo $editZone['is_active'] ? 'true' : 'false'; ?>;
                document.getElementById('zone-submit-btn').textContent = 'Update Zone';
                document.getElementById('zone-modal').classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($editZone): ?>
                openEditZoneModal();
                <?php endif; ?>
            });
            <?php endif; ?>

            function closeZoneModal() {
                document.getElementById('zone-modal').classList.remove('open');
                document.body.style.overflow = '';
            }

            document.getElementById('zone-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeZoneModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeZoneModal();
                }
            });
        </script>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- ADMIN SETTINGS -->
        <!-- ============================================ -->
        <?php if ($activeTab === 'admin'): ?>
        <div class="setting-card">
            <h2 class="font-semibold text-gray-800 text-lg mb-4">Admin Account</h2>
            <p class="text-sm text-gray-400 mb-4">Manage your admin account credentials</p>
            
            <!-- Update Email -->
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <h3 class="font-medium text-gray-700 text-sm mb-3">Update Email</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_admin_email">
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Current Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($adminInfo['username']); ?>" disabled style="background:#F5F0F2;color:#6B5A62;">
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">New Email Address</label>
                        <input type="email" name="admin_email" class="form-control" placeholder="admin@wrappedbyvee.com" value="<?php echo htmlspecialchars($adminInfo['email'] ?? ''); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:12px;">Update Email</button>
                </form>
            </div>
            
            <!-- Update Password -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                <h3 class="font-medium text-gray-700 text-sm mb-3">Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_admin_password">
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password (min 6 characters)" required>
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:12px;">Change Password</button>
                </form>
            </div>
            
            <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200">
                <p style="font-size:12px;color:#92400E;">
                    ⚠️ Keep your credentials secure. Never share your password with anyone.
                </p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
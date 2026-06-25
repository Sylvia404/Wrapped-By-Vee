<?php
// ============================================
// SECTION 1: ERROR REPORTING & CONFIGURATION
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wrapped_by_vee');
define('DB_USER', 'root');
define('DB_PASS', '');

// ============================================
// SECTION 2: DATABASE CONNECTION
// ============================================
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
            PDO::ATTR_TIMEOUT => 30,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function executeQuery($sql, $params = []) {
    try {
        $db = getDB();
        if (!$db) return null;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return null;
    }
}

// ============================================
// SECTION 3: DATABASE FUNCTIONS
// ============================================
function getAllProducts() {
    try {
        // IMPORTANT: Select ALL fields including tax_mpesa and tax_bank
        $stmt = executeQuery("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Exception $e) {
        error_log("Error in getAllProducts: " . $e->getMessage());
        return [];
    }
}

function getGeneralSettings() {
    try {
        $stmt = executeQuery("SELECT * FROM settings WHERE setting_group = 'general'");
        if (!$stmt) return ['site_name' => 'Wrapped by Vee', 'site_email' => 'info@wrappedbyvee.com', 'site_phone' => '+255 755 555 555'];
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge(['site_name' => 'Wrapped by Vee', 'site_email' => 'info@wrappedbyvee.com', 'site_phone' => '+255 755 555 555'], $settings);
    } catch (Exception $e) {
        return ['site_name' => 'Wrapped by Vee', 'site_email' => 'info@wrappedbyvee.com', 'site_phone' => '+255 755 555 555'];
    }
}

function getBrandingSettings() {
    try {
        $stmt = executeQuery("SELECT * FROM settings WHERE setting_group = 'branding'");
        if (!$stmt) return ['brand_name' => 'Wrapped by Vee', 'brand_logo' => '', 'brand_color' => '#C2697E', 'brand_tagline' => 'Where flowers tell stories'];
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge(['brand_name' => 'Wrapped by Vee', 'brand_logo' => '', 'brand_color' => '#C2697E', 'brand_tagline' => 'Where flowers tell stories'], $settings);
    } catch (Exception $e) {
        return ['brand_name' => 'Wrapped by Vee', 'brand_logo' => '', 'brand_color' => '#C2697E', 'brand_tagline' => 'Where flowers tell stories'];
    }
}

function getHomepageSettings() {
    try {
        $stmt = executeQuery("SELECT * FROM settings WHERE setting_group = 'homepage'");
        if (!$stmt) return [];
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

function getDeliveryZones() {
    try {
        $stmt = executeQuery("SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY zone_name");
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Exception $e) {
        return [];
    }
}

function getDeliverySettings() {
    try {
        $stmt = executeQuery("SELECT * FROM settings WHERE setting_group = 'delivery'");
        if (!$stmt) return ['free_delivery_threshold' => 50000];
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge(['free_delivery_threshold' => 50000], $settings);
    } catch (Exception $e) {
        return ['free_delivery_threshold' => 50000];
    }
}

function getPaymentMethods() {
    try {
        $stmt = executeQuery("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY id");
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Exception $e) {
        return [];
    }
}

function getPaymentSettings() {
    try {
        $stmt = executeQuery("SELECT * FROM settings WHERE setting_group = 'payment'");
        if (!$stmt) {
            return [
                'mpesa_enabled' => '1',
                'mpesa_phone' => '+255 755 555 555',
                'mpesa_tax' => '0',
                'bank_enabled' => '1',
                'bank_name' => 'CRDB Bank',
                'bank_account' => '1234567890',
                'bank_account_name' => 'Wrapped by Vee Ltd',
                'bank_tax' => '0'
            ];
        }
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'mpesa_enabled' => '1',
            'mpesa_phone' => '+255 755 555 555',
            'mpesa_tax' => '0',
            'bank_enabled' => '1',
            'bank_name' => 'CRDB Bank',
            'bank_account' => '1234567890',
            'bank_account_name' => 'Wrapped by Vee Ltd',
            'bank_tax' => '0'
        ], $settings);
    } catch (Exception $e) {
        return [
            'mpesa_enabled' => '1',
            'mpesa_phone' => '+255 755 555 555',
            'mpesa_tax' => '0',
            'bank_enabled' => '1',
            'bank_name' => 'CRDB Bank',
            'bank_account' => '1234567890',
            'bank_account_name' => 'Wrapped by Vee Ltd',
            'bank_tax' => '0'
        ];
    }
}

// ============================================
// SECTION 4: LOAD ALL DATA
// ============================================
$products = getAllProducts();
$homepage = getHomepageSettings();
$general = getGeneralSettings();
$branding = getBrandingSettings();
$deliveryZones = getDeliveryZones();
$deliverySettings = getDeliverySettings();
$paymentMethods = getPaymentMethods();
$paymentSettings = getPaymentSettings();

// Set variables
$siteName = $general['site_name'] ?? 'Wrapped by Vee';
$siteEmail = $general['site_email'] ?? 'info@wrappedbyvee.com';
$sitePhone = $general['site_phone'] ?? '+255 755 555 555';
$siteAddress = $general['site_address'] ?? 'Dodoma, Tanzania';
$brandLogo = $branding['brand_logo'] ?? '';
$brandName = $branding['brand_name'] ?? 'Wrapped by Vee';
$brandTagline = $branding['brand_tagline'] ?? 'Where flowers tell stories';
$brandColor = $branding['brand_color'] ?? '#C2697E';

// Parse scroll banner items
$scrollItems = array_map('trim', explode('·', $homepage['scroll_banner_items'] ?? '✦ Fresh Bouquets Daily · Same-Day Dodoma Delivery · Handcrafted with Love · Custom Gift Wrapping · Weddings & Events · Corporate Orders Welcome'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover" />
    <title><?php echo htmlspecialchars($siteName); ?> — <?php echo htmlspecialchars($brandTagline); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
    <style>
        :root {
            --rose:        <?php echo $brandColor; ?>;
            --rose-light:  <?php echo $brandColor; ?>cc;
            --rose-deep:   <?php echo $brandColor; ?>aa;
            --cream:       #FFF8F9;
            --cream-mid:   #F5EBED;
            --cream-warm:  #EDD8DC;
            --text-dark:   #3B2229;
            --text-mid:    #6E4050;
            --text-light:  #9C7280;
            --green-1:     #8BAD6E;
            --green-2:     #7A9C5E;
            --green-3:     #9BBF73;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Jost', sans-serif;
            background: var(--cream);
            color: var(--text-dark);
            overflow-x: hidden;
            font-size: 16px;
        }
        @media (min-width: 768px) { body { font-size: 18px; } }
        @media (min-width: 1024px) { body { font-size: 20px; } }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        button { cursor: pointer; font-family: inherit; }
        img { display: block; max-width: 100%; }

        #petal-canvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .petal {
            position: absolute;
            border-radius: 60% 10% 60% 10%;
            opacity: 0;
            animation: petalFall linear infinite;
        }
        @keyframes petalFall {
            0%   { transform: translateY(-40px) rotate(0deg) translateX(0px); opacity: 0; }
            8%   { opacity: 0.65; }
            85%  { opacity: 0.45; }
            100% { transform: translateY(105vh) rotate(380deg) translateX(70px); opacity: 0; }
        }

        .nav {
            position: sticky;
            top: 0;
            z-index: 90;
            background: rgba(255,248,249,0.94);
            backdrop-filter: blur(10px);
            border-bottom: 0.5px solid var(--cream-warm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            height: 66px;
            gap: 12px;
        }
        @media (min-width: 768px) { .nav { padding: 0 48px; gap: 24px; height: 82px; } }
        .nav-logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            position: relative;
            padding: 4px 0;
        }
        .nav-logo-ring {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            padding: 2.5px;
            background: linear-gradient(140deg, var(--rose-light) 0%, var(--rose) 45%, #D8B36A 100%);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            box-shadow: 0 2px 10px rgba(194,105,126,0.22);
        }
        .nav-logo-area:hover .nav-logo-ring {
            transform: scale(1.06);
            box-shadow: 0 4px 18px rgba(194,105,126,0.35);
        }
        .nav-logo-img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--cream);
            display: block;
            flex-shrink: 0;
        }
        @media (min-width: 768px) { .nav-logo-img { width: 50px; height: 50px; border-width: 2.5px; } }
        .nav-logo-img.placeholder {
            background: linear-gradient(135deg, var(--cream-warm), var(--rose-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--rose);
        }
        .nav-brand-text {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 500;
            color: var(--text-dark);
            letter-spacing: 0.01em;
            white-space: nowrap;
            line-height: 1.15;
        }
        @media (min-width: 768px) { .nav-brand-text { font-size: 28px; } }
        .nav-brand-text small {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 7.5px;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            color: var(--rose-deep);
            font-family: 'Jost', sans-serif;
            font-weight: 500;
            margin-top: 2px;
        }
        @media (min-width: 768px) { .nav-brand-text small { font-size: 10px; letter-spacing: 0.36em; margin-top: 4px; } }
        .nav-brand-text small::before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--rose-light));
        }
        .admin-access-hint {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--rose);
            opacity: 0.15;
            transition: opacity 0.3s;
        }
        .nav-logo-area:hover .admin-access-hint { opacity: 0.3; }
        .nav-links {
            display: none;
            gap: 30px;
        }
        @media (min-width: 768px) { .nav-links { display: flex; } }
        .nav-links a {
            font-size: 13px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-mid);
            font-weight: 400;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--rose); }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        @media (min-width: 768px) { .nav-right { gap: 18px; } }
        .nav-icon {
            font-size: 18px;
            color: var(--text-mid);
            background: none;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            transition: background 0.2s, color 0.2s;
        }
        .nav-icon:hover { background: var(--cream-mid); color: var(--rose); }
        .nav-cart {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--rose);
            color: #fff;
            border: none;
            padding: 8px 14px;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border-radius: 2px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav-cart:hover { background: var(--rose-deep); }
        .nav-cart span { display: none; }
        @media (min-width: 768px) { .nav-cart { padding: 10px 18px; font-size: 13px; gap: 7px; } .nav-cart span { display: inline; } }

        .hero {
            position: relative;
            min-height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 1;
        }
        @media (min-width: 768px) { .hero { min-height: 480px; } }
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 1;
            background-image: url('https://images.unsplash.com/photo-1526047932273-341f2a7631f9?w=1600&q=80');
            background-size: cover;
            background-position: center;
            transform: scale(1.05);
            transition: transform 8s ease;
        }
        .hero:hover .hero-bg { transform: scale(1); }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,248,249,0.88) 0%, rgba(255,248,249,0.75) 30%, rgba(255,248,249,0.40) 60%, rgba(255,248,249,0.20) 80%);
            z-index: 2;
        }
        .hero-glass {
            position: relative;
            z-index: 10;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 28px 24px;
            max-width: 600px;
            margin: 0 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08), inset 0 1px 0 rgba(255,255,255,0.5);
            text-align: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        @media (min-width: 768px) { .hero-glass { padding: 40px 48px; max-width: 680px; margin: 0 40px; border-radius: 28px; } }
        .hero-glass:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.6);
        }
        .hero-content { position: relative; z-index: 5; }
        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 10px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 12px;
            font-weight: 400;
            justify-content: center;
        }
        @media (min-width: 768px) { .hero-eyebrow { font-size: 12px; } }
        .eyebrow-dot { width: 4px; height: 4px; border-radius: 50%; background: var(--rose-light); display: inline-block; }
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            line-height: 1.06;
            font-weight: 400;
            color: var(--text-dark);
            margin-bottom: 12px;
            text-shadow: 0 2px 20px rgba(255,255,255,0.3);
        }
        @media (min-width: 768px) { .hero-title { font-size: 52px; margin-bottom: 18px; } }
        .hero-title em { color: var(--rose); font-style: italic; }
        .hero-tagline {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            font-style: italic;
            color: var(--text-mid);
            margin-bottom: 6px;
            font-weight: 300;
        }
        @media (min-width: 768px) { .hero-tagline { font-size: 20px; } }
        .hero-location {
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        @media (min-width: 768px) { .hero-location { font-size: 13px; margin-bottom: 28px; } }
        .hero-location::before { content: '✦  '; }
        .hero-cta {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: var(--rose);
            color: #fff;
            border: none;
            padding: 12px 22px;
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border-radius: 2px;
            font-weight: 500;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        @media (min-width: 768px) { .btn-primary { padding: 14px 28px; font-size: 14px; } }
        .btn-primary:hover {
            background: var(--rose-deep);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(194,105,126,0.35);
        }
        .btn-ghost {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(8px);
            color: var(--rose);
            border: 0.5px solid var(--rose);
            padding: 12px 20px;
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border-radius: 2px;
            font-weight: 400;
            transition: background 0.2s, color 0.2s, transform 0.15s;
        }
        @media (min-width: 768px) { .btn-ghost { padding: 14px 24px; font-size: 14px; } }
        .btn-ghost:hover {
            background: var(--rose);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(194,105,126,0.25);
        }

        .scroll-banner {
            background: var(--rose);
            padding: 8px 0;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }
        @media (min-width: 768px) { .scroll-banner { padding: 10px 0; } }
        .scroll-track {
            display: flex;
            gap: 30px;
            animation: scrollTrack 24s linear infinite;
            white-space: nowrap;
            width: max-content;
        }
        .scroll-track:hover { animation-play-state: paused; }
        @keyframes scrollTrack {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .scroll-item {
            font-size: 10px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.88);
            font-weight: 300;
        }
        @media (min-width: 768px) { .scroll-item { font-size: 12px; } }
        .scroll-sep { color: rgba(255,255,255,0.4); }

        .section-label {
            text-align: center;
            padding: 28px 16px 12px;
        }
        @media (min-width: 768px) { .section-label { padding: 48px 48px 20px; } }
        .section-eyebrow {
            font-size: 10px;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 6px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 400;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        @media (min-width: 768px) { .section-title { font-size: 36px; margin-bottom: 8px; } }
        .section-sub {
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            font-style: italic;
            color: var(--text-light);
            font-weight: 300;
        }
        @media (min-width: 768px) { .section-sub { font-size: 18px; } }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 0 10px 24px;
        }
        @media (min-width: 600px) { .product-grid { gap: 12px; padding: 0 16px 32px; } }
        @media (min-width: 768px) { .product-grid { grid-template-columns: repeat(4, 1fr); gap: 14px; padding: 0 24px 40px; } }
        .product-card {
            background: #fff;
            border: 0.5px solid var(--cream-warm);
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(194,105,126,0.12);
        }
        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(160deg, #f9e8ec, #f0c8d2, #e8a8b8);
        }
        @media (min-width: 768px) { .product-img { aspect-ratio: 4/5; max-height: 280px; } }
        @media (min-width: 1024px) { .product-img { max-height: 320px; } }
        .product-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-img .placeholder-icon {
            font-size: 40px;
            opacity: 0.3;
            color: var(--rose-light);
        }
        .badge {
            position: absolute;
            top: 6px;
            left: 6px;
            background: var(--rose);
            color: #fff;
            font-size: 9px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 2px;
            font-weight: 500;
            z-index: 2;
        }
        @media (min-width: 768px) { .badge { top: 10px; left: 10px; font-size: 10px; padding: 4px 10px; } }
        .badge-dark { background: var(--text-dark); }
        .wish-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            border: 0.5px solid var(--cream-warm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--rose);
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            z-index: 2;
        }
        @media (min-width: 768px) { .wish-btn { top: 10px; right: 10px; width: 30px; height: 30px; font-size: 16px; } }
        .wish-btn:hover { background: var(--rose); color: #fff; }
        .product-info { padding: 10px 12px 12px; }
        @media (min-width: 768px) { .product-info { padding: 14px 16px 18px; } }
        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            font-weight: 400;
            color: var(--text-dark);
            margin-bottom: 3px;
        }
        @media (min-width: 768px) { .product-name { font-size: 18px; margin-bottom: 5px; } }
        .product-desc {
            font-size: 11px;
            color: var(--text-light);
            line-height: 1.4;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        @media (min-width: 768px) { .product-desc { font-size: 14px; margin-bottom: 12px; } }
        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .product-price {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            color: var(--rose);
            font-weight: 400;
        }
        @media (min-width: 768px) { .product-price { font-size: 22px; } }
        .product-price-unit {
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            color: var(--text-light);
            font-weight: 300;
            display: block;
        }
        @media (min-width: 768px) { .product-price-unit { font-size: 12px; } }
        .add-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--cream-mid);
            border: 0.5px solid var(--cream-warm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--rose);
            cursor: pointer;
            transition: background 0.2s, color 0.2s, transform 0.15s;
            line-height: 1;
        }
        @media (min-width: 768px) { .add-btn { width: 36px; height: 36px; font-size: 20px; } }
        .add-btn:hover { background: var(--rose); color: #fff; transform: scale(1.08); }
        .add-btn.in-cart { background: var(--green-2); color: #fff; }

        .quote-section {
            background: var(--cream-mid);
            border-top: 0.5px solid var(--cream-warm);
            border-bottom: 0.5px solid var(--cream-warm);
            padding: 24px 20px;
            text-align: center;
        }
        @media (min-width: 768px) { .quote-section { padding: 48px 48px; } }
        .quote-mark {
            font-family: 'Playfair Display', serif;
            font-size: 40px;
            color: var(--cream-warm);
            line-height: 0.5;
            display: block;
            margin-bottom: 10px;
            user-select: none;
        }
        @media (min-width: 768px) { .quote-mark { font-size: 60px; margin-bottom: 14px; } }
        .quote-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-style: italic;
            font-weight: 300;
            color: var(--text-dark);
            max-width: 560px;
            margin: 0 auto 12px;
            line-height: 1.5;
        }
        @media (min-width: 768px) { .quote-text { font-size: 28px; margin-bottom: 18px; } }
        .quote-attr {
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--text-light);
        }
        @media (min-width: 768px) { .quote-attr { font-size: 12px; } }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 0 10px;
            margin: 20px 0;
        }
        @media (min-width: 1024px) { .features { grid-template-columns: repeat(4, 1fr); padding: 0 40px; margin: 40px 0; } }
        .feature-item {
            text-align: center;
            padding: 12px 10px;
            border-bottom: 0.5px solid var(--cream-warm);
        }
        @media (min-width: 600px) { .feature-item:nth-child(odd) { border-right: 0.5px solid var(--cream-warm); } }
        @media (min-width: 1024px) {
            .feature-item { border-bottom: none; border-right: 0.5px solid var(--cream-warm); padding: 28px 20px; }
            .feature-item:last-child { border-right: none; }
        }
        .feature-icon {
            font-size: 22px;
            color: var(--rose-light);
            display: block;
            margin-bottom: 6px;
        }
        @media (min-width: 768px) { .feature-icon { font-size: 28px; margin-bottom: 10px; } }
        .feature-title {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 3px;
            letter-spacing: 0.04em;
        }
        @media (min-width: 768px) { .feature-title { font-size: 16px; margin-bottom: 5px; } }
        .feature-desc {
            font-size: 12px;
            color: var(--text-light);
            line-height: 1.4;
        }
        @media (min-width: 768px) { .feature-desc { font-size: 14px; } }

        .testimonials { padding: 0 10px 24px; }
        @media (min-width: 768px) { .testimonials { padding: 0 40px 48px; } }
        .testi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        @media (min-width: 600px) { .testi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .testi-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } }
        .testi-card {
            background: #fff;
            border: 0.5px solid var(--cream-warm);
            border-radius: 4px;
            padding: 14px 12px;
        }
        @media (min-width: 768px) { .testi-card { padding: 22px 20px; } }
        .testi-stars {
            color: var(--rose);
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        @media (min-width: 768px) { .testi-stars { font-size: 14px; margin-bottom: 10px; } }
        .testi-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            font-style: italic;
            font-weight: 300;
            color: var(--text-mid);
            line-height: 1.5;
            margin-bottom: 10px;
        }
        @media (min-width: 768px) { .testi-text { font-size: 16px; margin-bottom: 14px; } }
        .testi-author {
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-light);
        }
        @media (min-width: 768px) { .testi-author { font-size: 12px; } }
        .testi-author span {
            color: var(--rose);
            font-style: italic;
            text-transform: none;
            letter-spacing: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 12px;
        }

        .footer {
            background: var(--text-dark);
            padding: 28px 16px 16px;
        }
        @media (min-width: 768px) { .footer { padding: 48px 48px 28px; } }
        .footer-top {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 0.5px solid rgba(255,255,255,0.08);
            margin-bottom: 16px;
        }
        @media (min-width: 600px) { .footer-top { grid-template-columns: 1fr 1fr; gap: 28px; } }
        @media (min-width: 1024px) { .footer-top { grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 40px; } }
        .footer-brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-style: italic;
            color: var(--rose-light);
            display: block;
            margin-bottom: 6px;
        }
        @media (min-width: 768px) { .footer-brand { font-size: 28px; margin-bottom: 10px; } }
        .footer-tagline {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 14px;
            color: rgba(255,255,255,0.38);
            margin-bottom: 8px;
            font-weight: 300;
        }
        @media (min-width: 768px) { .footer-tagline { font-size: 16px; margin-bottom: 12px; } }
        .footer-location {
            font-size: 10px;
            letter-spacing: 0.14em;
            color: rgba(255,255,255,0.28);
            text-transform: uppercase;
        }
        @media (min-width: 768px) { .footer-location { font-size: 12px; } }
        .footer-col-title {
            font-size: 10px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 10px;
            font-weight: 500;
        }
        @media (min-width: 768px) { .footer-col-title { font-size: 12px; margin-bottom: 14px; } }
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        @media (min-width: 768px) { .footer-links { gap: 10px; } }
        .footer-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.52);
            transition: color 0.2s;
        }
        @media (min-width: 768px) { .footer-links a { font-size: 15px; } }
        .footer-links a:hover { color: var(--rose-light); }
        .footer-bottom {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: rgba(255,255,255,0.28);
            text-align: center;
        }
        @media (min-width: 768px) { .footer-bottom { flex-direction: row; justify-content: space-between; font-size: 13px; } }
        .footer-heart { color: var(--rose-light); vertical-align: -2px; font-size: 10px; }

        .toast {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: var(--rose);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(181, 64, 90, 0.45);
        }
        .loading-spinner {
            border: 3px solid rgba(212, 96, 122, 0.2);
            border-top: 3px solid var(--rose);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        .modal-card {
            background: white;
            border-radius: 16px;
            max-width: 460px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            border: 0.5px solid var(--cream-warm);
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-header {
            padding: 16px 20px 12px;
            border-bottom: 0.5px solid var(--cream-warm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: var(--text-dark);
        }
        @media (min-width: 768px) { .modal-header h2 { font-size: 24px; } }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-light);
            cursor: pointer;
            padding: 4px 8px;
        }
        .modal-body { padding: 16px 20px; }
        @media (min-width: 768px) { .modal-body { padding: 24px; } }
        .modal-footer {
            padding: 12px 20px 20px;
            border-top: 0.5px solid var(--cream-warm);
            display: flex;
            gap: 12px;
        }
        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--cream-warm);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Jost', sans-serif;
            color: var(--text-dark);
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        @media (min-width: 768px) { .input-field { padding: 14px 18px; font-size: 16px; } }
        .input-field:focus {
            border-color: var(--rose);
            box-shadow: 0 0 0 3px rgba(194,105,126,0.08);
        }
        .upload-zone {
            border: 2px dashed var(--cream-warm);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            background: var(--cream);
            cursor: pointer;
            transition: all 0.2s;
        }
        .upload-zone:hover {
            border-color: var(--rose);
            background: var(--cream-mid);
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            width: 100%;
            max-width: 420px;
            background: white;
            z-index: 110;
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -8px 0 40px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
        }
        .cart-drawer.open { transform: translateX(0); }
        .cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 105;
            display: none;
        }
        .cart-overlay.open { display: block; }
        .hidden-mobile { display: none; }
        @media (min-width: 768px) { .hidden-mobile { display: inline; } }

        .product-detail-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-detail-price {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            color: var(--rose);
            font-weight: 400;
        }
        @media (min-width: 768px) { .product-detail-price { font-size: 32px; } }
        .product-detail-name {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        @media (min-width: 768px) { .product-detail-name { font-size: 28px; } }
        .product-detail-desc {
            font-size: 15px;
            color: var(--text-mid);
            line-height: 1.6;
        }
        @media (min-width: 768px) { .product-detail-desc { font-size: 17px; } }

        .gift-fields {
            border: 0.5px solid var(--cream-warm);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }
        .gift-fields label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        @media (min-width: 768px) { .gift-fields label { font-size: 16px; } }
        .gift-fields .gift-details {
            display: none;
            margin-top: 10px;
        }
        .gift-fields .gift-details.active { display: block; }
        .gift-fields .gift-details .input-field { margin-bottom: 6px; }

        .success-check {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .success-check span {
            font-size: 36px;
            color: #065F46;
        }

        /* Payment Method Cards */
        .pay-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1.5px solid var(--cream-warm);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .pay-card:hover { border-color: var(--rose-light); }
        .pay-card.selected { border-color: var(--rose); background: rgba(194,105,126,0.05); }
        .pay-card input[type="radio"] { 
            width: 18px; 
            height: 18px; 
            accent-color: var(--rose);
            cursor: pointer;
            flex-shrink: 0;
        }
        .pay-card .pay-icon { font-size: 22px; flex-shrink: 0; }
        .pay-card .pay-name { font-weight: 600; font-size: 14px; color: var(--text-dark); }
        .pay-card .pay-detail { font-size: 12px; color: var(--text-light); }

        .payment-instructions {
            background: var(--cream-mid);
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 13px;
            border-left: 3px solid var(--rose);
        }
        .payment-instructions .inst-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        .payment-instructions .inst-text { color: var(--text-mid); }
        .payment-instructions .inst-detail {
            color: var(--rose);
            font-weight: 600;
            margin-top: 4px;
        }
        
        /* Tax breakdown in checkout */
        .tax-breakdown {
            background: var(--cream);
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 4px;
            font-size: 13px;
        }
        .tax-breakdown .tax-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            color: var(--text-mid);
        }
        .tax-breakdown .tax-total {
            border-top: 0.5px solid var(--cream-warm);
            padding-top: 4px;
            margin-top: 4px;
            font-weight: 500;
            color: var(--text-dark);
        }
    </style>
</head>
<body>

<!-- ============================================ -->
<!-- SECTION 7: PETALS CANVAS -->
<!-- ============================================ -->
<div id="petal-canvas" aria-hidden="true"></div>

<!-- ============================================ -->
<!-- SECTION 8: TOAST NOTIFICATION -->
<!-- ============================================ -->
<div id="toast" class="toast fixed bottom-6 left-1/2 -translate-x-1/2 translate-y-24 text-white px-5 py-2.5 rounded-full text-sm font-medium shadow-lg z-[999] transition-all duration-400 pointer-events-none flex items-center gap-2">
    <span id="toast-icon">✓</span>
    <span id="toast-msg">Added to cart</span>
</div>

<!-- ============================================ -->
<!-- SECTION 9: NAVIGATION -->
<!-- ============================================ -->
<header>
    <nav class="nav" aria-label="Main navigation">
        <div class="nav-logo-area" id="adminLogoTrigger" title="Double-click for admin access">
            <div class="nav-logo-ring">
                <?php if (!empty($brandLogo) && file_exists($brandLogo)): ?>
                    <img src="<?php echo htmlspecialchars($brandLogo); ?>" alt="<?php echo htmlspecialchars($brandName); ?>" class="nav-logo-img" />
                <?php else: ?>
                    <div class="nav-logo-img placeholder">✦</div>
                <?php endif; ?>
            </div>
            <div class="nav-brand-text">
                <?php echo htmlspecialchars($brandName); ?>
                <small>Flowers &amp; Gifts</small>
            </div>
            <span class="admin-access-hint" aria-hidden="true"></span>
        </div>
        <ul class="nav-links" role="list">
            <li><a href="#shop">Bouquets</a></li>
            <li><a href="#shop">Gift Sets</a></li>
            <li><a href="#shop">Occasions</a></li>
            <li><button onclick="openCustomRequest()" style="background:none;border:none;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-mid);font-weight:400;font-family:inherit;cursor:pointer;">Custom</button></li>
        </ul>
        <div class="nav-right">
            <button onclick="toggleCart()" class="nav-cart" aria-label="Shopping cart">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span id="cart-count">0</span>
                <span class="hidden-mobile">Cart</span>
            </button>
        </div>
    </nav>
</header>

<!-- ============================================ -->
<!-- SECTION 10: HERO -->
<!-- ============================================ -->
<section class="hero" aria-labelledby="hero-heading">
    <div class="hero-bg"></div>
    <div class="hero-glass">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <span class="eyebrow-dot"></span>
                <?php echo htmlspecialchars($homepage['hero_badge_text'] ?? '✦ New arrivals — Season\'s finest blooms'); ?>
            </div>
            <h1 id="hero-heading" class="hero-title">
                <?php echo htmlspecialchars($homepage['hero_title_text'] ?? 'Every bloom tells a story'); ?>
            </h1>
            <p class="hero-tagline"><?php echo htmlspecialchars($homepage['hero_tagline'] ?? '"Where flowers tell stories"'); ?></p>
            <p class="hero-location"><?php echo htmlspecialchars($homepage['hero_location'] ?? 'Handcrafted with love in Dodoma, Tanzania'); ?></p>
            <div class="hero-cta">
                <button class="btn-primary" onclick="document.getElementById('shop').scrollIntoView({behavior:'smooth'})">Shop Bouquets</button>
                <button class="btn-ghost" onclick="openCustomRequest()">Custom Order</button>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- SECTION 11: SCROLLING BANNER -->
<!-- ============================================ -->
<div class="scroll-banner" aria-hidden="true">
    <div class="scroll-track">
        <?php foreach($scrollItems as $item): ?>
            <span class="scroll-item"><?php echo htmlspecialchars(trim($item)); ?></span>
            <span class="scroll-sep">·</span>
        <?php endforeach; ?>
        <?php foreach($scrollItems as $item): ?>
            <span class="scroll-item"><?php echo htmlspecialchars(trim($item)); ?></span>
            <span class="scroll-sep">·</span>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 12: PRODUCTS -->
<!-- ============================================ -->
<section id="shop" aria-labelledby="shop-heading">
    <div class="section-label">
        <p class="section-eyebrow">✦ <?php echo htmlspecialchars($homepage['features_title'] ?? 'Curated Collections'); ?></p>
        <h2 id="shop-heading" class="section-title"><?php echo htmlspecialchars($homepage['features_title'] ?? 'Blooms for Every Moment'); ?></h2>
        <p class="section-sub"><?php echo htmlspecialchars($homepage['features_subtitle'] ?? 'From quiet love notes to grand celebrations'); ?></p>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;padding:0 10px 16px;">
        <button onclick="filterByMainCategory('All')" id="cat-all" class="cat-pill active" style="padding:6px 16px;border-radius:100px;font-size:12px;border:0.5px solid var(--cream-warm);background:var(--rose);color:#fff;transition:all 0.2s;cursor:pointer;">All</button>
        <button onclick="filterByMainCategory('Flowers')" id="cat-flowers" class="cat-pill" style="padding:6px 16px;border-radius:100px;font-size:12px;border:0.5px solid var(--cream-warm);background:transparent;color:var(--text-mid);transition:all 0.2s;cursor:pointer;">Flowers</button>
        <button onclick="filterByMainCategory('Gift Packages')" id="cat-gifts" class="cat-pill" style="padding:6px 16px;border-radius:100px;font-size:12px;border:0.5px solid var(--cream-warm);background:transparent;color:var(--text-mid);transition:all 0.2s;cursor:pointer;">Gift Sets</button>
        <button onclick="filterByMainCategory('Decorations')" id="cat-decor" class="cat-pill" style="padding:6px 16px;border-radius:100px;font-size:12px;border:0.5px solid var(--cream-warm);background:transparent;color:var(--text-mid);transition:all 0.2s;cursor:pointer;">Decor</button>
    </div>
    <div id="subcategory-filters" style="display:flex;flex-wrap:wrap;gap:4px;justify-content:center;padding:0 10px 16px;"></div>
    <div id="product-grid" class="product-grid"></div>
</section>

<!-- ============================================ -->
<!-- SECTION 13: QUOTE -->
<!-- ============================================ -->
<section class="quote-section" aria-label="Inspirational quote">
    <span class="quote-mark" aria-hidden="true">"</span>
    <p class="quote-text"><?php echo htmlspecialchars($homepage['quote_text'] ?? 'Flowers are the music of the ground. From earth\'s lips, spoken without sound.'); ?></p>
    <p class="quote-attr">✦ &nbsp; <?php echo htmlspecialchars($homepage['quote_author'] ?? 'Edwin Curran'); ?> &nbsp; ✦</p>
</section>

<!-- ============================================ -->
<!-- SECTION 14: FEATURES -->
<!-- ============================================ -->
<section aria-label="Why Wrapped by Vee">
    <div class="features">
        <div class="feature-item">
            <span class="feature-icon" aria-hidden="true">✦</span>
            <p class="feature-title">Farm Fresh</p>
            <p class="feature-desc">Sourced daily from local growers</p>
        </div>
        <div class="feature-item">
            <span class="feature-icon" aria-hidden="true">✦</span>
            <p class="feature-title">Same-Day Delivery</p>
            <p class="feature-desc">Order by noon in Dodoma</p>
        </div>
        <div class="feature-item">
            <span class="feature-icon" aria-hidden="true">✦</span>
            <p class="feature-title">Artisan Wrapping</p>
            <p class="feature-desc">Signature finishing touch</p>
        </div>
        <div class="feature-item">
            <span class="feature-icon" aria-hidden="true">✦</span>
            <p class="feature-title">Personal Touch</p>
            <p class="feature-desc">Handwritten messages always</p>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- SECTION 15: TESTIMONIALS -->
<!-- ============================================ -->
<section aria-labelledby="testi-heading">
    <div class="section-label">
        <p class="section-eyebrow">✦ Kind Words</p>
        <h2 id="testi-heading" class="section-title">From Our Customers</h2>
        <p class="section-sub">Real moments, real joy</p>
    </div>
    <div class="testimonials">
        <div class="testi-grid">
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"<?php echo htmlspecialchars($homepage['testimonial_1_text'] ?? 'The bouquet arrived looking like it had been photographed for a magazine. My mother cried happy tears.'); ?>"</p>
                <p class="testi-author"><?php echo htmlspecialchars($homepage['testimonial_1_name'] ?? 'Amina T.'); ?> — <span><?php echo htmlspecialchars($homepage['testimonial_1_location'] ?? 'Dodoma'); ?></span></p>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"<?php echo htmlspecialchars($homepage['testimonial_2_text'] ?? 'Ordered the Golden Hour gift set for my anniversary. Vee went above and beyond.'); ?>"</p>
                <p class="testi-author"><?php echo htmlspecialchars($homepage['testimonial_2_name'] ?? 'David M.'); ?> — <span><?php echo htmlspecialchars($homepage['testimonial_2_location'] ?? 'Dodoma'); ?></span></p>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"<?php echo htmlspecialchars($homepage['testimonial_3_text'] ?? 'Used Wrapped by Vee for our corporate event centrepieces. Professional and breathtaking.'); ?>"</p>
                <p class="testi-author"><?php echo htmlspecialchars($homepage['testimonial_3_name'] ?? 'Rehema K.'); ?> — <span><?php echo htmlspecialchars($homepage['testimonial_3_location'] ?? 'Dodoma'); ?></span></p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- SECTION 16: FOOTER -->
<!-- ============================================ -->
<footer class="footer">
    <div class="footer-top">
        <div>
            <span class="footer-brand"><?php echo htmlspecialchars($brandName); ?></span>
            <p class="footer-tagline"><?php echo htmlspecialchars($homepage['footer_tagline'] ?? '"Where flowers tell stories"'); ?></p>
            <p class="footer-location">✦ <?php echo htmlspecialchars($homepage['footer_location'] ?? 'Handcrafted in Dodoma, Tanzania'); ?></p>
            <p style="font-size:11px;color:rgba(255,255,255,0.2);margin-top:6px;">
                <?php echo htmlspecialchars($sitePhone); ?> • <?php echo htmlspecialchars($siteEmail); ?>
            </p>
        </div>
        <div>
            <p class="footer-col-title">Shop</p>
            <ul class="footer-links">
                <li><a href="#shop">Fresh Bouquets</a></li>
                <li><a href="#shop">Gift Packages</a></li>
                <li><a href="#shop">Wedding Florals</a></li>
                <li><a href="#shop">Corporate Orders</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-col-title">Help</p>
            <ul class="footer-links">
                <li><a href="#">Delivery Info</a></li>
                <li><a href="#">Flower Care</a></li>
                <li><a href="#">Returns</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-col-title">Connect</p>
            <ul class="footer-links">
                <li><a href="#">Instagram</a></li>
                <li><a href="#">WhatsApp</a></li>
                <li><a href="#">Facebook</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. All rights reserved.</span>
        <span>Made with <span class="footer-heart">♥</span> in Tanzania</span>
    </div>
</footer>

<!-- ============================================ -->
<!-- SECTION 17: MODALS -->
<!-- ============================================ -->
<!-- Cart Overlay -->
<div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>

<!-- Cart Drawer -->
<div id="cart-drawer" class="cart-drawer">
    <div class="modal-header">
        <h2>Your Cart</h2>
        <button class="modal-close" onclick="toggleCart()">✕</button>
    </div>
    <div id="cart-items" class="modal-body" style="flex:1;overflow-y:auto;padding:12px 16px;"></div>
    <div style="padding:12px 16px 16px;border-top:0.5px solid var(--cream-warm);background:var(--cream);">
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
            <span>Subtotal</span>
            <span id="cart-subtotal">TZS 0</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
            <span>Tax</span>
            <span id="cart-tax">TZS 0</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:500;margin-bottom:12px;padding-top:8px;border-top:0.5px solid var(--cream-warm);">
            <span>Total</span>
            <span id="cart-total" style="color:var(--rose);">TZS 0</span>
        </div>
        <button onclick="openCheckout()" id="checkout-btn" class="btn-primary" style="width:100%;text-align:center;padding:12px;font-size:14px;">Proceed to Checkout</button>
    </div>
</div>

<!-- Product Detail Modal -->
<div id="product-detail-modal" class="modal-overlay">
    <div class="modal-card" style="max-width:600px;">
        <div class="modal-header">
            <h2 id="product-detail-title">Product Details</h2>
            <button class="modal-close" onclick="closeProductDetail()">✕</button>
        </div>
        <div class="modal-body" id="product-detail-body">
            <div id="product-detail-content"></div>
        </div>
    </div>
</div>

<!-- Custom Request Modal -->
<div id="custom-request-modal" class="modal-overlay">
    <div class="modal-card">
        <div id="cr-form-view">
            <div class="modal-header" id="cr-modal-header">
                <h2>Share Your Vision</h2>
                <button class="modal-close" onclick="closeCustomRequest()">✕</button>
            </div>
            <div class="modal-body" id="cr-modal-body">
                <div style="background:var(--cream-mid);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:15px;color:var(--text-mid);">
                    Tell us what you're dreaming of — we'll create something magical!
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                    <input type="text" id="cr-name" placeholder="Your Name *" class="input-field">
                    <input type="tel" id="cr-phone" placeholder="Phone *" class="input-field">
                </div>
                <select id="cr-request-type" class="input-field" style="margin-bottom:10px;">
                    <option value="">What are you looking for? *</option>
                    <option value="Flowers">Flowers</option>
                    <option value="Gift Package">Gift Package</option>
                    <option value="Decorations">Decorations</option>
                    <option value="Event">Event</option>
                    <option value="Other">Other</option>
                </select>
                <textarea id="cr-location" rows="2" placeholder="Location / Delivery Address *" class="input-field" style="margin-bottom:10px;"></textarea>
                <div class="gift-fields">
                    <label>
                        <input type="checkbox" id="cr-is-gift" onchange="toggleGiftFields()" style="accent-color:var(--rose);">
                        This is a gift
                    </label>
                    <div class="gift-details" id="cr-gift-details">
                        <input type="text" id="cr-recipient-name" placeholder="Recipient's Name *" class="input-field">
                        <input type="tel" id="cr-recipient-phone" placeholder="Recipient's Phone Number *" class="input-field">
                        <textarea id="cr-recipient-address" rows="2" placeholder="Recipient's Delivery Address *" class="input-field"></textarea>
                        <textarea id="cr-gift-message" rows="2" placeholder="Gift message (optional)" class="input-field"></textarea>
                    </div>
                </div>
                <textarea id="cr-vision" rows="3" placeholder="Describe your vision… colours, mood, occasion *" class="input-field" style="margin-bottom:10px;"></textarea>
                <div class="upload-zone" onclick="document.getElementById('cr-inspo-images').click()">
                    <input type="file" id="cr-inspo-images" multiple accept="image/*" style="display:none" onchange="handleCustomRequestImageUpload(event)">
                    <div style="font-size:20px;margin-bottom:2px;">📷</div>
                    <p style="font-weight:500;color:var(--text-mid);font-size:14px;">Upload inspiration photos</p>
                    <p style="font-size:12px;color:var(--text-light);">Tap to browse (max 5MB each)</p>
                </div>
                <div id="cr-image-preview-container" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;"></div>
                <div style="display:flex;gap:12px;margin-top:10px;font-size:14px;">
                    <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="contact_method" value="Phone Call" checked style="accent-color:var(--rose);"> Call me</label>
                    <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="contact_method" value="WhatsApp" style="accent-color:var(--rose);"> WhatsApp</label>
                </div>
                <div id="cr-error" style="display:none;margin-top:10px;"></div>
            </div>
            <div class="modal-footer" id="cr-modal-footer">
                <button onclick="closeCustomRequest()" style="flex:1;padding:10px;border:0.5px solid var(--cream-warm);border-radius:4px;background:transparent;color:var(--text-mid);font-size:14px;cursor:pointer;">Cancel</button>
                <button onclick="submitCustomRequest()" style="flex:2;padding:10px;background:var(--rose);color:#fff;border:none;border-radius:4px;font-size:14px;font-weight:500;cursor:pointer;transition:background 0.2s;">Send Request</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHECKOUT MODAL - WITH PRODUCT-LEVEL TAXES -->
<!-- ============================================ -->
<div id="checkout-modal" class="modal-overlay">
    <div class="modal-card" style="max-width:500px;">
        <div id="checkout-form">
            <div class="modal-header">
                <h2>Checkout</h2>
                <button class="modal-close" onclick="closeCheckout()">✕</button>
            </div>
            <div class="modal-body">
                <!-- Your Information -->
                <div style="background:var(--cream-mid);padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:14px;color:var(--text-mid);">
                    Your Information
                </div>
                <div style="margin-bottom:10px;">
                    <input type="text" id="co-name" placeholder="Your Full Name *" class="input-field">
                </div>
                <div style="margin-bottom:10px;">
                    <input type="tel" id="co-phone" placeholder="Your Phone Number *" class="input-field">
                </div>

                <!-- Gift Checkbox -->
                <div style="border:0.5px solid var(--cream-warm);border-radius:8px;padding:12px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                        <input type="checkbox" id="co-is-gift" onchange="toggleRecipientFields()" style="accent-color:var(--rose);">
                        This package is dedicated to someone
                    </label>
                    <div id="recipient-fields" style="display:none;margin-top:12px;padding-top:12px;border-top:0.5px solid var(--cream-warm);">
                        <p style="font-size:14px;font-weight:500;color:var(--text-dark);margin-bottom:8px;">Recipient Information</p>
                        <div style="margin-bottom:8px;"><input type="text" id="co-recipient-name" placeholder="Recipient's Full Name *" class="input-field"></div>
                        <div style="margin-bottom:8px;"><input type="tel" id="co-recipient-phone" placeholder="Recipient's Phone Number *" class="input-field"></div>
                        <div style="margin-bottom:8px;"><textarea id="co-recipient-address" rows="2" placeholder="Recipient's Delivery Address *" class="input-field"></textarea></div>
                        <div style="margin-bottom:4px;"><textarea id="co-gift-message" rows="2" placeholder="Gift Message (optional)" class="input-field"></textarea></div>
                    </div>
                </div>

                <!-- Delivery Zone -->
                <div style="border:0.5px solid var(--cream-warm);border-radius:8px;padding:12px;margin-bottom:10px;">
                    <label style="font-size:14px;font-weight:500;color:var(--text-dark);display:block;margin-bottom:4px;">Delivery Zone *</label>
                    <select id="delivery-zone" class="input-field" onchange="updateDeliveryFee()">
                        <option value="">Select your delivery zone...</option>
                        <?php if($deliveryZones && count($deliveryZones) > 0): ?>
                            <?php foreach($deliveryZones as $zone): ?>
                                <option value="<?php echo $zone['id']; ?>" data-fee="<?php echo $zone['delivery_fee']; ?>" data-name="<?php echo htmlspecialchars($zone['zone_name']); ?>">
                                    <?php echo htmlspecialchars($zone['zone_name']); ?> - TZS <?php echo number_format($zone['delivery_fee'], 0); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No delivery zones available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Payment Methods -->
                <div style="margin-bottom:10px;">
                    <p style="font-size:14px;font-weight:500;color:var(--text-dark);margin-bottom:8px;">Payment Method *</p>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <?php if($paymentMethods && count($paymentMethods) > 0): ?>
                            <?php foreach($paymentMethods as $index => $method): 
                                $methodName = strtolower($method['method']);
                                $isMpesa = (strpos($methodName, 'mpesa') !== false || strpos($methodName, 'm-pesa') !== false);
                                $isBank = (strpos($methodName, 'bank') !== false || strpos($methodName, 'transfer') !== false);
                            ?>
                                <label class="pay-card <?php echo $index === 0 ? 'selected' : ''; ?>" style="border-color: <?php echo $index === 0 ? 'var(--rose)' : 'var(--cream-warm)'; ?>;">
                                    <input type="radio" name="payment" value="<?php echo htmlspecialchars($method['method']); ?>" <?php echo $index === 0 ? 'checked' : ''; ?> onchange="selectPayment(this, '<?php echo htmlspecialchars($method['method']); ?>')" style="accent-color:var(--rose);">
                                    <span class="pay-icon">
                                        <?php if($isMpesa): ?>📱<?php elseif($isBank): ?>🏦<?php else: ?>💳<?php endif; ?>
                                    </span>
                                    <div>
                                        <p class="pay-name"><?php echo htmlspecialchars($method['method']); ?></p>
                                        <p class="pay-detail">
                                            <?php 
                                            if($isMpesa && isset($paymentSettings['mpesa_phone']) && !empty($paymentSettings['mpesa_phone'])): 
                                                echo '📞 ' . htmlspecialchars($paymentSettings['mpesa_phone']);
                                            elseif($isBank): 
                                                if(isset($paymentSettings['bank_account']) && !empty($paymentSettings['bank_account'])):
                                                    echo '🏦 ' . htmlspecialchars($paymentSettings['bank_account']);
                                                else:
                                                    echo 'Available';
                                                endif;
                                            else: 
                                                echo 'Available';
                                            endif; 
                                            ?>
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default payment methods -->
                            <label class="pay-card selected" style="border-color: var(--rose);">
                                <input type="radio" name="payment" value="M-Pesa" checked onchange="selectPayment(this, 'M-Pesa')" style="accent-color:var(--rose);">
                                <span class="pay-icon">📱</span>
                                <div>
                                    <p class="pay-name">M-Pesa</p>
                                    <p class="pay-detail">📞 <?php echo htmlspecialchars($paymentSettings['mpesa_phone'] ?? '+255 755 555 555'); ?></p>
                                </div>
                            </label>
                            <label class="pay-card" style="border-color: var(--cream-warm);">
                                <input type="radio" name="payment" value="Bank Transfer" onchange="selectPayment(this, 'Bank Transfer')" style="accent-color:var(--rose);">
                                <span class="pay-icon">🏦</span>
                                <div>
                                    <p class="pay-name">Bank Transfer</p>
                                    <p class="pay-detail">🏦 <?php echo htmlspecialchars($paymentSettings['bank_account'] ?? '1234567890'); ?></p>
                                </div>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div id="payment-instructions" class="payment-instructions" style="display:none;">
                    <p class="inst-title">Payment Instructions</p>
                    <div id="instruction-text" class="inst-text"></div>
                    <div id="instruction-details" class="inst-detail"></div>
                </div>

                <!-- Upload Proof -->
                <div class="upload-zone" onclick="document.getElementById('proof-image').click()" style="margin-bottom:10px;">
                    <input type="file" id="proof-image" accept="image/*" style="display:none" onchange="handleProofUpload(event)">
                    <div style="font-size:20px;margin-bottom:2px;">📷</div>
                    <p style="font-weight:500;color:var(--text-mid);font-size:14px;">Upload Proof of Payment *</p>
                </div>
                <div id="proof-preview" style="display:none;align-items:center;gap:10px;margin-bottom:10px;">
                    <img id="proof-preview-img" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:0.5px solid var(--cream-warm);">
                    <button onclick="removeProof()" style="color:red;font-size:14px;background:none;border:none;cursor:pointer;">Remove</button>
                </div>

                <!-- Order Summary with Tax Breakdown -->
                <div style="background:var(--cream-mid);padding:12px;border-radius:8px;margin-bottom:10px;">
                    <div id="co-summary" style="font-size:14px;color:var(--text-mid);"></div>
                    <div id="co-tax-breakdown" class="tax-breakdown" style="display:none;"></div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-top:6px;padding-top:6px;border-top:0.5px solid var(--cream-warm);">
                        <span>Delivery Fee</span>
                        <span id="co-delivery-fee" style="font-weight:500;">TZS 0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-top:6px;padding-top:6px;border-top:0.5px solid var(--cream-warm);">
                        <span>Tax</span>
                        <span id="co-tax-amount" style="font-weight:500;">TZS 0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:500;font-size:18px;margin-top:6px;padding-top:6px;border-top:0.5px solid var(--cream-warm);">
                        <span>Total</span>
                        <span id="co-total" style="color:var(--rose);">TZS 0</span>
                    </div>
                </div>
                <p id="co-error" style="color:red;font-size:14px;display:none;"></p>
            </div>
            <div class="modal-footer">
                <button onclick="closeCheckout()" style="flex:1;padding:10px;border:0.5px solid var(--cream-warm);border-radius:4px;background:transparent;color:var(--text-mid);font-size:14px;cursor:pointer;">Cancel</button>
                <button onclick="placeOrder()" style="flex:2;padding:10px;background:var(--rose);color:#fff;border:none;border-radius:4px;font-size:14px;font-weight:500;cursor:pointer;transition:background 0.2s;">Place Order</button>
            </div>
        </div>
        <div id="receipt-view" style="display:none;text-align:center;padding:24px 16px;">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#D1FAE5,#A7F3D0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;">✓</div>
            <h2 style="font-family:'Playfair Display',serif;font-size:22px;color:var(--rose);margin-bottom:4px;">Order Placed!</h2>
            <p style="color:var(--text-light);font-size:15px;margin-bottom:16px;">We'll confirm your payment shortly</p>
            <div id="receipt-details" style="text-align:left;background:var(--cream-mid);padding:12px;border-radius:8px;font-size:14px;margin-bottom:16px;"></div>
            <button onclick="closeCheckout()" class="btn-primary" style="padding:10px 28px;font-size:14px;">Continue Shopping</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 18: JAVASCRIPT -->
<!-- ============================================ -->
<script>
// ============================================
// DATA - Load products from database with TAX FIELDS
// ============================================
let allProducts = [];
try {
    allProducts = <?php 
        $productsArray = is_array($products) ? $products : [];
        $sanitizedProducts = array_map(function($product) {
            return [
                'id' => intval($product['id'] ?? 0),
                'name' => htmlspecialchars($product['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'),
                'price' => floatval($product['price'] ?? 0),
                'description' => htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8'),
                'image_url' => htmlspecialchars($product['image_url'] ?? '', ENT_QUOTES, 'UTF-8'),
                'main_category' => htmlspecialchars($product['main_category'] ?? 'Flowers', ENT_QUOTES, 'UTF-8'),
                'sub_category' => htmlspecialchars($product['sub_category'] ?? 'Fresh Flowers', ENT_QUOTES, 'UTF-8'),
                'stock' => intval($product['stock'] ?? 10),
                'tax_mpesa' => floatval($product['tax_mpesa'] ?? 0),
                'tax_bank' => floatval($product['tax_bank'] ?? 0)
            ];
        }, $productsArray);
        $json = json_encode($sanitizedProducts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo $json ?: '[]';
    ?>;
} catch(e) {
    console.error('Error loading products:', e);
    allProducts = [];
}

// DEBUG: Check if taxes are loaded
console.log('=== PRODUCT TAX DEBUG ===');
console.log('Total products loaded:', allProducts.length);
if (allProducts.length > 0) {
    console.log('First product:', allProducts[0]);
    console.log('tax_mpesa value:', allProducts[0].tax_mpesa);
    console.log('tax_bank value:', allProducts[0].tax_bank);
} else {
    console.warn('No products loaded!');
}

// Payment methods data from PHP
const paymentMethodsData = <?php echo json_encode($paymentMethods ?: []); ?>;
const paymentSettings = <?php echo json_encode($paymentSettings); ?>;
let cart = [];
let currentMainCategory = 'All';
let currentSubCategory = 'All';
let crUploadedImages = [];
let paymentProofData = null;
let deliveryFee = 0;

// ============================================
// PETALS ANIMATION
// ============================================
(function() {
    const canvas = document.getElementById('petal-canvas');
    if (!canvas) return;
    const colors = ['#EFAAB8','#F3C4CF','#E8879A','#F8D8E2','#FDE8EC','#D4899A','#F0B8C8'];
    const count = 16;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'petal';
        const sz = 6 + Math.random() * 6;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const dur = 7 + Math.random() * 12;
        const delay = Math.random() * 16;
        p.style.cssText = [
            `width:${sz}px`,
            `height:${(sz * 0.6).toFixed(1)}px`,
            `background:${color}`,
            `left:${(Math.random() * 100).toFixed(1)}%`,
            `animation-duration:${dur.toFixed(1)}s`,
            `animation-delay:${delay.toFixed(1)}s`,
            `border-radius:${50 + Math.round(Math.random()*20)}% ${8 + Math.round(Math.random()*14)}% ${50 + Math.round(Math.random()*20)}% ${8 + Math.round(Math.random()*14)}%`,
            `opacity:0`
        ].join(';');
        canvas.appendChild(p);
    }
})();

// ============================================
// UTILITY FUNCTIONS
// ============================================
function showToast(icon, msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toast-icon').textContent = icon;
    document.getElementById('toast-msg').textContent = msg;
    toast.classList.remove('translate-y-24');
    toast.classList.add('translate-y-0');
    setTimeout(() => {
        toast.classList.remove('translate-y-0');
        toast.classList.add('translate-y-24');
    }, 2600);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

// ============================================
// ADMIN ACCESS - Double Click Logo
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const logoTrigger = document.getElementById('adminLogoTrigger');
    let clickCount = 0;
    let clickTimer = null;
    
    if (logoTrigger) {
        logoTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            clickCount++;
            if (clickTimer) clearTimeout(clickTimer);
            clickTimer = setTimeout(() => { clickCount = 0; }, 800);
            if (clickCount === 2) {
                clickCount = 0;
                showToast('🔐', 'Accessing admin area...');
                setTimeout(() => { window.location.href = 'login.php'; }, 500);
            }
        });
    }
    
    // Set up observer for cart and checkout
    const cartDrawer = document.getElementById('cart-drawer');
    if (cartDrawer) {
        const cartObserver = new MutationObserver(function() {
            if (cartDrawer.classList.contains('open')) updateTotals();
        });
        cartObserver.observe(cartDrawer, { attributes: true, attributeFilter: ['class'] });
    }
    
    const checkoutModal = document.getElementById('checkout-modal');
    if (checkoutModal) {
        const checkoutObserver = new MutationObserver(function() {
            if (checkoutModal.classList.contains('open')) {
                const selected = document.querySelector('input[name="payment"]:checked');
                if (selected) {
                    selectPayment(selected, selected.value);
                }
                updateCheckoutTotal();
            }
        });
        checkoutObserver.observe(checkoutModal, { attributes: true, attributeFilter: ['class'] });
    }
    
    renderProducts();
});

// ============================================
// PRODUCT FUNCTIONS
// ============================================
function renderProducts() {
    let filtered = allProducts;
    if (currentMainCategory !== 'All') {
        filtered = filtered.filter(p => p.main_category === currentMainCategory);
    }
    if (currentSubCategory !== 'All') {
        filtered = filtered.filter(p => p.sub_category === currentSubCategory);
    }

    const grid = document.getElementById('product-grid');
    if (!grid) return;
    
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center py-12" style="color:var(--text-light);"><div style="font-size:32px;margin-bottom:8px;">🌸</div><p style="font-weight:500;font-size:16px;">No products found</p><p style="font-size:13px;color:#ccc;">Products will appear here once added by admin</p></div>';
        return;
    }

    grid.innerHTML = filtered.map((p, i) => {
        const inCart = cart.some(c => c.id === p.id);
        const disabled = p.stock === 0;
        const hasImage = p.image_url && p.image_url !== '' && p.image_url !== null;
        
        return `
        <div class="product-card" style="transition-delay:${i * 40}ms" onclick="openProductDetail(${p.id})">
            <div class="product-img">
                ${p.stock < 5 && p.stock > 0 ? `<span class="badge">${p.stock} left</span>` : ''}
                ${p.stock === 0 ? `<span class="badge badge-dark">Sold Out</span>` : ''}
                <button class="wish-btn" onclick="event.stopPropagation();" aria-label="Add to wishlist">♡</button>
                ${hasImage ? 
                    `<img src="${p.image_url}" alt="${escapeHtml(p.name)}" />` :
                    `<span class="placeholder-icon">✦</span>`
                }
            </div>
            <div class="product-info">
                <h3 class="product-name">${escapeHtml(p.name)}</h3>
                <p class="product-desc">${escapeHtml(p.description || 'Beautiful floral arrangement')}</p>
                <div class="product-footer">
                    <div>
                        <div class="product-price">TZS ${Number(p.price).toLocaleString()}</div>
                        <span class="product-price-unit">per item</span>
                        <span style="font-size:9px;color:var(--text-light);display:block;">Tax M: ${Number(p.tax_mpesa).toLocaleString()} | B: ${Number(p.tax_bank).toLocaleString()}</span>
                    </div>
                    <button onclick="event.stopPropagation(); addToCart(${p.id}, '${escapeHtml(p.name).replace(/'/g,"\\'")}', ${p.price}, ${p.stock})"
                        class="add-btn ${inCart ? 'in-cart' : ''}" ${disabled ? 'disabled' : ''}>
                        ${inCart ? '✓' : '+'}
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

function openProductDetail(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;
    
    const modal = document.getElementById('product-detail-modal');
    const content = document.getElementById('product-detail-content');
    const title = document.getElementById('product-detail-title');
    
    title.textContent = product.name;
    
    const hasImage = product.image_url && product.image_url !== '' && product.image_url !== null;
    const inCart = cart.some(c => c.id === product.id);
    const disabled = product.stock === 0;
    
    content.innerHTML = `
        <div style="margin-bottom:16px;">
            ${hasImage ? 
                `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" class="product-detail-img" />` :
                `<div style="width:100%;height:200px;background:linear-gradient(160deg, #f9e8ec, #f0c8d2, #e8a8b8);display:flex;align-items:center;justify-content:center;font-size:64px;color:var(--rose-light);border-radius:8px;">✦</div>`
            }
        </div>
        <h3 class="product-detail-name">${escapeHtml(product.name)}</h3>
        <div class="product-detail-price">TZS ${Number(product.price).toLocaleString()}</div>
        <p style="font-size:13px;color:var(--text-light);margin-bottom:12px;">per item</p>
        <p class="product-detail-desc">${escapeHtml(product.description || 'Beautiful floral arrangement')}</p>
        <div style="margin-top:16px;padding-top:16px;border-top:0.5px solid var(--cream-warm);display:flex;gap:12px;align-items:center;justify-content:space-between;">
            <div>
                <p style="font-size:13px;color:var(--text-light);">Stock: <span style="font-weight:500;color:var(--text-dark);">${product.stock} available</span></p>
                <p style="font-size:13px;color:var(--text-light);">Category: <span style="font-weight:500;color:var(--text-dark);">${escapeHtml(product.main_category)}</span></p>
                <p style="font-size:13px;color:var(--text-light);">M-Pesa Tax: <span style="font-weight:500;color:var(--text-dark);">TZS ${Number(product.tax_mpesa).toLocaleString()}</span></p>
                <p style="font-size:13px;color:var(--text-light);">Bank Tax: <span style="font-weight:500;color:var(--text-dark);">TZS ${Number(product.tax_bank).toLocaleString()}</span></p>
            </div>
            <button onclick="addToCart(${product.id}, '${escapeHtml(product.name).replace(/'/g,"\\'")}', ${product.price}, ${product.stock}); closeProductDetail();"
                class="add-btn ${inCart ? 'in-cart' : ''}" ${disabled ? 'disabled' : ''} 
                style="width:48px;height:48px;font-size:24px;border-radius:50%;">
                ${inCart ? '✓' : '+'}
            </button>
        </div>
    `;
    
    modal.classList.add('open');
}

function closeProductDetail() {
    document.getElementById('product-detail-modal').classList.remove('open');
}

document.getElementById('product-detail-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductDetail();
    }
});

function filterByMainCategory(category) {
    currentMainCategory = category;
    currentSubCategory = 'All';
    document.querySelectorAll('.cat-pill').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color = 'var(--text-mid)';
    });
    const map = { 'All': 'cat-all', 'Flowers': 'cat-flowers', 'Gift Packages': 'cat-gifts', 'Decorations': 'cat-decor' };
    const btn = document.getElementById(map[category]);
    if (btn) { btn.style.background = 'var(--rose)'; btn.style.color = '#fff'; }
    updateSubcategoryFilters();
    renderProducts();
}

function updateSubcategoryFilters() {
    const container = document.getElementById('subcategory-filters');
    const subs = {
        'Flowers': ['Fresh Flowers', 'Artificial Flowers'],
        'Gift Packages': ['Birthday', 'Anniversary', 'Wedding'],
        'Decorations': ['Party Decor', 'Wedding Decor']
    }[currentMainCategory] || [];

    if (subs.length) {
        container.style.display = 'flex';
        container.innerHTML = `<button onclick="filterBySubCategory('All', this)" class="subcat-pill" style="padding:4px 12px;border-radius:100px;font-size:11px;border:0.5px solid var(--cream-warm);background:var(--rose);color:#fff;cursor:pointer;transition:all 0.2s;">All</button>
            ${subs.map(s => `<button onclick="filterBySubCategory('${s}', this)" class="subcat-pill" style="padding:4px 12px;border-radius:100px;font-size:11px;border:0.5px solid var(--cream-warm);background:transparent;color:var(--text-mid);cursor:pointer;transition:all 0.2s;">${s}</button>`).join('')}`;
    } else {
        container.style.display = 'none';
    }
}

function filterBySubCategory(sub, el) {
    currentSubCategory = sub;
    document.querySelectorAll('.subcat-pill').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = 'var(--text-mid)';
    });
    if (el) { el.style.background = 'var(--rose)'; el.style.color = '#fff'; }
    renderProducts();
}

// ============================================
// CART FUNCTIONS
// ============================================
function addToCart(id, name, price, stock) {
    const existing = cart.find(c => c.id === id);
    if (existing) {
        if (existing.qty >= stock) { showToast('⚠️', 'Max stock reached'); return; }
        existing.qty++;
    } else {
        cart.push({ id, name, price, qty: 1 });
    }
    updateCartUI();
    showToast('🌸', `${name} added`);
    renderProducts();
}

function updateCartUI() {
    const totalQty = cart.reduce((s, c) => s + c.qty, 0);
    document.getElementById('cart-count').textContent = totalQty;

    const container = document.getElementById('cart-items');
    if (cart.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:32px 0;color:var(--text-light);"><div style="font-size:32px;margin-bottom:8px;">🛒</div><p style="font-weight:500;font-size:16px;">Your cart is empty</p></div>';
        document.getElementById('checkout-btn').disabled = true;
    } else {
        document.getElementById('checkout-btn').disabled = false;
        container.innerHTML = cart.map(c => `
        <div style="display:flex;gap:10px;padding:10px;background:var(--cream-mid);border-radius:8px;margin-bottom:8px;">
            <div style="width:40px;height:40px;background:var(--cream-warm);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;">🌸</div>
            <div style="flex:1;">
                <p style="font-weight:500;font-size:15px;">${escapeHtml(c.name)}</p>
                <p style="color:var(--rose);font-size:14px;">TZS ${c.price.toLocaleString()}</p>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button onclick="changeQty(${c.id}, -1)" style="width:28px;height:28px;border-radius:50%;border:0.5px solid var(--cream-warm);background:white;font-size:16px;cursor:pointer;">−</button>
                <span style="width:24px;text-align:center;font-weight:500;font-size:15px;">${c.qty}</span>
                <button onclick="changeQty(${c.id}, 1)" style="width:28px;height:28px;border-radius:50%;border:0.5px solid var(--cream-warm);background:white;font-size:16px;cursor:pointer;">+</button>
                <button onclick="removeFromCart(${c.id})" style="background:none;border:none;color:var(--text-light);font-size:18px;cursor:pointer;">✕</button>
            </div>
        </div>`).join('');
    }
    updateTotals();
}

function changeQty(id, delta) {
    const item = cart.find(c => c.id === id);
    if (item) { item.qty += delta; if (item.qty <= 0) removeFromCart(id); else updateCartUI(); }
}

function removeFromCart(id) {
    cart = cart.filter(c => c.id !== id);
    updateCartUI();
    renderProducts();
}

function updateTotals() {
    const sub = cart.reduce((s, c) => s + c.price * c.qty, 0);
    const tax = 0;
    const total = sub + tax + deliveryFee;
    document.getElementById('cart-subtotal').textContent = `TZS ${sub.toLocaleString()}`;
    document.getElementById('cart-tax').textContent = `TZS ${tax.toLocaleString()}`;
    document.getElementById('cart-total').textContent = `TZS ${total.toLocaleString()}`;
}

function toggleCart() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer) drawer.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

// ============================================
// RECIPIENT FIELDS
// ============================================
function toggleRecipientFields() {
    const checked = document.getElementById('co-is-gift').checked;
    document.getElementById('recipient-fields').style.display = checked ? 'block' : 'none';
    if (!checked) {
        document.getElementById('co-recipient-name').value = '';
        document.getElementById('co-recipient-phone').value = '';
        document.getElementById('co-recipient-address').value = '';
        document.getElementById('co-gift-message').value = '';
    }
}

// ============================================
// DELIVERY FUNCTIONS
// ============================================
function updateDeliveryFee() {
    const select = document.getElementById('delivery-zone');
    const selectedOption = select.options[select.selectedIndex];
    const fee = selectedOption ? parseFloat(selectedOption.dataset.fee) || 0 : 0;
    deliveryFee = fee;
    document.getElementById('co-delivery-fee').textContent = `TZS ${fee.toLocaleString()}`;
    updateCheckoutTotal();
}

// ============================================
// CHECKOUT FUNCTIONS WITH PRODUCT-LEVEL TAXES
// ============================================

function updateCheckoutTotal() {
    const selectedPayment = document.querySelector('input[name="payment"]:checked')?.value || '';
    let sub = 0;
    let tax = 0;
    let taxBreakdown = [];
    
    // Calculate subtotal and tax based on selected payment method
    cart.forEach(item => {
        const product = allProducts.find(p => p.id === item.id);
        if (product) {
            sub += product.price * item.qty;
            
            let itemTax = 0;
            const methodLower = selectedPayment.toLowerCase();
            if (methodLower.includes('mpesa') || methodLower.includes('m-pesa')) {
                itemTax = (parseFloat(product.tax_mpesa) || 0) * item.qty;
            } else if (methodLower.includes('bank') || methodLower.includes('transfer')) {
                itemTax = (parseFloat(product.tax_bank) || 0) * item.qty;
            } else {
                // For other payment methods, use M-Pesa tax as fallback
                itemTax = (parseFloat(product.tax_mpesa) || 0) * item.qty;
            }
            tax += itemTax;
            
            taxBreakdown.push({
                name: product.name,
                qty: item.qty,
                tax: itemTax
            });
        }
    });
    
    const total = sub + tax + deliveryFee;
    
    // Update item summary
    document.getElementById('co-summary').innerHTML = cart.map(c => {
        const product = allProducts.find(p => p.id === c.id);
        const itemTax = product ? 
            (selectedPayment.toLowerCase().includes('mpesa') ? parseFloat(product.tax_mpesa) || 0 : 
             selectedPayment.toLowerCase().includes('bank') ? parseFloat(product.tax_bank) || 0 : 
             parseFloat(product.tax_mpesa) || 0) * c.qty : 0;
        return `<div style="display:flex;justify-content:space-between;font-size:14px;padding:2px 0;">
            <span>${escapeHtml(c.name)} ×${c.qty}</span>
            <span style="font-weight:500;">TZS ${(c.price * c.qty).toLocaleString()}</span>
            ${itemTax > 0 ? `<span style="font-size:11px;color:var(--text-light);">+ tax TZS ${itemTax.toLocaleString()}</span>` : ''}
        </div>`;
    }).join('');
    
    // Show tax breakdown
    const taxBreakdownEl = document.getElementById('co-tax-breakdown');
    if (tax > 0 && taxBreakdown.length > 0) {
        taxBreakdownEl.style.display = 'block';
        taxBreakdownEl.innerHTML = `
            <div style="font-size:12px;font-weight:500;color:var(--text-mid);margin-bottom:2px;">Tax Breakdown</div>
            ${taxBreakdown.filter(t => t.tax > 0).map(t => 
                `<div class="tax-row"><span>${escapeHtml(t.name)} ×${t.qty}</span><span>TZS ${t.tax.toLocaleString()}</span></div>`
            ).join('')}
            <div class="tax-row tax-total"><span>Total Tax</span><span>TZS ${tax.toLocaleString()}</span></div>
        `;
    } else {
        taxBreakdownEl.style.display = 'none';
    }
    
    document.getElementById('co-tax-amount').textContent = `TZS ${tax.toLocaleString()}`;
    document.getElementById('co-total').textContent = `TZS ${total.toLocaleString()}`;
    document.getElementById('co-delivery-fee').textContent = `TZS ${deliveryFee.toLocaleString()}`;
}

function selectPayment(radio, method) {
    // Update UI
    document.querySelectorAll('.pay-card').forEach(c => {
        c.style.borderColor = 'var(--cream-warm)';
        c.classList.remove('selected');
    });
    const parent = radio.closest('.pay-card');
    if (parent) {
        parent.style.borderColor = 'var(--rose)';
        parent.classList.add('selected');
    }
    
    const div = document.getElementById('payment-instructions');
    const instText = document.getElementById('instruction-text');
    const instDetail = document.getElementById('instruction-details');
    
    // Get payment method details from settings
    const methodLower = method.toLowerCase();
    const isMpesa = methodLower.includes('mpesa') || methodLower.includes('m-pesa');
    const isBank = methodLower.includes('bank') || methodLower.includes('transfer');
    
    // Calculate total tax for this payment method
    let totalTax = 0;
    cart.forEach(item => {
        const product = allProducts.find(p => p.id === item.id);
        if (product) {
            if (isMpesa) {
                totalTax += (parseFloat(product.tax_mpesa) || 0) * item.qty;
            } else if (isBank) {
                totalTax += (parseFloat(product.tax_bank) || 0) * item.qty;
            } else {
                totalTax += (parseFloat(product.tax_mpesa) || 0) * item.qty;
            }
        }
    });
    
    if (isMpesa) {
        const phone = paymentSettings.mpesa_phone || '+255 755 555 555';
        instText.textContent = 'Send payment via M-Pesa to the number below. Include your order ID as reference.';
        instDetail.textContent = '📞 Send to: ' + phone + ' | Total Tax: TZS ' + totalTax.toLocaleString();
        div.style.display = 'block';
    } else if (isBank) {
        const bankName = paymentSettings.bank_name || 'CRDB Bank';
        const account = paymentSettings.bank_account || '1234567890';
        const accountName = paymentSettings.bank_account_name || 'Wrapped by Vee Ltd';
        instText.textContent = 'Transfer payment to the bank account below. Include your order ID as reference.';
        instDetail.textContent = '🏦 ' + bankName + ' - Account: ' + account + ' (' + accountName + ') | Total Tax: TZS ' + totalTax.toLocaleString();
        div.style.display = 'block';
    } else {
        instText.textContent = 'Please complete payment and upload proof.';
        instDetail.textContent = 'Total Tax: TZS ' + totalTax.toLocaleString();
        div.style.display = 'block';
    }
    
    // Update totals
    updateCheckoutTotal();
}

function handleProofUpload(event) {
    const file = event.target.files[0];
    if (file && file.size <= 5 * 1024 * 1024) {
        const reader = new FileReader();
        reader.onload = e => {
            paymentProofData = e.target.result;
            document.getElementById('proof-preview-img').src = paymentProofData;
            document.getElementById('proof-preview').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    } else if (file) showToast('⚠️', 'File too large (max 5MB)');
}

function removeProof() {
    paymentProofData = null;
    document.getElementById('proof-preview').style.display = 'none';
    document.getElementById('proof-image').value = '';
}

function openCheckout() {
    if (cart.length === 0) { showToast('⚠️', 'Cart is empty'); return; }
    toggleCart();
    
    // Reset form
    document.getElementById('co-name').value = '';
    document.getElementById('co-phone').value = '';
    document.getElementById('co-is-gift').checked = false;
    document.getElementById('recipient-fields').style.display = 'none';
    document.getElementById('co-recipient-name').value = '';
    document.getElementById('co-recipient-phone').value = '';
    document.getElementById('co-recipient-address').value = '';
    document.getElementById('co-gift-message').value = '';
    document.getElementById('proof-preview').style.display = 'none';
    document.getElementById('proof-image').value = '';
    paymentProofData = null;
    document.getElementById('co-error').style.display = 'none';
    document.getElementById('checkout-form').style.display = 'block';
    document.getElementById('receipt-view').style.display = 'none';
    
    const sub = cart.reduce((s, c) => s + c.price * c.qty, 0);
    document.getElementById('co-summary').innerHTML = cart.map(c =>
        `<div style="display:flex;justify-content:space-between;font-size:14px;padding:2px 0;">
            <span>${escapeHtml(c.name)} ×${c.qty}</span>
            <span style="font-weight:500;">TZS ${(c.price * c.qty).toLocaleString()}</span>
        </div>`
    ).join('');
    
    document.getElementById('checkout-modal').classList.add('open');
    
    const firstPayment = document.querySelector('input[name="payment"]');
    if (firstPayment) {
        selectPayment(firstPayment, firstPayment.value);
    }
    updateCheckoutTotal();
}

function closeCheckout() {
    document.getElementById('checkout-modal').classList.remove('open');
    document.getElementById('checkout-form').style.display = 'block';
    document.getElementById('receipt-view').style.display = 'none';
    ['co-name','co-phone','co-recipient-name','co-recipient-phone','co-recipient-address','co-gift-message'].forEach(id => { 
        const el = document.getElementById(id); 
        if(el) el.value = ''; 
    });
    document.getElementById('co-is-gift').checked = false;
    document.getElementById('recipient-fields').style.display = 'none';
    paymentProofData = null;
    document.getElementById('proof-preview').style.display = 'none';
    document.getElementById('proof-image').value = '';
    document.getElementById('delivery-zone').value = '';
    deliveryFee = 0;
}

async function placeOrder() {
    const name = document.getElementById('co-name').value.trim();
    const phone = document.getElementById('co-phone').value.trim();
    const isGift = document.getElementById('co-is-gift').checked;
    const recipientName = isGift ? document.getElementById('co-recipient-name').value.trim() : '';
    const recipientPhone = isGift ? document.getElementById('co-recipient-phone').value.trim() : '';
    const recipientAddress = isGift ? document.getElementById('co-recipient-address').value.trim() : '';
    const giftMessage = isGift ? document.getElementById('co-gift-message').value.trim() : '';
    const payment = document.querySelector('input[name="payment"]:checked')?.value;
    const proofFile = document.getElementById('proof-image').files[0];
    const zoneSelect = document.getElementById('delivery-zone');
    const zoneId = zoneSelect.value;
    const zoneName = zoneSelect.options[zoneSelect.selectedIndex]?.dataset?.name || '';

    if (!name || !phone) { 
        document.getElementById('co-error').textContent='Please fill in your name and phone number'; 
        document.getElementById('co-error').style.display='block'; 
        return; 
    }
    
    if (isGift) {
        if (!recipientName || !recipientPhone || !recipientAddress) {
            document.getElementById('co-error').textContent='Please fill in all recipient information (name, phone, address)'; 
            document.getElementById('co-error').style.display='block'; 
            return;
        }
    }
    
    if (!zoneId) { 
        document.getElementById('co-error').textContent='Please select your delivery zone'; 
        document.getElementById('co-error').style.display='block'; 
        return; 
    }
    if (!proofFile) { 
        document.getElementById('co-error').textContent='Please upload proof of payment'; 
        document.getElementById('co-error').style.display='block'; 
        return; 
    }
    document.getElementById('co-error').style.display = 'none';
    
    // Calculate with product-level taxes
    const methodLower = (payment || '').toLowerCase();
    const isMpesa = methodLower.includes('mpesa') || methodLower.includes('m-pesa');
    const isBank = methodLower.includes('bank') || methodLower.includes('transfer');
    
    let sub = 0;
    let tax = 0;
    cart.forEach(item => {
        const product = allProducts.find(p => p.id === item.id);
        if (product) {
            sub += product.price * item.qty;
            if (isMpesa) {
                tax += (parseFloat(product.tax_mpesa) || 0) * item.qty;
            } else if (isBank) {
                tax += (parseFloat(product.tax_bank) || 0) * item.qty;
            } else {
                tax += (parseFloat(product.tax_mpesa) || 0) * item.qty;
            }
        }
    });
    
    const total = sub + tax + deliveryFee;
    
    const formData = new FormData();
    formData.append('name', name); 
    formData.append('phone', phone); 
    formData.append('is_gift', isGift ? '1' : '0');
    formData.append('recipient_name', recipientName);
    formData.append('recipient_phone', recipientPhone);
    formData.append('recipient_address', recipientAddress);
    formData.append('gift_message', giftMessage);
    formData.append('payment', payment);
    formData.append('subtotal', sub);
    formData.append('tax', tax);
    formData.append('delivery_fee', deliveryFee);
    formData.append('delivery_zone_id', zoneId);
    formData.append('delivery_zone_name', zoneName);
    formData.append('total', total);
    formData.append('items', JSON.stringify(cart));
    formData.append('payment_proof', proofFile);

    try {
        const r = await fetch('place_order.php', { method: 'POST', body: formData });
        const result = await r.json();
        if (result.success) {
            let receiptHtml = `
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Order ID</span><span style="font-weight:600;color:var(--rose);">#${result.orderId}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Customer</span><span style="font-weight:500;">${escapeHtml(name)}</span></div>
            `;
            if (isGift) {
                receiptHtml += `
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Recipient</span><span style="font-weight:500;">${escapeHtml(recipientName)}</span></div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Recipient Phone</span><span style="font-weight:500;">${escapeHtml(recipientPhone)}</span></div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Recipient Address</span><span style="font-weight:500;">${escapeHtml(recipientAddress)}</span></div>
                `;
                if (giftMessage) {
                    receiptHtml += `<div style="margin-bottom:4px;"><span style="color:var(--text-light);">Gift Message</span><br><span style="font-weight:500;font-style:italic;">"${escapeHtml(giftMessage)}"</span></div>`;
                }
            }
            receiptHtml += `
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Delivery Zone</span><span style="font-weight:500;">${escapeHtml(zoneName)}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--text-light);">Tax</span><span style="font-weight:500;">TZS ${tax.toLocaleString()}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span style="color:var(--text-light);">Total</span><span style="font-weight:700;color:var(--rose);">TZS ${total.toLocaleString()}</span></div>
                <div style="background:#FFFBEB;padding:8px 12px;border-radius:8px;font-size:14px;color:#92400E;">⏳ Payment pending confirmation. Save your Order ID: <strong>#${result.orderId}</strong></div>
            `;
            document.getElementById('receipt-details').innerHTML = receiptHtml;
            document.getElementById('checkout-form').style.display = 'none';
            document.getElementById('receipt-view').style.display = 'block';
            cart = []; deliveryFee = 0; updateCartUI(); renderProducts(); showToast('✅', 'Order placed!');
        } else showToast('❌', result.error || 'Order failed');
    } catch (e) {
        console.error('Place order error:', e);
        showToast('❌', 'Network error. Try again.');
    }
}

// ============================================
// CUSTOM REQUEST
// ============================================
function toggleGiftFields() {
    const isGift = document.getElementById('cr-is-gift').checked;
    const giftDetails = document.getElementById('cr-gift-details');
    if (isGift) {
        giftDetails.classList.add('active');
    } else {
        giftDetails.classList.remove('active');
        document.getElementById('cr-recipient-name').value = '';
        document.getElementById('cr-recipient-phone').value = '';
        document.getElementById('cr-recipient-address').value = '';
        document.getElementById('cr-gift-message').value = '';
    }
}

function openCustomRequest() { 
    document.getElementById('custom-request-modal').classList.add('open'); 
}

function closeCustomRequest() {
    document.getElementById('custom-request-modal').classList.remove('open');
    ['cr-name','cr-phone','cr-location','cr-vision','cr-recipient-name','cr-recipient-phone','cr-recipient-address','cr-gift-message'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const requestType = document.getElementById('cr-request-type');
    if (requestType) requestType.value = '';
    const isGift = document.getElementById('cr-is-gift');
    if (isGift) isGift.checked = false;
    document.getElementById('cr-gift-details').classList.remove('active');
    const previewContainer = document.getElementById('cr-image-preview-container');
    if (previewContainer) previewContainer.innerHTML = '';
    crUploadedImages = [];
    const errorEl = document.getElementById('cr-error');
    if (errorEl) errorEl.style.display = 'none';
    
    const submitBtn = document.querySelector('#custom-request-modal .modal-footer button:last-child');
    if (submitBtn) {
        submitBtn.textContent = 'Send Request';
        submitBtn.disabled = false;
        submitBtn.style.background = 'var(--rose)';
    }
    
    restoreCustomRequestForm();
}

function restoreCustomRequestForm() {
    const modalHeader = document.getElementById('cr-modal-header');
    const modalBody = document.getElementById('cr-modal-body');
    const modalFooter = document.getElementById('cr-modal-footer');
    
    if (!modalHeader || !modalBody || !modalFooter) return;
    
    modalHeader.innerHTML = `
        <h2>Share Your Vision</h2>
        <button class="modal-close" onclick="closeCustomRequest()">✕</button>
    `;
    
    modalBody.innerHTML = `
        <div style="background:var(--cream-mid);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:15px;color:var(--text-mid);">
            Tell us what you're dreaming of — we'll create something magical!
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <input type="text" id="cr-name" placeholder="Your Name *" class="input-field">
            <input type="tel" id="cr-phone" placeholder="Phone *" class="input-field">
        </div>
        <select id="cr-request-type" class="input-field" style="margin-bottom:10px;">
            <option value="">What are you looking for? *</option>
            <option value="Flowers">Flowers</option>
            <option value="Gift Package">Gift Package</option>
            <option value="Decorations">Decorations</option>
            <option value="Event">Event</option>
            <option value="Other">Other</option>
        </select>
        <textarea id="cr-location" rows="2" placeholder="Location / Delivery Address *" class="input-field" style="margin-bottom:10px;"></textarea>
        <div class="gift-fields">
            <label>
                <input type="checkbox" id="cr-is-gift" onchange="toggleGiftFields()" style="accent-color:var(--rose);">
                This is a gift
            </label>
            <div class="gift-details" id="cr-gift-details">
                <input type="text" id="cr-recipient-name" placeholder="Recipient's Name *" class="input-field">
                <input type="tel" id="cr-recipient-phone" placeholder="Recipient's Phone Number *" class="input-field">
                <textarea id="cr-recipient-address" rows="2" placeholder="Recipient's Delivery Address *" class="input-field"></textarea>
                <textarea id="cr-gift-message" rows="2" placeholder="Gift message (optional)" class="input-field"></textarea>
            </div>
        </div>
        <textarea id="cr-vision" rows="3" placeholder="Describe your vision… colours, mood, occasion *" class="input-field" style="margin-bottom:10px;"></textarea>
        <div class="upload-zone" onclick="document.getElementById('cr-inspo-images').click()">
            <input type="file" id="cr-inspo-images" multiple accept="image/*" style="display:none" onchange="handleCustomRequestImageUpload(event)">
            <div style="font-size:20px;margin-bottom:2px;">📷</div>
            <p style="font-weight:500;color:var(--text-mid);font-size:14px;">Upload inspiration photos</p>
            <p style="font-size:12px;color:var(--text-light);">Tap to browse (max 5MB each)</p>
        </div>
        <div id="cr-image-preview-container" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;"></div>
        <div style="display:flex;gap:12px;margin-top:10px;font-size:14px;">
            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="contact_method" value="Phone Call" checked style="accent-color:var(--rose);"> Call me</label>
            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="contact_method" value="WhatsApp" style="accent-color:var(--rose);"> WhatsApp</label>
        </div>
        <div id="cr-error" style="display:none;margin-top:10px;"></div>
    `;
    
    modalFooter.innerHTML = `
        <button onclick="closeCustomRequest()" style="flex:1;padding:10px;border:0.5px solid var(--cream-warm);border-radius:4px;background:transparent;color:var(--text-mid);font-size:14px;cursor:pointer;">Cancel</button>
        <button onclick="submitCustomRequest()" style="flex:2;padding:10px;background:var(--rose);color:#fff;border:none;border-radius:4px;font-size:14px;font-weight:500;cursor:pointer;transition:background 0.2s;">Send Request</button>
    `;
}

function handleCustomRequestImageUpload(event) {
    const files = event.target.files;
    if (!files) return;
    
    Array.from(files).forEach(file => {
        if (file.size > 5 * 1024 * 1024) { 
            showToast('⚠️', `${file.name} too large (max 5MB)`); 
            return; 
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            crUploadedImages.push(e.target.result);
            
            const container = document.getElementById('cr-image-preview-container');
            const preview = document.createElement('div');
            preview.style.position = 'relative';
            preview.style.display = 'inline-block';
            preview.style.margin = '4px';
            preview.innerHTML = `
                <img src="${e.target.result}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:2px solid var(--rose-light);">
                <button onclick="this.parentElement.remove()" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border:none;border-radius:50%;width:18px;height:18px;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.2);">×</button>
            `;
            container.appendChild(preview);
        };
        reader.readAsDataURL(file);
    });
    event.target.value = '';
}

async function submitCustomRequest() {
    const name = document.getElementById('cr-name').value.trim();
    const phone = document.getElementById('cr-phone').value.trim();
    const requestType = document.getElementById('cr-request-type').value;
    const location = document.getElementById('cr-location').value.trim();
    const vision = document.getElementById('cr-vision').value.trim();
    const isGift = document.getElementById('cr-is-gift').checked;
    const recipientName = isGift ? document.getElementById('cr-recipient-name').value.trim() : '';
    const recipientPhone = isGift ? document.getElementById('cr-recipient-phone').value.trim() : '';
    const recipientAddress = isGift ? document.getElementById('cr-recipient-address').value.trim() : '';
    const giftMessage = isGift ? document.getElementById('cr-gift-message').value.trim() : '';
    
    const errorEl = document.getElementById('cr-error');
    const submitBtn = document.querySelector('#custom-request-modal .modal-footer button:last-child');
    const modalBody = document.getElementById('cr-modal-body');
    const modalFooter = document.getElementById('cr-modal-footer');
    const modalHeader = document.getElementById('cr-modal-header');
    
    if (!name || !phone || !requestType || !location || !vision) {
        errorEl.textContent = 'Please fill in all required fields';
        errorEl.style.display = 'block';
        errorEl.style.color = '#ef4444';
        errorEl.style.background = '#fee2e2';
        errorEl.style.padding = '10px';
        errorEl.style.borderRadius = '8px';
        errorEl.style.marginTop = '10px';
        errorEl.style.border = '1px solid #fecaca';
        return;
    }
    
    if (isGift) {
        if (!recipientName || !recipientPhone || !recipientAddress) {
            errorEl.textContent = 'Please fill in all recipient information (name, phone, address)';
            errorEl.style.display = 'block';
            errorEl.style.color = '#ef4444';
            errorEl.style.background = '#fee2e2';
            errorEl.style.padding = '10px';
            errorEl.style.borderRadius = '8px';
            errorEl.style.marginTop = '10px';
            errorEl.style.border = '1px solid #fecaca';
            return;
        }
    }
    errorEl.style.display = 'none';
    
    const contactMethod = document.querySelector('input[name="contact_method"]:checked')?.value || 'Phone Call';
    
    submitBtn.textContent = 'Sending...';
    submitBtn.style.background = '#6B7280';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('custom_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                name, 
                phone, 
                requestType, 
                location, 
                vision, 
                isGift,
                recipientName,
                recipientPhone,
                recipientAddress,
                giftMessage,
                contactMethod,
                images: crUploadedImages 
            })
        });
        
        const result = await response.json();
        
        if (result.success) { 
            const requestTypeDisplay = requestType.charAt(0).toUpperCase() + requestType.slice(1);
            
            modalBody.innerHTML = `
                <div style="text-align:center;padding:20px 10px;">
                    <div class="success-check">
                        <span>✓</span>
                    </div>
                    <h2 style="font-family:'Playfair Display',serif;color:var(--rose);font-size:26px;margin-bottom:8px;">Request Sent</h2>
                    <p style="color:var(--text-mid);font-size:16px;margin-bottom:4px;">Your custom request has been received.</p>
                    <p style="color:var(--text-light);font-size:15px;margin-bottom:16px;">We'll reach out to you within 24 hours.</p>
                    <div style="background:var(--cream-mid);border-radius:12px;padding:16px;margin-bottom:12px;text-align:left;">
                        <p style="font-size:13px;color:var(--text-light);margin-bottom:4px;">Request Summary</p>
                        <p style="font-size:15px;font-weight:500;color:var(--text-dark);">${escapeHtml(name)}</p>
                        <p style="font-size:14px;color:var(--text-mid);">Phone: ${escapeHtml(phone)}</p>
                        <p style="font-size:14px;color:var(--text-mid);">Request: ${escapeHtml(requestTypeDisplay)}</p>
                        <p style="font-size:14px;color:var(--text-mid);">Location: ${escapeHtml(location)}</p>
                        ${isGift ? `<p style="font-size:14px;color:var(--text-mid);">Gift for: ${escapeHtml(recipientName)}</p>` : ''}
                        ${isGift && recipientPhone ? `<p style="font-size:14px;color:var(--text-mid);">Recipient Phone: ${escapeHtml(recipientPhone)}</p>` : ''}
                        ${isGift && recipientAddress ? `<p style="font-size:14px;color:var(--text-mid);">Recipient Address: ${escapeHtml(recipientAddress)}</p>` : ''}
                    </div>
                    <div style="background:var(--rose);color:white;border-radius:12px;padding:12px;margin-bottom:8px;">
                        <p style="font-size:15px;font-weight:500;">We'll create something beautiful for you</p>
                    </div>
                </div>
            `;
            
            modalFooter.innerHTML = `
                <button onclick="closeCustomRequest()" style="flex:1;padding:10px;background:var(--rose);color:#fff;border:none;border-radius:4px;font-size:14px;font-weight:500;cursor:pointer;transition:background 0.2s;">Continue</button>
            `;
            
            modalHeader.innerHTML = `
                <h2 style="font-family:'Playfair Display',serif;color:var(--rose);">Request Sent</h2>
                <button class="modal-close" onclick="closeCustomRequest()">✕</button>
            `;
            
            showToast('✓', 'Request sent! We\'ll contact you soon.');
            
        } else {
            errorEl.textContent = 'Error: ' + (result.error || 'Something went wrong. Please try again.');
            errorEl.style.display = 'block';
            errorEl.style.color = '#ef4444';
            errorEl.style.background = '#fee2e2';
            errorEl.style.padding = '10px';
            errorEl.style.borderRadius = '8px';
            errorEl.style.marginTop = '10px';
            errorEl.style.border = '1px solid #fecaca';
            
            submitBtn.textContent = 'Send Request';
            submitBtn.style.background = 'var(--rose)';
            submitBtn.disabled = false;
            
            showToast('✗', result.error || 'Failed to submit');
        }
    } catch (error) {
        console.error('Submit error:', error);
        errorEl.textContent = 'Network error. Please check your connection and try again.';
        errorEl.style.display = 'block';
        errorEl.style.color = '#ef4444';
        errorEl.style.background = '#fee2e2';
        errorEl.style.padding = '10px';
        errorEl.style.borderRadius = '8px';
        errorEl.style.marginTop = '10px';
        errorEl.style.border = '1px solid #fecaca';
        
        submitBtn.textContent = 'Send Request';
        submitBtn.style.background = 'var(--rose)';
        submitBtn.disabled = false;
        
        showToast('✗', 'Network error. Please try again.');
    }
}
</script>
</body>
</html>
<?php
// admin/products.php - With Tax Fields for Each Payment Method (No Default Tax)
require_once '../functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle product actions with image upload and tax fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_product':
                $image_url = '';
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadProductImage($_FILES['product_image']);
                    if ($uploaded) {
                        $image_url = $uploaded;
                    }
                }
                addProduct([
                    'name' => $_POST['name'],
                    'main_category' => $_POST['main_category'],
                    'sub_category' => $_POST['sub_category'] ?? null,
                    'price' => $_POST['price'],
                    'tax_mpesa' => $_POST['tax_mpesa'] ?? 0,
                    'tax_bank' => $_POST['tax_bank'] ?? 0,
                    'image_url' => $image_url,
                    'description' => $_POST['description'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'featured' => isset($_POST['featured']) ? 1 : 0
                ]);
                header('Location: products.php?success=added');
                break;
                
            case 'update_product':
                $image_url = $_POST['existing_image'] ?? '';
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadProductImage($_FILES['product_image']);
                    if ($uploaded) {
                        $image_url = $uploaded;
                    }
                }
                updateProduct($_POST['id'], [
                    'name' => $_POST['name'],
                    'main_category' => $_POST['main_category'],
                    'sub_category' => $_POST['sub_category'] ?? null,
                    'price' => $_POST['price'],
                    'tax_mpesa' => $_POST['tax_mpesa'] ?? 0,
                    'tax_bank' => $_POST['tax_bank'] ?? 0,
                    'image_url' => $image_url,
                    'description' => $_POST['description'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'featured' => isset($_POST['featured']) ? 1 : 0
                ]);
                header('Location: products.php?success=updated');
                break;
                
            case 'delete_product':
                deleteProduct($_POST['id']);
                header('Location: products.php?success=deleted');
                break;
                
            case 'toggle_status':
                toggleProductStatus($_POST['id'], $_POST['status']);
                header('Location: products.php?success=status');
                break;
        }
        exit;
    }
}

$products = getAllProducts();

// Get filters
$categoryFilter = $_GET['category'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Filter products
$filteredProducts = array_filter($products, function($product) use ($categoryFilter, $statusFilter, $searchQuery) {
    if ($categoryFilter !== 'all' && $product['main_category'] !== $categoryFilter) {
        return false;
    }
    if ($statusFilter !== 'all' && ($product['is_active'] ?? 1) != $statusFilter) {
        return false;
    }
    if ($searchQuery) {
        $searchLower = strtolower($searchQuery);
        if (strpos(strtolower($product['name']), $searchLower) === false &&
            strpos(strtolower($product['main_category']), $searchLower) === false) {
            return false;
        }
    }
    return true;
});

$totalProducts = count($products);
$activeCount = count(array_filter($products, function($p) { return ($p['is_active'] ?? 1) == 1; }));
$draftCount = count(array_filter($products, function($p) { return ($p['is_active'] ?? 1) == 0; }));

$categoryCounts = [
    'Flowers' => count(array_filter($products, function($p) { return $p['main_category'] === 'Flowers'; })),
    'Gift Packages' => count(array_filter($products, function($p) { return $p['main_category'] === 'Gift Packages'; })),
    'Decorations' => count(array_filter($products, function($p) { return $p['main_category'] === 'Decorations'; }))
];

// Get subcategories for dropdown
$subCategories = [
    'Flowers' => ['Fresh Flowers', 'Artificial Flowers', 'Dried Flowers', 'Seasonal'],
    'Gift Packages' => ['Birthday', 'Anniversary', 'Wedding', 'Corporate', 'Get Well', 'Custom'],
    'Decorations' => ['Wedding Decor', 'Party Decor', 'Event Decor', 'Home Decor', 'Seasonal']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Studio | Wrapped by Vee</title>
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
        .sidebar-footer .logout-link:hover { opacity: 0.7; }

        .stat-card {
            background: white; border-radius: 16px; padding: 16px 18px;
            border: 0.5px solid rgba(194,105,126,0.06);
            box-shadow: 0 2px 12px rgba(194,105,126,0.04);
            transition: all 0.3s ease;
        }
        .stat-card:active { transform: scale(0.98); }
        .stat-card .stat-label {
            font-size: 10px; font-weight: 600; color: #B8A0A8;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .stat-card .stat-value {
            font-size: 22px; font-weight: 700; color: #3B2229;
            margin-top: 2px; letter-spacing: -0.5px;
        }
        .stat-card .stat-value.rose { color: #C2697E; }
        .stat-card .stat-value.green { color: #059669; }
        .stat-card .stat-value.amber { color: #D97706; }

        .filter-chip {
            padding: 6px 14px; border-radius: 40px; font-size: 11px; font-weight: 500;
            background: white; border: 0.5px solid rgba(194,105,126,0.1);
            color: #B8A0A8; white-space: nowrap; text-decoration: none;
            display: inline-block; transition: all 0.2s ease;
        }
        .filter-chip.active {
            background: #C2697E; border-color: #C2697E; color: white;
            box-shadow: 0 2px 12px rgba(194,105,126,0.15);
        }
        .filter-chip:active { transform: scale(0.95); }
        .filter-chip .count { font-weight: 400; opacity: 0.7; }
        .filter-chip.active .count { opacity: 0.9; }

        .search-input {
            width: 100%; padding: 12px 16px 12px 44px;
            border: 0.5px solid rgba(194,105,126,0.1); border-radius: 50px;
            font-size: 13px; background: white; transition: all 0.2s ease;
            outline: none; font-family: 'Inter', sans-serif;
        }
        .search-input:focus { border-color: #C2697E; box-shadow: 0 0 0 3px rgba(194,105,126,0.06); }
        .search-input::placeholder { color: #C8B8C0; }

        .product-card {
            background: white; border-radius: 16px;
            border: 0.5px solid rgba(194,105,126,0.06);
            padding: 16px; margin-bottom: 10px;
            box-shadow: 0 2px 12px rgba(194,105,126,0.03);
            transition: all 0.3s ease;
        }
        .product-card:active { transform: scale(0.99); }
        @media (min-width: 768px) {
            .product-card:hover {
                box-shadow: 0 4px 24px rgba(194,105,126,0.08);
                transform: translateY(-2px);
            }
        }

        .filters-scroll {
            overflow-x: auto; white-space: nowrap;
            -webkit-overflow-scrolling: touch; scrollbar-width: none; padding-bottom: 4px;
        }
        .filters-scroll::-webkit-scrollbar { display: none; }

        .empty-state {
            text-align: center; padding: 60px 20px; background: white;
            border-radius: 20px; border: 0.5px solid rgba(194,105,126,0.06);
        }

        .back-btn {
            width: 40px; height: 40px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 12px rgba(194,105,126,0.06);
            border: 0.5px solid rgba(194,105,126,0.06);
            text-decoration: none; color: #C2697E; font-size: 18px;
        }
        .back-btn:active { transform: scale(0.92); }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            backdrop-filter: blur(12px); z-index: 100;
            display: none; align-items: flex-end; justify-content: center;
            padding: 0;
        }
        @media (min-width: 768px) {
            .modal-overlay { align-items: center; padding: 20px; }
        }
        .modal-overlay.open { display: flex; }

        .modal-content {
            background: white; border-radius: 24px 24px 0 0;
            max-width: 600px; width: 100%; max-height: 92vh;
            overflow-y: auto; padding: 24px 20px 20px;
            animation: slideUp 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @media (min-width: 768px) {
            .modal-content { border-radius: 24px; padding: 28px 32px 24px; animation: modalIn 0.3s ease; }
        }
        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 16px; border-bottom: 0.5px solid rgba(194,105,126,0.08);
            margin-bottom: 16px;
        }
        .modal-header h2 { font-size: 18px; font-weight: 700; color: #3B2229; }
        .modal-header .close-btn {
            background: none; border: none; font-size: 22px; color: #B8A0A8;
            cursor: pointer; padding: 4px 8px; border-radius: 8px;
        }
        .modal-header .close-btn:hover { background: #F5F0F2; color: #C2697E; }

        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block; font-size: 11px; font-weight: 600;
            color: #6B5A62; margin-bottom: 4px; letter-spacing: 0.3px;
        }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #EDE4E8; border-radius: 12px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            background: white; transition: all 0.2s; outline: none;
        }
        .form-control:focus { border-color: #C2697E; box-shadow: 0 0 0 3px rgba(194,105,126,0.06); }
        .form-control::placeholder { color: #C8B8C0; }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M6 8L1 3h10z' fill='%23B8A0A8'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }

        /* Tax Section - Only M-Pesa and Bank */
        .tax-section {
            background: linear-gradient(135deg, #FAF5F7, #F5F0F2);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            border: 0.5px solid rgba(194,105,126,0.08);
        }
        .tax-section .tax-title {
            font-size: 12px; font-weight: 600; color: #6B5A62;
            margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .tax-section .tax-title .icon { font-size: 14px; }
        .tax-section .tax-title .sub { font-weight: 400; color: #B8A0A8; font-size: 10px; }
        .tax-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .tax-row .tax-field label {
            font-size: 9px; font-weight: 600; color: #8A7A82;
            text-transform: uppercase; letter-spacing: 0.3px;
            display: block; margin-bottom: 2px;
        }
        .tax-row .tax-field .form-control {
            font-size: 12px; padding: 8px 12px;
            background: white;
        }
        .tax-row .tax-field .form-control:focus { border-color: #C2697E; }
        .tax-row .tax-field .hint {
            font-size: 9px; color: #C8B8C0; margin-top: 2px;
        }

        .upload-area {
            border: 2px dashed #EDE4E8; border-radius: 12px;
            padding: 20px; text-align: center; cursor: pointer;
            transition: all 0.2s; background: #FAF5F7;
        }
        .upload-area:hover { border-color: #C2697E; background: #FFF0F5; }
        .upload-area .icon { font-size: 28px; opacity: 0.4; margin-bottom: 4px; }
        .upload-area .text { font-size: 12px; color: #B8A0A8; }
        .upload-area .sub { font-size: 10px; color: #C8B8C0; }

        .image-preview-wrap {
            display: none; align-items: center; gap: 12px;
            padding: 10px; background: #FAF5F7; border-radius: 12px;
        }
        .image-preview-wrap.show { display: flex; }
        .image-preview-wrap img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 0.5px solid rgba(194,105,126,0.1); }
        .image-preview-wrap .info { flex: 1; }
        .image-preview-wrap .info p { font-size: 12px; font-weight: 500; color: #3B2229; }
        .image-preview-wrap .info span { font-size: 10px; color: #B8A0A8; }
        .image-preview-wrap .remove-btn {
            background: none; border: none; color: #DC2626;
            font-size: 16px; cursor: pointer; padding: 4px 8px;
        }
        .image-preview-wrap .remove-btn:hover { opacity: 0.7; }

        .btn-primary {
            background: #C2697E; color: white; border: none;
            padding: 12px 20px; border-radius: 12px; font-size: 13px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-primary:hover { background: #A8576A; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(194,105,126,0.25); }
        .btn-primary:active { transform: scale(0.97); }

        .btn-secondary {
            background: #F5F0F2; color: #6B5A62; border: none;
            padding: 12px 20px; border-radius: 12px; font-size: 13px;
            font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .btn-secondary:hover { background: #EDE4E8; }
        .btn-secondary:active { transform: scale(0.97); }

        .toggle-wrap {
            display: flex; align-items: center; gap: 10px;
        }
        .toggle {
            position: relative; width: 44px; height: 24px; flex-shrink: 0;
        }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle .slider {
            position: absolute; cursor: pointer; inset: 0;
            background: #E5E7EB; transition: .3s; border-radius: 34px;
        }
        .toggle .slider::before {
            content: ""; position: absolute; height: 18px; width: 18px;
            left: 3px; bottom: 3px; background: white; transition: .3s;
            border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .toggle input:checked + .slider { background: #C2697E; }
        .toggle input:checked + .slider::before { transform: translateX(20px); }
        .toggle-label { font-size: 12px; color: #6B5A62; font-weight: 500; }
        .toggle-label .status-text { font-weight: 600; }
        .toggle-label .status-text.active { color: #059669; }
        .toggle-label .status-text.draft { color: #D97706; }

        .modal-content::-webkit-scrollbar { width: 4px; }
        .modal-content::-webkit-scrollbar-track { background: transparent; }
        .modal-content::-webkit-scrollbar-thumb { background: #E8D8DE; border-radius: 4px; }

        @media (max-width: 480px) {
            .stat-card .stat-value { font-size: 18px; }
            .product-card { padding: 14px; }
            .modal-content { padding: 16px; }
            .tax-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item"><span class="nav-icon">✦</span><span>Home</span></a>
    <a href="orders.php" class="nav-item"><span class="nav-icon">◌</span><span>Orders</span></a>
    <a href="products.php" class="nav-item active"><span class="nav-icon">◍</span><span>Studio</span></a>
    <a href="finance.php" class="nav-item"><span class="nav-icon">◊</span><span>Finance</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">◎</span><span>Settings</span></a>
</div>

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
        <a href="products.php" class="active"><span class="nav-dot"></span>Studio</a>
        <a href="finance.php"><span class="nav-dot"></span>Finance</a>
        <a href="settings.php"><span class="nav-dot"></span>Settings</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
        <div class="user-role">Administrator</div>
        <a href="../logout.php" class="logout-link">Sign out →</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <a href="index.php" class="back-btn"><span>←</span></a>
            <div>
                <h1 class="text-2xl font-bold text-rose-700 tracking-tight">Inspiration Studio</h1>
                <p class="text-sm text-gray-400 mt-0.5">Set M-Pesa and Bank Transfer taxes per product</p>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value rose"><?php echo $totalProducts; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active</div>
                <div class="stat-value green"><?php echo $activeCount; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Draft</div>
                <div class="stat-value amber"><?php echo $draftCount; ?></div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="mb-4">
            <form method="GET" class="relative">
                <input type="text" name="search" placeholder="Search pieces by name or category..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300 text-sm">⌕</span>
                <?php if($searchQuery): ?>
                    <a href="products.php" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-rose-400 text-xs font-medium">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Category Filters -->
        <div class="filters-scroll mb-3">
            <div class="flex gap-2">
                <a href="?category=all&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $categoryFilter === 'all' ? 'active' : ''; ?>">
                    All <span class="count">(<?php echo $totalProducts; ?>)</span>
                </a>
                <a href="?category=Flowers&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $categoryFilter === 'Flowers' ? 'active' : ''; ?>">
                    Floral <span class="count">(<?php echo $categoryCounts['Flowers']; ?>)</span>
                </a>
                <a href="?category=Gift Packages&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $categoryFilter === 'Gift Packages' ? 'active' : ''; ?>">
                    Gift <span class="count">(<?php echo $categoryCounts['Gift Packages']; ?>)</span>
                </a>
                <a href="?category=Decorations&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $categoryFilter === 'Decorations' ? 'active' : ''; ?>">
                    Decor <span class="count">(<?php echo $categoryCounts['Decorations']; ?>)</span>
                </a>
            </div>
        </div>
        
        <!-- Status Filters -->
        <div class="filters-scroll mb-5">
            <div class="flex gap-2">
                <a href="?category=<?php echo $categoryFilter; ?>&status=all&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">All Status</a>
                <a href="?category=<?php echo $categoryFilter; ?>&status=1&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $statusFilter === '1' ? 'active' : ''; ?>">Active</a>
                <a href="?category=<?php echo $categoryFilter; ?>&status=0&search=<?php echo urlencode($searchQuery); ?>" 
                   class="filter-chip <?php echo $statusFilter === '0' ? 'active' : ''; ?>">Draft</a>
            </div>
        </div>
        
        <!-- Add Button & Results -->
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-400 font-medium"><?php echo count($filteredProducts); ?> piece(s)</p>
            <button onclick="openAddModal()" class="bg-rose-500 text-white px-5 py-2.5 rounded-full text-sm font-medium shadow-md hover:bg-rose-600 transition active:scale-95">
                + Add Piece
            </button>
        </div>
        
        <!-- Products List -->
        <div class="space-y-3">
            <?php if(count($filteredProducts) > 0): ?>
                <?php foreach($filteredProducts as $product): 
                    $isActive = $product['is_active'] ?? 1;
                ?>
                <div class="product-card">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <?php if(!empty($product['image_url']) && file_exists('../' . $product['image_url'])): ?>
                                <img src="../<?php echo $product['image_url']; ?>" class="w-12 h-12 rounded-xl object-cover border border-rose-100 flex-shrink-0">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-300 text-xl flex-shrink-0">✦</div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></p>
                                <p class="text-xs text-gray-400">
                                    <?php 
                                        $catLabels = ['Flowers' => 'Floral', 'Gift Packages' => 'Gift', 'Decorations' => 'Decor'];
                                        echo $catLabels[$product['main_category']] ?? $product['main_category'];
                                    ?>
                                    <?php if(!empty($product['sub_category'])): ?>
                                        • <?php echo htmlspecialchars($product['sub_category']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <span class="font-bold text-rose-600 text-lg flex-shrink-0 ml-2">TZS <?php echo number_format($product['price'], 0); ?></span>
                    </div>
                    
                    <!-- Tax Summary - Only M-Pesa and Bank -->
                    <div class="flex flex-wrap gap-2 mb-3 text-xs">
                        <span class="bg-rose-50 text-rose-600 px-2 py-1 rounded-full">M-Pesa Tax: TZS <?php echo number_format($product['tax_mpesa'] ?? 0, 0); ?></span>
                        <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded-full">Bank Tax: TZS <?php echo number_format($product['tax_bank'] ?? 0, 0); ?></span>
                    </div>
                    
                    <?php if(!empty($product['description'])): ?>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                    <?php endif; ?>
                    
                    <div class="flex flex-wrap justify-between items-center gap-2 pt-2 border-t border-rose-50">
                        <div class="flex items-center gap-3">
                            <form method="POST" class="inline toggle-form">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $isActive ? 0 : 1; ?>">
                                <div class="toggle-wrap">
                                    <label class="toggle">
                                        <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?> onchange="this.closest('form').submit()">
                                        <span class="slider"></span>
                                    </label>
                                    <span class="toggle-label">
                                        Status: 
                                        <span class="status-text <?php echo $isActive ? 'active' : 'draft'; ?>">
                                            <?php echo $isActive ? 'Active' : 'Draft'; ?>
                                        </span>
                                    </span>
                                </div>
                            </form>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='editProduct(<?php echo json_encode($product); ?>)' class="text-stone-500 text-sm font-medium px-3 py-1.5 rounded-full hover:bg-stone-50 transition active:scale-95">
                                Edit
                            </button>
                            <form method="POST" class="inline" onsubmit="return confirm('Remove this piece from collection?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="text-red-400 text-sm font-medium px-3 py-1.5 rounded-full hover:bg-red-50 transition active:scale-95">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="text-5xl mb-3 text-rose-200 font-light">◍</div>
                    <p class="text-gray-500 font-medium">No pieces in collection</p>
                    <p class="text-xs text-gray-400 mt-1">Start building your inspiration catalog</p>
                    <button onclick="openAddModal()" class="inline-block mt-4 text-rose-500 text-sm font-medium">
                        Add your first piece →
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Modal with Tax Fields (No Default Tax) -->
<div id="product-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Add Piece</h2>
            <button class="close-btn" onclick="closeModal()">✕</button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="product-form">
            <input type="hidden" name="action" id="form-action" value="add_product">
            <input type="hidden" name="id" id="product-id">
            <input type="hidden" name="existing_image" id="existing-image">
            
            <!-- Name -->
            <div class="form-group">
                <label>Piece Name *</label>
                <input type="text" name="name" id="prod-name" class="form-control" placeholder="e.g., Classic Red Rose Bouquet" required>
            </div>
            
            <!-- Main Category -->
            <div class="form-group">
                <label>Category *</label>
                <select name="main_category" id="prod-cat" class="form-control" onchange="updateSubCategories()" required>
                    <option value="Flowers">Floral</option>
                    <option value="Gift Packages">Gift</option>
                    <option value="Decorations">Decor</option>
                </select>
            </div>
            
            <!-- Sub Category -->
            <div class="form-group">
                <label>Sub-Category</label>
                <select name="sub_category" id="prod-sub" class="form-control">
                    <option value="">— Select sub-category —</option>
                </select>
            </div>
            
            <!-- Price -->
            <div class="form-group">
                <label>Price (TZS) *</label>
                <input type="number" name="price" id="prod-price" class="form-control" placeholder="e.g., 45000" required>
            </div>
            
            <!-- ============================================ -->
            <!-- TAX SECTION - ONLY M-PESA AND BANK (No Default) -->
            <!-- ============================================ -->
            <div class="tax-section">
                <div class="tax-title">
                    <span class="icon">◈</span> Tax Settings by Payment Method
                    <span class="sub">— Fixed amount in TZS</span>
                </div>
                <div class="tax-row">
                    <div class="tax-field">
                        <label>M-Pesa Tax</label>
                        <input type="number" name="tax_mpesa" id="prod-tax-mpesa" class="form-control" placeholder="0" value="0" step="100">
                        <div class="hint">Tax applied when paying with M-Pesa</div>
                    </div>
                    <div class="tax-field">
                        <label>Bank Transfer Tax</label>
                        <input type="number" name="tax_bank" id="prod-tax-bank" class="form-control" placeholder="0" value="0" step="100">
                        <div class="hint">Tax applied when paying via Bank Transfer</div>
                    </div>
                </div>
                <div style="margin-top:8px;font-size:10px;color:#B8A0A8;text-align:center;border-top:0.5px solid rgba(194,105,126,0.08);padding-top:8px;">
                    ⚡ Tax updates automatically in checkout when payment method changes
                </div>
            </div>
            
            <!-- Image Upload -->
            <div class="form-group">
                <label>Piece Image</label>
                <div class="upload-area" id="upload-area" onclick="document.getElementById('product-image').click()">
                    <input type="file" name="product_image" id="product-image" accept="image/*" style="display:none" onchange="previewImage(this)">
                    <div id="upload-placeholder">
                        <div class="icon">◍</div>
                        <p class="text">Click to upload image</p>
                        <p class="sub">PNG, JPG, WEBP up to 5MB</p>
                    </div>
                    <div id="image-preview" class="image-preview-wrap">
                        <img id="preview-img" src="">
                        <div class="info">
                            <p id="preview-name">Image uploaded</p>
                            <span id="preview-size"></span>
                        </div>
                        <button type="button" class="remove-btn" onclick="removeImage()">✕</button>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label>Description / Inspiration Notes</label>
                <textarea name="description" id="prod-desc" class="form-control" rows="3" placeholder="Describe the piece, its inspiration, or special details..." style="resize:vertical;"></textarea>
            </div>
            
            <!-- Status Toggle -->
            <div class="form-group">
                <label>Status</label>
                <div style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle">
                        <input type="checkbox" name="is_active" id="prod-active" checked>
                        <span class="slider"></span>
                    </label>
                    <span style="font-size:13px;color:#6B5A62;">
                        <span id="status-label" style="font-weight:600;color:#059669;">Active</span>
                        <span style="color:#B8A0A8;font-size:11px;">— visible to customers</span>
                    </span>
                </div>
            </div>
            
            <!-- Featured -->
            <div class="form-group">
                <label>Featured</label>
                <div style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle">
                        <input type="checkbox" name="featured" id="prod-featured">
                        <span class="slider"></span>
                    </label>
                    <span style="font-size:13px;color:#6B5A62;">
                        <span id="featured-label" style="font-weight:600;color:#B8A0A8;">No</span>
                        <span style="color:#B8A0A8;font-size:11px;">— show on homepage</span>
                    </span>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn-primary flex-1">Save Piece</button>
                <button type="button" class="btn-secondary flex-1" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Subcategories data
const subCategories = {
    'Flowers': ['Fresh Flowers', 'Artificial Flowers', 'Dried Flowers', 'Seasonal'],
    'Gift Packages': ['Birthday', 'Anniversary', 'Wedding', 'Corporate', 'Get Well', 'Custom'],
    'Decorations': ['Wedding Decor', 'Party Decor', 'Event Decor', 'Home Decor', 'Seasonal']
};

// Update subcategories dropdown
function updateSubCategories() {
    const cat = document.getElementById('prod-cat').value;
    const subSelect = document.getElementById('prod-sub');
    const currentValue = subSelect.value;
    subSelect.innerHTML = '<option value="">— Select sub-category —</option>';
    
    if (subCategories[cat]) {
        subCategories[cat].forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub;
            opt.textContent = sub;
            if (sub === currentValue) opt.selected = true;
            subSelect.appendChild(opt);
        });
    }
}

// Open modal for adding
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Piece';
    document.getElementById('form-action').value = 'add_product';
    document.getElementById('product-id').value = '';
    document.getElementById('prod-name').value = '';
    document.getElementById('prod-cat').value = 'Flowers';
    document.getElementById('prod-price').value = '';
    document.getElementById('prod-desc').value = '';
    document.getElementById('existing-image').value = '';
    document.getElementById('prod-active').checked = true;
    document.getElementById('prod-featured').checked = false;
    // Reset tax fields (only M-Pesa and Bank now)
    document.getElementById('prod-tax-mpesa').value = '0';
    document.getElementById('prod-tax-bank').value = '0';
    updateStatusLabel();
    updateFeaturedLabel();
    updateSubCategories();
    resetImageUpload();
    document.getElementById('product-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

// Open modal for editing
function editProduct(product) {
    document.getElementById('modal-title').textContent = 'Edit Piece';
    document.getElementById('form-action').value = 'update_product';
    document.getElementById('product-id').value = product.id;
    document.getElementById('prod-name').value = product.name;
    document.getElementById('prod-cat').value = product.main_category;
    document.getElementById('prod-price').value = product.price;
    document.getElementById('prod-desc').value = product.description || '';
    document.getElementById('existing-image').value = product.image_url || '';
    document.getElementById('prod-active').checked = (product.is_active || 1) == 1;
    document.getElementById('prod-featured').checked = (product.featured || 0) == 1;
    // Set tax fields (only M-Pesa and Bank)
    document.getElementById('prod-tax-mpesa').value = product.tax_mpesa || 0;
    document.getElementById('prod-tax-bank').value = product.tax_bank || 0;
    updateStatusLabel();
    updateFeaturedLabel();
    updateSubCategories();
    
    // Set subcategory
    if (product.sub_category) {
        document.getElementById('prod-sub').value = product.sub_category;
    }
    
    // Show existing image
    if (product.image_url) {
        document.getElementById('upload-placeholder').style.display = 'none';
        const preview = document.getElementById('image-preview');
        preview.classList.add('show');
        document.getElementById('preview-img').src = '../' + product.image_url;
        document.getElementById('preview-name').textContent = product.image_url.split('/').pop();
        document.getElementById('preview-size').textContent = 'Existing image';
    } else {
        resetImageUpload();
    }
    
    document.getElementById('product-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeModal() {
    document.getElementById('product-modal').classList.remove('open');
    document.body.style.overflow = '';
}

// Update status label
function updateStatusLabel() {
    const checked = document.getElementById('prod-active').checked;
    const label = document.getElementById('status-label');
    if (checked) {
        label.textContent = 'Active';
        label.style.color = '#059669';
    } else {
        label.textContent = 'Draft';
        label.style.color = '#D97706';
    }
}

// Update featured label
function updateFeaturedLabel() {
    const checked = document.getElementById('prod-featured').checked;
    const label = document.getElementById('featured-label');
    if (checked) {
        label.textContent = 'Yes';
        label.style.color = '#C2697E';
    } else {
        label.textContent = 'No';
        label.style.color = '#B8A0A8';
    }
}

// Image preview
function previewImage(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('upload-placeholder').style.display = 'none';
            const preview = document.getElementById('image-preview');
            preview.classList.add('show');
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('preview-name').textContent = file.name;
            document.getElementById('preview-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
        };
        reader.readAsDataURL(file);
    }
}

// Remove image
function removeImage() {
    resetImageUpload();
    document.getElementById('product-image').value = '';
}

// Reset image upload
function resetImageUpload() {
    document.getElementById('upload-placeholder').style.display = 'block';
    document.getElementById('image-preview').classList.remove('show');
    document.getElementById('preview-img').src = '';
    document.getElementById('preview-name').textContent = '';
    document.getElementById('preview-size').textContent = '';
}

// Event listeners for toggles
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('prod-active').addEventListener('change', function() {
        updateStatusLabel();
    });
    document.getElementById('prod-featured').addEventListener('change', function() {
        updateFeaturedLabel();
    });
});

// Close modal on overlay click
document.getElementById('product-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
</body>
</html>
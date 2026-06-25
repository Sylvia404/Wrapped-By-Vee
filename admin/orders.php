<?php
require_once '../functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_order_status') {
            updateTrackingStatus($_POST['order_id'], $_POST['tracking_status']);
        } elseif ($_POST['action'] === 'confirm_payment') {
            updatePaymentStatus($_POST['order_id'], 'Paid');
            updateTrackingStatus($_POST['order_id'], 'Processing');
        }
        header('Location: orders.php');
        exit;
    }
}

// Get filters from URL
$orderType = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$dateFilter = $_GET['date'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

$allOrders = getAllOrders();
$customRequests = getCustomRequests();

// Separate orders by type
$regularOrders = array_filter($allOrders, function($o) { 
    return $o['payment_method'] !== 'Custom Request'; 
});
$customOrders = array_filter($allOrders, function($o) { 
    return $o['payment_method'] === 'Custom Request'; 
});

// Select which orders to show
if ($orderType === 'regular') {
    $orders = $regularOrders;
    $activeTab = 'regular';
} elseif ($orderType === 'custom') {
    $orders = $customOrders;
    $activeTab = 'custom';
} else {
    $orders = array_merge($regularOrders, $customOrders);
    $activeTab = 'all';
}

// Apply filters
$filteredOrders = array_filter($orders, function($order) use ($statusFilter, $dateFilter, $searchQuery) {
    if ($statusFilter !== 'all' && $order['payment_method'] !== 'Custom Request') {
        if (($order['tracking_status'] ?? 'Pending') !== $statusFilter) {
            return false;
        }
    }
    
    if ($dateFilter !== 'all') {
        $orderDate = date('Y-m-d', strtotime($order['created_at']));
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        
        if ($dateFilter === 'today' && $orderDate !== $today) return false;
        if ($dateFilter === 'week' && $orderDate < $weekAgo) return false;
        if ($dateFilter === 'month' && $orderDate < $monthAgo) return false;
    }
    
    if ($searchQuery) {
        $searchLower = strtolower($searchQuery);
        if (strpos(strtolower($order['client_name']), $searchLower) === false &&
            strpos(strtolower($order['phone']), $searchLower) === false &&
            strpos(strtolower($order['id']), $searchLower) === false) {
            return false;
        }
    }
    
    return true;
});

// Statistics
$totalRegularOrders = count($regularOrders);
$totalCustomOrders = count($customOrders);
$totalAllOrders = $totalRegularOrders + $totalCustomOrders;
$totalRevenue = array_sum(array_column($regularOrders, 'total_amount'));
$pendingCount = count(array_filter($regularOrders, function($o) { 
    return ($o['payment_status'] ?? 'Pending') !== 'Paid'; 
}));
$completedCount = count(array_filter($regularOrders, function($o) { 
    return ($o['tracking_status'] ?? '') === 'Delivered'; 
}));

$statusCounts = [
    'all' => count($regularOrders),
    'Pending' => count(array_filter($regularOrders, function($o) { return ($o['tracking_status'] ?? 'Pending') === 'Pending'; })),
    'Processing' => count(array_filter($regularOrders, function($o) { return ($o['tracking_status'] ?? '') === 'Processing'; })),
    'Ready' => count(array_filter($regularOrders, function($o) { return ($o['tracking_status'] ?? '') === 'Ready'; })),
    'Shipped' => count(array_filter($regularOrders, function($o) { return ($o['tracking_status'] ?? '') === 'Shipped'; })),
    'Delivered' => count(array_filter($regularOrders, function($o) { return ($o['tracking_status'] ?? '') === 'Delivered'; }))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Orders | Wrapped by Vee</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #F8F4F6;
            padding-bottom: 80px;
        }
        * { transition: all 0.2s ease; }
        
        .bottom-nav {
            position: fixed; 
            bottom: 0; 
            left: 0; 
            right: 0;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 0.5px solid rgba(194,105,126,0.12);
            padding: 8px 16px 12px;
            display: flex; 
            justify-content: space-around;
            z-index: 50;
            padding-bottom: max(12px, env(safe-area-inset-bottom));
        }
        .nav-item {
            display: flex; 
            flex-direction: column; 
            align-items: center;
            gap: 2px; 
            padding: 6px 12px; 
            border-radius: 30px;
            font-size: 10px; 
            color: #B8A0A8; 
            font-weight: 500;
            text-decoration: none;
            min-width: 56px;
            position: relative;
        }
        .nav-item.active { color: #C2697E; }
        .nav-item.active::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: #C2697E;
            border-radius: 0 0 4px 4px;
        }
        .nav-item:active { transform: scale(0.92); }
        .nav-icon { font-size: 20px; line-height: 1; font-weight: 300; }
        .nav-label { font-size: 9px; letter-spacing: 0.3px; }
        @media (min-width: 768px) { .bottom-nav { display: none; } }
        
        /* Desktop Sidebar - Consistent */
        .desktop-sidebar { display: none; }
        @media (min-width: 768px) {
            .desktop-sidebar {
                display: block; 
                position: fixed; 
                left: 0; 
                top: 0;
                width: 260px; 
                height: 100%; 
                background: white;
                border-right: 0.5px solid rgba(194,105,126,0.08);
                overflow-y: auto; 
                z-index: 40;
                padding: 32px 24px;
            }
            .main-content { margin-left: 260px; padding: 32px 40px 40px; }
            body { padding-bottom: 0; }
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .sidebar-brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #F5E8EC, #FDE8EE);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #C2697E;
            font-weight: 700;
        }
        .sidebar-brand-text { font-size: 18px; font-weight: 700; color: #3B2229; letter-spacing: -0.3px; }
        .sidebar-brand-text span { color: #C2697E; }
        .sidebar-brand-sub { font-size: 10px; color: #B8A0A8; font-weight: 400; letter-spacing: 0.5px; }

        .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #6B5A62;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sidebar-nav a:hover { background: #F8F4F6; color: #C2697E; }
        .sidebar-nav a.active { background: #F8F4F6; color: #C2697E; font-weight: 600; }
        .sidebar-nav a .nav-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.3;
        }
        .sidebar-nav a.active .nav-dot { opacity: 1; background: #C2697E; }

        .sidebar-footer {
            position: absolute;
            bottom: 32px;
            left: 24px;
            right: 24px;
            padding-top: 20px;
            border-top: 0.5px solid rgba(194,105,126,0.08);
        }
        .sidebar-footer .user-name { font-size: 13px; font-weight: 600; color: #3B2229; }
        .sidebar-footer .user-role { font-size: 11px; color: #B8A0A8; }
        .sidebar-footer .logout-link {
            display: inline-block;
            margin-top: 8px;
            font-size: 12px;
            color: #C2697E;
            text-decoration: none;
            font-weight: 500;
        }
        
        .orders-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #F5E8EC, #FDE8EE);
            border-radius: 14px;
            font-size: 22px;
            color: #C2697E;
            flex-shrink: 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 16px 18px;
            border: 0.5px solid rgba(194,105,126,0.06);
            box-shadow: 0 2px 12px rgba(194,105,126,0.04);
            transition: all 0.3s ease;
        }
        .stat-card:active { transform: scale(0.98); }
        .stat-card .stat-label {
            font-size: 10px;
            font-weight: 600;
            color: #B8A0A8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #3B2229;
            margin-top: 2px;
            letter-spacing: -0.5px;
        }
        .stat-card .stat-value.rose { color: #C2697E; }
        .stat-card .stat-value.amber { color: #D97706; }
        .stat-card .stat-value.green { color: #059669; }
        .stat-card .stat-sub { font-size: 10px; color: #B8A0A8; margin-top: 2px; }
        
        .tab-btn {
            padding: 8px 18px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 500;
            background: transparent;
            border: 0.5px solid rgba(194,105,126,0.15);
            color: #B8A0A8;
            text-decoration: none;
            display: inline-block;
            transition: all 0.25s ease;
        }
        .tab-btn.active { 
            background: #C2697E; 
            border-color: #C2697E; 
            color: white;
            box-shadow: 0 4px 16px rgba(194,105,126,0.25);
        }
        .tab-btn:active { transform: scale(0.95); }
        
        .filter-chip {
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 11px;
            font-weight: 500;
            background: white;
            border: 0.5px solid rgba(194,105,126,0.1);
            color: #B8A0A8;
            white-space: nowrap;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .filter-chip.active { 
            background: #C2697E; 
            border-color: #C2697E; 
            color: white;
            box-shadow: 0 2px 12px rgba(194,105,126,0.15);
        }
        .filter-chip:active { transform: scale(0.95); }
        .filter-chip .count { font-weight: 400; opacity: 0.7; }
        .filter-chip.active .count { opacity: 0.9; }
        
        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 0.5px solid rgba(194,105,126,0.1);
            border-radius: 50px;
            font-size: 13px;
            background: white;
            transition: all 0.2s ease;
            outline: none;
            font-family: 'Inter', sans-serif;
        }
        .search-input:focus { 
            border-color: #C2697E; 
            box-shadow: 0 0 0 3px rgba(194,105,126,0.06);
        }
        .search-input::placeholder { color: #C8B8C0; }
        
        .order-card {
            background: white;
            border-radius: 16px;
            border: 0.5px solid rgba(194,105,126,0.06);
            padding: 16px;
            margin-bottom: 10px;
            box-shadow: 0 2px 12px rgba(194,105,126,0.03);
            transition: all 0.3s ease;
        }
        .order-card:active { transform: scale(0.99); }
        @media (min-width: 768px) {
            .order-card:hover { 
                box-shadow: 0 4px 24px rgba(194,105,126,0.08);
                transform: translateY(-2px);
            }
        }
        
        .custom-card {
            background: white;
            border-radius: 16px;
            border: 0.5px solid rgba(194,105,126,0.06);
            padding: 16px;
            margin-bottom: 10px;
            border-left: 3px solid #9333EA;
            box-shadow: 0 2px 12px rgba(194,105,126,0.03);
        }
        .custom-card:active { transform: scale(0.99); }
        
        .status-badge {
            padding: 3px 12px;
            border-radius: 40px;
            font-size: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.2px;
        }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-processing { background: #DBEAFE; color: #1E40AF; }
        .status-ready { background: #D1FAE5; color: #065F46; }
        .status-shipped { background: #EDE9FE; color: #5B21B6; }
        .status-delivered { background: #A7F3D0; color: #065F46; }
        
        .request-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
        }
        .badge-flowers { background: #FFE0EC; color: #C2697E; }
        .badge-gift { background: #E8E6F0; color: #6B4C8A; }
        .badge-decor { background: #E6F0E8; color: #4A8A6B; }
        .badge-event { background: #FEF3C7; color: #D97706; }
        .badge-other { background: #F0F0F0; color: #6B7280; }
        
        .type-indicator {
            font-size: 8px;
            padding: 2px 8px;
            border-radius: 20px;
            background: #F0F0F0;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .type-regular { background: #E8F0FE; color: #2563EB; }
        .type-custom { background: #F3E8FF; color: #9333EA; }
        
        .vision-box {
            background: #FFF8F9;
            border-radius: 12px;
            padding: 10px 14px;
            margin-top: 10px;
            border: 0.5px solid rgba(194,105,126,0.06);
        }
        
        .filters-scroll {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 4px;
        }
        .filters-scroll::-webkit-scrollbar { display: none; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            border: 0.5px solid rgba(194,105,126,0.06);
        }
        
        .logout-fab {
            position: fixed; 
            bottom: 80px; 
            right: 16px; 
            z-index: 60;
            background: linear-gradient(135deg, #C2697E, #D98E9F);
            color: white; 
            width: 52px; 
            height: 52px; 
            border-radius: 50%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            box-shadow: 0 4px 20px rgba(194,105,126,0.3);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .logout-fab:active { transform: scale(0.92); }
        @media (min-width: 768px) { .logout-fab { display: none; } }
        
        /* Enhanced Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 640px;
            width: 100%;
            max-height: 95vh;
            overflow-y: auto;
            padding: 24px 24px 20px;
            animation: modalIn 0.3s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 0.5px solid rgba(194,105,126,0.08);
            margin-bottom: 16px;
        }
        .modal-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #C2697E;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 22px;
            color: #B8A0A8;
            cursor: pointer;
            padding: 4px 8px;
            transition: color 0.2s;
            border-radius: 8px;
        }
        .modal-close:hover { background: #F5F0F2; color: #C2697E; }
        
        .modal-body {
            padding: 0;
        }
        
        /* Proof Image Container */
        .proof-container {
            position: relative;
            background: #0a0a0a;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            min-height: 200px;
            max-height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .proof-container img {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
            transition: transform 0.3s ease;
            display: block;
        }
        .proof-container img.zoomed {
            transform: scale(1.8);
            cursor: zoom-out;
        }
        .proof-container .zoom-hint {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            color: rgba(255,255,255,0.7);
            font-size: 10px;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: 0.3px;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }
        .proof-container .zoom-hint.hidden {
            opacity: 0;
        }
        .proof-container .zoom-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            color: white;
            border: 0.5px solid rgba(255,255,255,0.1);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 5;
        }
        .proof-container .zoom-btn:active { transform: scale(0.9); }
        .proof-container .zoom-btn:hover { background: rgba(255,255,255,0.15); }
        
        /* Order Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
            background: #F8F4F6;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
        }
        @media (max-width: 480px) {
            .details-grid { grid-template-columns: 1fr; gap: 6px; }
        }
        .details-grid .item {
            display: flex;
            flex-direction: column;
        }
        .details-grid .item .label {
            font-size: 9px;
            font-weight: 600;
            color: #B8A0A8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .details-grid .item .value {
            font-size: 13px;
            font-weight: 500;
            color: #3B2229;
            margin-top: 1px;
            word-break: break-word;
        }
        .details-grid .item .value.rose { color: #C2697E; }
        .details-grid .item .value.green { color: #059669; }
        .details-grid .item .value.amber { color: #D97706; }
        
        .confirm-btn {
            background: #10B981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        .confirm-btn:hover { background: #059669; }
        .confirm-btn:active { transform: scale(0.97); }
        
        .proof-status {
            font-size: 11px;
            padding: 3px 14px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }
        .proof-paid { background: #D1FAE5; color: #059669; }
        .proof-pending { background: #FEF3C7; color: #D97706; }
        
        .btn-ghost-sm {
            background: transparent;
            border: 0.5px solid rgba(194,105,126,0.15);
            color: #C2697E;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-ghost-sm:active { transform: scale(0.95); }
        
        .modal-content::-webkit-scrollbar { width: 4px; }
        .modal-content::-webkit-scrollbar-track { background: transparent; }
        .modal-content::-webkit-scrollbar-thumb { background: #E8D8DE; border-radius: 4px; }
        
        @media (max-width: 480px) {
            .stat-card .stat-value { font-size: 18px; }
            .order-card { padding: 14px; }
            .modal-content { padding: 16px; }
            .proof-container { min-height: 150px; max-height: 320px; }
            .proof-container img { max-height: 320px; }
            .details-grid { padding: 12px; }
            .details-grid .item .value { font-size: 12px; }
            .orders-icon { width: 40px; height: 40px; font-size: 18px; }
        }
    </style>
</head>
<body>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item"><span class="nav-icon">✦</span><span>Home</span></a>
    <a href="orders.php" class="nav-item active"><span class="nav-icon">◌</span><span>Orders</span></a>
    <a href="products.php" class="nav-item"><span class="nav-icon">◍</span><span>Studio</span></a>
    <a href="finance.php" class="nav-item"><span class="nav-icon">◊</span><span>Finance</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">◎</span><span>Settings</span></a>
</div>

<!-- Floating Logout - Mobile -->
<a href="../logout.php" class="logout-fab" title="Sign out">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
    </svg>
</a>

<!-- Proof Modal -->
<div id="proof-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Payment Proof</h2>
            <button onclick="closeProofModal()" class="modal-close">✕</button>
        </div>
        <div class="modal-body" id="proof-content">
            <!-- Dynamic content loaded via JavaScript -->
        </div>
    </div>
</div>

<!-- Desktop Sidebar - Consistent -->
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
        <a href="orders.php" class="active"><span class="nav-dot"></span>Orders</a>
        <a href="products.php"><span class="nav-dot"></span>Studio</a>
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
        
        <!-- Header - Removed back arrow, added icon -->
        <div class="flex items-center gap-3 mb-6">
            <div class="orders-icon">◌</div>
            <div>
                <h1 class="text-2xl font-bold text-rose-700 tracking-tight">Orders</h1>
                <p class="text-sm text-gray-400 mt-0.5">Manage all customer orders and requests</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?php echo $totalAllOrders; ?></div>
                <div class="stat-sub"><?php echo $totalRegularOrders; ?> orders · <?php echo $totalCustomOrders; ?> custom</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Revenue</div>
                <div class="stat-value rose">TZS <?php echo number_format($totalRevenue, 0); ?></div>
                <div class="stat-sub">Total earned</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value amber"><?php echo $pendingCount; ?></div>
                <div class="stat-sub">Awaiting action</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-value green"><?php echo $completedCount; ?></div>
                <div class="stat-sub">Delivered orders</div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="flex gap-2 mb-4 overflow-x-auto pb-1">
            <a href="?type=all&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="tab-btn <?php echo $activeTab === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?type=regular&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="tab-btn <?php echo $activeTab === 'regular' ? 'active' : ''; ?>">Orders</a>
            <a href="?type=custom&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="tab-btn <?php echo $activeTab === 'custom' ? 'active' : ''; ?>">Custom</a>
        </div>
        
        <!-- Search -->
        <div class="mb-4">
            <form method="GET" class="relative">
                <input type="hidden" name="type" value="<?php echo $orderType; ?>">
                <input type="text" name="search" placeholder="Search by name, phone or order ID..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300 text-sm">⌕</span>
                <?php if($searchQuery): ?>
                    <a href="?type=<?php echo $orderType; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-rose-400 text-xs font-medium">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Status Filters -->
        <?php if($orderType !== 'custom'): ?>
        <div class="filters-scroll mb-3">
            <div class="flex gap-2">
                <a href="?type=<?php echo $orderType; ?>&status=all&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">All <span class="count">(<?php echo $statusCounts['all']; ?>)</span></a>
                <a href="?type=<?php echo $orderType; ?>&status=Pending&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'Pending' ? 'active' : ''; ?>">Pending <span class="count">(<?php echo $statusCounts['Pending']; ?>)</span></a>
                <a href="?type=<?php echo $orderType; ?>&status=Processing&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'Processing' ? 'active' : ''; ?>">Processing <span class="count">(<?php echo $statusCounts['Processing']; ?>)</span></a>
                <a href="?type=<?php echo $orderType; ?>&status=Ready&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'Ready' ? 'active' : ''; ?>">Ready <span class="count">(<?php echo $statusCounts['Ready']; ?>)</span></a>
                <a href="?type=<?php echo $orderType; ?>&status=Shipped&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'Shipped' ? 'active' : ''; ?>">Shipped <span class="count">(<?php echo $statusCounts['Shipped']; ?>)</span></a>
                <a href="?type=<?php echo $orderType; ?>&status=Delivered&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $statusFilter === 'Delivered' ? 'active' : ''; ?>">Delivered <span class="count">(<?php echo $statusCounts['Delivered']; ?>)</span></a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Date Filters -->
        <div class="filters-scroll mb-5">
            <div class="flex gap-2">
                <a href="?type=<?php echo $orderType; ?>&status=<?php echo $statusFilter; ?>&date=all&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="?type=<?php echo $orderType; ?>&status=<?php echo $statusFilter; ?>&date=today&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter === 'today' ? 'active' : ''; ?>">Today</a>
                <a href="?type=<?php echo $orderType; ?>&status=<?php echo $statusFilter; ?>&date=week&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter === 'week' ? 'active' : ''; ?>">This Week</a>
                <a href="?type=<?php echo $orderType; ?>&status=<?php echo $statusFilter; ?>&date=month&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter === 'month' ? 'active' : ''; ?>">This Month</a>
            </div>
        </div>
        
        <!-- Results Count -->
        <div class="flex justify-between items-center mb-3">
            <p class="text-sm text-gray-400 font-medium"><?php echo count($filteredOrders); ?> order(s)</p>
        </div>
        
        <!-- Orders List -->
        <div class="space-y-3">
            <?php if(count($filteredOrders) > 0): ?>
                <?php foreach($filteredOrders as $order): 
                    $isCustom = $order['payment_method'] === 'Custom Request';
                    $orderDate = date('M d, H:i', strtotime($order['created_at']));
                    
                    $proofPath = $order['payment_proof'] ?? '';
                    $hasProof = !empty($proofPath);
                    $proofExists = false;
                    $fullProofPath = '';
                    $proofFilename = '';
                    
                    if ($hasProof) {
                        $proofFilename = basename($proofPath);
                        $possiblePaths = [
                            '../uploads/payment/' . $proofFilename,
                            'uploads/payment/' . $proofFilename,
                            '../../uploads/payment/' . $proofFilename,
                            '../uploads/payments/' . $proofFilename,
                            'uploads/payments/' . $proofFilename
                        ];
                        
                        foreach ($possiblePaths as $path) {
                            if (file_exists($path)) {
                                $proofExists = true;
                                $fullProofPath = strpos($path, '../') === 0 ? substr($path, 3) : $path;
                                break;
                            }
                        }
                        
                        if (!$proofExists) {
                            $fullProofPath = $proofPath;
                        }
                    }
                    
                    $isPaid = ($order['payment_status'] ?? 'Pending') === 'Paid';
                    
                    if ($isCustom):
                        $notes = json_decode($order['delivery_notes'], true);
                        $requestType = $notes['request_type'] ?? 'General';
                        $badgeClass = 'badge-other';
                        if (strpos($requestType, 'Flower') !== false) $badgeClass = 'badge-flowers';
                        elseif (strpos($requestType, 'Gift') !== false) $badgeClass = 'badge-gift';
                        elseif (strpos($requestType, 'Decor') !== false) $badgeClass = 'badge-decor';
                        elseif (strpos($requestType, 'Event') !== false) $badgeClass = 'badge-event';
                ?>
                <div class="custom-card">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">#<?php echo $order['id']; ?></span>
                                <span class="text-xs text-gray-400"><?php echo $orderDate; ?></span>
                                <span class="type-indicator type-custom">Custom</span>
                            </div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['client_name']); ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                        <span class="request-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($requestType); ?></span>
                    </div>
                    <div class="mb-2"><p class="text-sm text-gray-600">📍 <?php echo htmlspecialchars(substr($order['address'], 0, 60)); ?></p></div>
                    <div class="vision-box"><p class="text-xs text-rose-500 mb-1 font-medium">Vision</p><p class="text-sm text-gray-700"><?php echo htmlspecialchars($notes['vision'] ?? 'No description'); ?></p></div>
                    <?php if($notes['is_gift'] ?? false): ?>
                        <div class="mt-3 pt-2 border-t border-rose-50">
                            <p class="text-xs text-rose-500 font-medium">Gift for: <?php echo htmlspecialchars($notes['recipient_name'] ?? 'Someone special'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php else: 
                    $statusClass = 'status-' . strtolower($order['tracking_status'] ?? 'pending');
                    $items = json_decode($order['items'], true);
                    $itemCount = count($items);
                ?>
                <div class="order-card">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">#<?php echo $order['id']; ?></span>
                                <span class="text-xs text-gray-400"><?php echo $orderDate; ?></span>
                                <span class="type-indicator type-regular">Order</span>
                            </div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['client_name']); ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $order['tracking_status'] ?? 'Pending'; ?></span>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-3 mb-3">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500"><?php echo $itemCount; ?> item(s)</span>
                            <span class="font-medium text-rose-600">TZS <?php echo number_format($order['total_amount'], 0); ?></span>
                        </div>
                        <div class="text-xs text-gray-500 space-y-1">
                            <?php foreach(array_slice($items, 0, 2) as $item): ?>
                                <div>• <?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['qty']; ?></div>
                            <?php endforeach; ?>
                            <?php if($itemCount > 2): ?>
                                <div class="text-gray-400">+<?php echo ($itemCount - 2); ?> more</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap justify-between items-center gap-2 pt-2 border-t border-rose-50">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs px-2 py-1 rounded-full <?php echo $isPaid ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?> font-medium">
                                <?php echo $isPaid ? 'Paid' : 'Pending'; ?>
                            </span>
                            <?php if($order['payment_method']): ?>
                                <span class="text-xs text-gray-400"><?php echo $order['payment_method']; ?></span>
                            <?php endif; ?>
                            
                            <?php if($hasProof): ?>
                                <button onclick="viewProof(<?php echo htmlspecialchars(json_encode(array_merge($order, ['proof_path' => $fullProofPath, 'proof_filename' => $proofFilename, 'proof_exists' => $proofExists]))); ?>)" 
                                    class="btn-ghost-sm">
                                    View Proof
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex gap-2 flex-wrap">
                            <?php if(!$isPaid && $proofExists): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Confirm payment for order #<?php echo $order['id']; ?>?')">
                                    <input type="hidden" name="action" value="confirm_payment">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="confirm-btn">Confirm Payment</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="tracking_status" onchange="this.form.submit()" class="text-xs px-3 py-1.5 rounded-full border border-rose-200 bg-white text-gray-600 focus:outline-none focus:border-rose-400">
                                    <option value="Pending" <?php echo ($order['tracking_status'] ?? 'Pending') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo ($order['tracking_status'] ?? '') == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Ready" <?php echo ($order['tracking_status'] ?? '') == 'Ready' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="Shipped" <?php echo ($order['tracking_status'] ?? '') == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="Delivered" <?php echo ($order['tracking_status'] ?? '') == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="text-5xl mb-3 text-rose-200 font-light">◌</div>
                    <p class="text-gray-500 font-medium">No orders found</p>
                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                    <a href="orders.php" class="inline-block mt-4 text-rose-500 text-sm font-medium">Clear filters →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ============================================
// PROOF MODAL - ENHANCED WITH TOGGLE
// ============================================

function viewProof(order) {
    const modal = document.getElementById('proof-modal');
    const content = document.getElementById('proof-content');
    
    let proofPath = order.proof_path || order.payment_proof || '';
    let proofFilename = order.proof_filename || '';
    let proofExists = order.proof_exists || false;
    let isPaid = order.payment_status === 'Paid';
    let isZoomed = false;
    
    let imageUrl = '';
    if (proofFilename) {
        imageUrl = 'view_proof.php?file=' + encodeURIComponent(proofFilename);
    } else if (proofPath) {
        let parts = proofPath.split('/');
        let filename = parts[parts.length - 1];
        if (filename) {
            imageUrl = 'view_proof.php?file=' + encodeURIComponent(filename);
            proofFilename = filename;
        }
    }
    
    // Build the HTML
    let html = '';
    
    // Status badge
    let statusHtml = isPaid 
        ? '<span class="proof-status proof-paid">✓ Paid</span>' 
        : '<span class="proof-status proof-pending">⏳ Pending</span>';
    
    // Image section with zoom
    let imageHtml = '';
    if (proofFilename && imageUrl) {
        imageHtml = `
            <div class="proof-container" id="proofContainer" onclick="toggleZoom()">
                <img src="${imageUrl}" alt="Payment Proof" id="proofImage" 
                     onload="onProofLoad()" 
                     onerror="onProofError()"
                     style="display:none;">
                <div id="proofLoading" style="display:flex;align-items:center;justify-content:center;min-height:200px;color:rgba(255,255,255,0.4);font-size:13px;flex-direction:column;gap:10px;">
                    <div style="width:28px;height:28px;border:2px solid rgba(255,255,255,0.1);border-top-color:#C2697E;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
                    <span>Loading image...</span>
                </div>
                <div id="proofError" style="display:none;text-align:center;padding:30px 20px;color:rgba(255,255,255,0.5);flex-direction:column;gap:8px;">
                    <div style="font-size:32px;">🖼️</div>
                    <p style="font-weight:500;">Could not load image</p>
                    <p style="font-size:11px;opacity:0.5;">File: ${escapeHtml(proofFilename)}</p>
                    <button onclick="window.open('${imageUrl}', '_blank')" style="margin-top:8px;padding:6px 18px;background:rgba(255,255,255,0.1);border:0.5px solid rgba(255,255,255,0.15);border-radius:20px;color:white;font-size:12px;cursor:pointer;">Open Directly ↗</button>
                </div>
                <button class="zoom-btn" id="zoomBtn" onclick="event.stopPropagation();toggleZoom();" title="Toggle zoom (tap image also works)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="11" y1="8" x2="11" y2="14"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </button>
                <span class="zoom-hint" id="zoomHint">Tap to zoom</span>
            </div>
            <style>
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        `;
    } else {
        imageHtml = `
            <div style="background:#F8F4F6;border-radius:16px;padding:30px;text-align:center;margin-bottom:16px;">
                <div style="font-size:32px;opacity:0.3;">📄</div>
                <p style="font-weight:500;color:#B8A0A8;">No payment proof uploaded</p>
                <p style="font-size:12px;color:#C8B8C0;">This order has no proof file</p>
            </div>
        `;
    }
    
    // Details grid
    let detailsHtml = `
        <div class="details-grid">
            <div class="item">
                <span class="label">Order ID</span>
                <span class="value rose">#${order.id}</span>
            </div>
            <div class="item">
                <span class="label">Customer</span>
                <span class="value">${escapeHtml(order.client_name)}</span>
            </div>
            <div class="item">
                <span class="label">Phone</span>
                <span class="value">${escapeHtml(order.phone)}</span>
            </div>
            <div class="item">
                <span class="label">Total Amount</span>
                <span class="value rose">TZS ${Number(order.total_amount).toLocaleString()}</span>
            </div>
            <div class="item">
                <span class="label">Payment Method</span>
                <span class="value">${escapeHtml(order.payment_method || 'N/A')}</span>
            </div>
            <div class="item">
                <span class="label">Status</span>
                <span class="value">${escapeHtml(order.tracking_status || 'Pending')}</span>
            </div>
            <div class="item" style="grid-column:1/-1;">
                <span class="label">Address</span>
                <span class="value" style="font-size:12px;">${escapeHtml(order.address || 'No address')}</span>
            </div>
            ${order.delivery_notes ? `
            <div class="item" style="grid-column:1/-1;">
                <span class="label">Notes</span>
                <span class="value" style="font-size:12px;color:#6B5A62;">${escapeHtml(order.delivery_notes)}</span>
            </div>
            ` : ''}
            <div class="item">
                <span class="label">Order Date</span>
                <span class="value" style="font-size:12px;">${new Date(order.created_at).toLocaleString()}</span>
            </div>
            <div class="item">
                <span class="label">Payment Status</span>
                <span class="value">${statusHtml}</span>
            </div>
        </div>
    `;
    
    // Action button
    let actionHtml = '';
    if (!isPaid && proofFilename) {
        actionHtml = `
            <form method="POST" onsubmit="return confirm('Confirm payment for order #${order.id}?')">
                <input type="hidden" name="action" value="confirm_payment">
                <input type="hidden" name="order_id" value="${order.id}">
                <button type="submit" class="confirm-btn">✓ Confirm Payment & Process</button>
            </form>
        `;
    } else if (isPaid) {
        actionHtml = `
            <div style="background:#D1FAE5;border-radius:12px;padding:12px;text-align:center;color:#065F46;font-weight:500;font-size:13px;">
                ✓ Payment verified and confirmed
            </div>
        `;
    }
    
    html = `
        ${imageHtml}
        ${detailsHtml}
        ${actionHtml}
        <button onclick="closeProofModal()" style="width:100%;margin-top:12px;padding:12px;border:0.5px solid rgba(194,105,126,0.15);border-radius:12px;background:transparent;color:#B8A0A8;font-weight:500;font-size:13px;cursor:pointer;transition:all 0.2s;font-family:inherit;" 
                onmouseover="this.style.background='#F8F4F6'" 
                onmouseout="this.style.background='transparent'">
            Close
        </button>
    `;
    
    content.innerHTML = html;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    
    // Store toggle function for later use
    window.toggleZoom = function() {
        const img = document.getElementById('proofImage');
        const hint = document.getElementById('zoomHint');
        const zoomBtn = document.getElementById('zoomBtn');
        if (!img) return;
        
        isZoomed = !isZoomed;
        img.classList.toggle('zoomed', isZoomed);
        
        if (hint) {
            hint.textContent = isZoomed ? 'Tap to zoom out' : 'Tap to zoom in';
            hint.classList.remove('hidden');
            setTimeout(() => hint.classList.add('hidden'), 1500);
        }
        
        if (zoomBtn) {
            zoomBtn.innerHTML = isZoomed ? 
                `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    <line x1="8" y1="11" x2="14" y2="11"/>
                </svg>` :
                `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    <line x1="11" y1="8" x2="11" y2="14"/>
                    <line x1="8" y1="11" x2="14" y2="11"/>
                </svg>`;
        }
    };
    
    window.onProofLoad = function() {
        const loading = document.getElementById('proofLoading');
        const img = document.getElementById('proofImage');
        const hint = document.getElementById('zoomHint');
        if (loading) loading.style.display = 'none';
        if (img) img.style.display = 'block';
        if (hint) {
            hint.classList.remove('hidden');
            setTimeout(() => hint.classList.add('hidden'), 3000);
        }
    };
    
    window.onProofError = function() {
        const loading = document.getElementById('proofLoading');
        const error = document.getElementById('proofError');
        if (loading) loading.style.display = 'none';
        if (error) {
            error.style.display = 'flex';
        }
    };
}

function closeProofModal() {
    document.getElementById('proof-modal').classList.remove('open');
    document.body.style.overflow = '';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

document.getElementById('proof-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProofModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProofModal();
    }
});
</script>

</body>
</html>
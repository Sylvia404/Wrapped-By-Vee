<?php
require_once '../functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Get settings
$general = getGeneralSettings();
$branding = getBrandingSettings();

// Get dashboard stats - with fallbacks
$totalRevenue = getTotalRevenue();
$totalOrders = getTotalOrders();
$pendingOrders = getPendingOrders();
$deliveredOrders = getDeliveredOrders();
$todayOrders = getTodayOrders();
$todayRevenue = getTodayRevenue();
$recentOrders = getRecentOrdersForDashboard(5);
$totalProducts = getTotalProducts();

// Finance stats - with fallbacks
$totalExpenses = getTotalExpenses();
$netProfit = getNetProfit();
$todayExpenses = getTodayExpenses();
$recentExpenses = getRecentExpenses(5);
$expensesByCategory = getExpensesByCategory();

// Get monthly data for charts
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthNum = date('n', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months"));
    
    $income = getMonthlyRevenue($monthNum, $year);
    $expense = getMonthlyExpenses($monthNum, $year);
    
    $monthlyData[] = [
        'month' => $monthName,
        'income' => floatval($income),
        'expense' => floatval($expense),
        'profit' => floatval($income - $expense)
    ];
}

// Ensure all values are numbers
$totalRevenue = is_numeric($totalRevenue) ? $totalRevenue : 0;
$totalOrders = is_numeric($totalOrders) ? $totalOrders : 0;
$pendingOrders = is_numeric($pendingOrders) ? $pendingOrders : 0;
$deliveredOrders = is_numeric($deliveredOrders) ? $deliveredOrders : 0;
$todayOrders = is_numeric($todayOrders) ? $todayOrders : 0;
$todayRevenue = is_numeric($todayRevenue) ? $todayRevenue : 0;
$totalProducts = is_numeric($totalProducts) ? $totalProducts : 0;
$totalExpenses = is_numeric($totalExpenses) ? $totalExpenses : 0;
$netProfit = is_numeric($netProfit) ? $netProfit : 0;
$todayExpenses = is_numeric($todayExpenses) ? $todayExpenses : 0;

// Calculate growth percentages
$prevMonthRevenue = getMonthlyRevenue(date('n', strtotime('-1 month')), date('Y', strtotime('-1 month')));
$revenueGrowth = $prevMonthRevenue > 0 ? round((($totalRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100, 1) : 0;

// Use settings
$siteName = $general['site_name'] ?? 'Wrapped by Vee';
$brandColor = $branding['brand_color'] ?? '#C2697E';
$brandLogo = $branding['brand_logo'] ?? '';
$brandName = $branding['brand_name'] ?? 'Wrapped by Vee';
$brandTagline = $branding['brand_tagline'] ?? 'Where flowers tell stories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Dashboard | <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Inter', sans-serif; background: #F8F4F6; padding-bottom: 80px; }
        * { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Bottom Navigation */
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

        /* Desktop Sidebar */
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

        .dashboard-icon {
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

        /* Premium Stat Cards */
        .stat-card {
            background: white; border-radius: 16px; padding: 16px 18px;
            border: 0.5px solid rgba(194,105,126,0.06);
            box-shadow: 0 2px 12px rgba(194,105,126,0.04);
            position: relative;
            overflow: hidden;
        }
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
        .stat-card .stat-value.purple { color: #7C3AED; }
        .stat-card .stat-change {
            font-size: 10px; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;
            font-weight: 500;
        }
        .stat-card .stat-change.up { color: #059669; }
        .stat-card .stat-change.down { color: #DC2626; }

        /* Chart Containers - Professional */
        .chart-container {
            background: white; border-radius: 16px; padding: 20px;
            border: 0.5px solid rgba(194,105,126,0.06);
            box-shadow: 0 2px 12px rgba(194,105,126,0.04);
        }
        .chart-container .chart-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;
        }
        .chart-container .chart-header .title {
            font-size: 14px; font-weight: 600; color: #3B2229;
        }
        .chart-container .chart-header .subtitle {
            font-size: 11px; color: #B8A0A8; font-weight: 400;
        }
        .chart-container .chart-wrapper {
            position: relative;
            height: 200px;
        }
        @media (min-width: 768px) {
            .chart-container .chart-wrapper {
                height: 220px;
            }
        }
        .chart-container .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .badge {
            padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 500;
        }
        .badge-pending { background: #FEF3C7; color: #D97706; }
        .badge-delivered { background: #D1FAE5; color: #059669; }
        .badge-processing { background: #DBEAFE; color: #2563EB; }

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

        .transaction-item {
            background: white; border-radius: 12px; padding: 10px 14px;
            border: 0.5px solid rgba(194,105,126,0.06);
            margin-bottom: 6px;
            display: flex; justify-content: space-between; align-items: center;
            transition: all 0.2s;
        }
        .transaction-item:active { transform: scale(0.98); }

        @media (max-width: 480px) {
            .stat-card .stat-value { font-size: 18px; }
            .dashboard-icon { width: 40px; height: 40px; font-size: 18px; }
            .stat-card { padding: 12px 14px; }
            .chart-container { padding: 14px; }
            .chart-container .chart-wrapper { height: 160px; }
        }
        
        @media (min-width: 768px) and (max-width: 1024px) {
            .chart-container .chart-wrapper { height: 180px; }
        }
    </style>
</head>
<body>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item active"><span class="nav-icon">✦</span><span>Home</span></a>
    <a href="orders.php" class="nav-item"><span class="nav-icon">◌</span><span>Orders</span></a>
    <a href="products.php" class="nav-item"><span class="nav-icon">◍</span><span>Studio</span></a>
    <a href="finance.php" class="nav-item"><span class="nav-icon">◊</span><span>Finance</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">◎</span><span>Settings</span></a>
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
        <a href="index.php" class="active"><span class="nav-dot"></span>Dashboard</a>
        <a href="orders.php"><span class="nav-dot"></span>Orders</a>
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
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <div class="dashboard-icon">✦</div>
            <div>
                <h1 class="text-2xl font-bold text-rose-700 tracking-tight">Dashboard</h1>
                <p class="text-sm text-gray-400 mt-0.5">Welcome back! Here's what's happening today.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <p class="stat-label">Total Revenue</p>
                <p class="stat-value rose">TZS <?php echo number_format(floatval($totalRevenue), 0); ?></p>
                <span class="stat-change <?php echo $revenueGrowth >= 0 ? 'up' : 'down'; ?>">
                    <?php echo $revenueGrowth >= 0 ? '↑' : '↓'; ?> <?php echo abs($revenueGrowth); ?>%
                </span>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Orders</p>
                <p class="stat-value"><?php echo intval($totalOrders); ?></p>
                <span class="stat-change up">↑ <?php echo intval($todayOrders); ?> today</span>
            </div>
            <div class="stat-card">
                <p class="stat-label">Pending</p>
                <p class="stat-value amber"><?php echo intval($pendingOrders); ?></p>
                <span class="stat-change down">Awaiting action</span>
            </div>
            <div class="stat-card">
                <p class="stat-label">Net Profit</p>
                <p class="stat-value green">TZS <?php echo number_format(floatval($netProfit), 0); ?></p>
                <span class="stat-change <?php echo $netProfit >= 0 ? 'up' : 'down'; ?>">
                    <?php echo $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0; ?>% margin
                </span>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Income vs Expenses Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <div>
                        <div class="title">Income vs Expenses</div>
                        <div class="subtitle">Last 6 months</div>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#6B5A62;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10B981;"></span> Income
                        </span>
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;color:#6B5A62;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#C2697E;"></span> Expenses
                        </span>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>

            <!-- Profit Trend Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <div>
                        <div class="title">Profit Trend</div>
                        <div class="subtitle">Monthly net profit</div>
                    </div>
                    <span style="font-size:10px;color:#B8A0A8;font-weight:500;">↗ Growth</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Expense Categories -->
        <?php if (is_array($expensesByCategory) && count($expensesByCategory) > 0): ?>
        <div class="chart-container mb-6">
            <div class="chart-header">
                <div>
                    <div class="title">Expense Categories</div>
                    <div class="subtitle">Where your money goes</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <?php 
                $totalExpenseAmount = array_sum(array_column($expensesByCategory, 'total'));
                $colors = ['#C2697E', '#D98E9F', '#F59E0B', '#10B981', '#8B5CF6', '#6B7280'];
                $i = 0;
                foreach ($expensesByCategory as $cat):
                    $percentage = $totalExpenseAmount > 0 ? round(($cat['total'] / $totalExpenseAmount) * 100, 1) : 0;
                    $color = $colors[$i % count($colors)];
                    $i++;
                ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span style="color:#6B5A62;font-weight:500;"><?php echo htmlspecialchars($cat['category']); ?></span>
                        <span style="font-weight:600;color:#3B2229;"><?php echo $percentage; ?>%</span>
                    </div>
                    <div style="height:6px;background:#F5EBED;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo $percentage; ?>%;background:<?php echo $color; ?>;border-radius:4px;transition:width 1s ease;"></div>
                    </div>
                    <span style="font-size:10px;color:#B8A0A8;margin-top:2px;display:block;">TZS <?php echo number_format(floatval($cat['total']), 0); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-rose-100">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-gray-700 text-sm">Recent Orders</h2>
                    <a href="orders.php" class="text-xs text-rose-500 hover:text-rose-600 font-medium">View All →</a>
                </div>
                <?php if (is_array($recentOrders) && count($recentOrders) > 0): ?>
                    <div class="space-y-2">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="transaction-item">
                                <div>
                                    <p class="font-medium text-sm">#<?php echo $order['id']; ?> - <?php echo htmlspecialchars($order['client_name']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo date('M d, H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-rose-600">TZS <?php echo number_format(floatval($order['total_amount']), 0); ?></span>
                                    <?php
                                    $status = $order['tracking_status'] ?? 'Pending';
                                    $badgeClass = $status == 'Delivered' ? 'badge-delivered' : ($status == 'Processing' ? 'badge-processing' : 'badge-pending');
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400 text-sm text-center py-4">No recent orders</p>
                <?php endif; ?>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-rose-100">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-gray-700 text-sm">Recent Expenses</h2>
                    <a href="finance.php" class="text-xs text-rose-500 hover:text-rose-600 font-medium">View All →</a>
                </div>
                <?php if (is_array($recentExpenses) && count($recentExpenses) > 0): ?>
                    <div class="space-y-2">
                        <?php foreach ($recentExpenses as $expense): ?>
                            <div class="transaction-item">
                                <div>
                                    <p class="font-medium text-sm"><?php echo htmlspecialchars($expense['description']); ?></p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo htmlspecialchars($expense['category'] ?? 'Other'); ?> • 
                                        <?php echo date('M d, H:i', strtotime($expense['created_at'])); ?>
                                    </p>
                                </div>
                                <span class="text-sm font-medium text-amber-600">TZS <?php echo number_format(floatval($expense['amount']), 0); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400 text-sm text-center py-4">No expenses recorded</p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<script>
// ============================================
// UNIQUE & PROFESSIONAL CHARTS
// ============================================

// Colors
const roseColor = '#C2697E';
const roseLight = 'rgba(194, 105, 126, 0.15)';
const greenColor = '#10B981';
const greenLight = 'rgba(16, 185, 129, 0.15)';
const purpleColor = '#8B5CF6';
const amberColor = '#F59E0B';

// 1. Income vs Expenses - Stacked Area Chart (Unique)
const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyData, 'month')); ?>,
        datasets: [
            {
                label: 'Income',
                data: <?php echo json_encode(array_column($monthlyData, 'income')); ?>,
                borderColor: greenColor,
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
                    return gradient;
                },
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: greenColor,
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            },
            {
                label: 'Expenses',
                data: <?php echo json_encode(array_column($monthlyData, 'expense')); ?>,
                borderColor: roseColor,
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, 'rgba(194, 105, 126, 0.4)');
                    gradient.addColorStop(1, 'rgba(194, 105, 126, 0.02)');
                    return gradient;
                },
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: roseColor,
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(255,255,255,0.95)',
                titleColor: '#1A0A12',
                bodyColor: '#6B5A62',
                borderColor: 'rgba(194,105,126,0.1)',
                borderWidth: 1,
                cornerRadius: 12,
                padding: 12,
                boxShadow: '0 4px 20px rgba(0,0,0,0.06)',
                callbacks: {
                    label: function(ctx) {
                        return ctx.dataset.label + ': TZS ' + ctx.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(194,105,126,0.05)', drawBorder: false },
                ticks: {
                    callback: function(v) {
                        if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                        if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                        return v;
                    },
                    font: { size: 10, family: 'Inter' },
                    color: '#B8A0A8'
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    font: { size: 10, family: 'Inter', weight: '500' },
                    color: '#6B5A62'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        animation: {
            duration: 1200,
            easing: 'easeInOutQuart'
        }
    }
});

// 2. Profit Trend - Beautiful Doughnut + Line Hybrid
const ctx2 = document.getElementById('profitChart').getContext('2d');

// Create gradient for line
const gradient2 = ctx2.createLinearGradient(0, 0, 0, 200);
gradient2.addColorStop(0, 'rgba(194, 105, 126, 0.3)');
gradient2.addColorStop(0.5, 'rgba(194, 105, 126, 0.08)');
gradient2.addColorStop(1, 'rgba(194, 105, 126, 0)');

new Chart(ctx2, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyData, 'month')); ?>,
        datasets: [{
            label: 'Net Profit',
            data: <?php echo json_encode(array_column($monthlyData, 'profit')); ?>,
            borderColor: roseColor,
            backgroundColor: gradient2,
            borderWidth: 4,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: (context) => {
                const value = context.raw;
                if (value >= 0) return greenColor;
                return '#DC2626';
            },
            pointBorderColor: 'white',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 10,
            pointHoverBorderColor: 'white',
            pointHoverBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(255,255,255,0.95)',
                titleColor: '#1A0A12',
                bodyColor: '#6B5A62',
                borderColor: 'rgba(194,105,126,0.1)',
                borderWidth: 1,
                cornerRadius: 12,
                padding: 12,
                callbacks: {
                    label: function(ctx) {
                        const sign = ctx.raw >= 0 ? '+' : '';
                        return 'Profit: ' + sign + 'TZS ' + ctx.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(194,105,126,0.05)', drawBorder: false },
                ticks: {
                    callback: function(v) {
                        if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                        if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                        return v;
                    },
                    font: { size: 10, family: 'Inter' },
                    color: '#B8A0A8'
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    font: { size: 10, family: 'Inter', weight: '500' },
                    color: '#6B5A62'
                }
            }
        },
        animation: {
            duration: 1200,
            easing: 'easeInOutQuart'
        }
    }
});
</script>
</body>
</html>
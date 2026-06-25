<?php
require_once '../functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Handle income actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_income') {
            addIncome($_POST['income_name'], $_POST['income_amount'], $_POST['income_category'], $_POST['income_source'], $_POST['income_note'] ?? '');
            header('Location: finance.php?success=income_added');
            exit;
        } elseif ($_POST['action'] === 'delete_income') {
            deleteIncome($_POST['income_id']);
            header('Location: finance.php');
            exit;
        } elseif ($_POST['action'] === 'add_expense') {
            addExpense([
                'description' => $_POST['expense_name'],
                'amount' => $_POST['expense_amount'],
                'category' => $_POST['expense_category'] ?? 'Other'
            ]);
            header('Location: finance.php?success=expense_added');
            exit;
        } elseif ($_POST['action'] === 'delete_expense') {
            deleteExpense($_POST['expense_id']);
            header('Location: finance.php');
            exit;
        }
    }
}

$orders = getAllOrders();
$expenses = getExpenses();
$incomeRecords = getIncomeRecords();

// Calculate totals
$totalRevenue = getTotalRevenue();
$totalManualIncome = getTotalIncome();
$totalIncome = $totalRevenue + $totalManualIncome;
$totalExpenses = getTotalExpenses();
$netProfit = $totalIncome - $totalExpenses;
$profitMargin = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : 0;

// Monthly data for charts
$months = [];
$revenueData = [];
$manualIncomeData = [];
$expenseData = [];
$profitData = [];

for ($i = 5; $i >= 0; $i--) {
    $monthNum = date('n', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months"));
    $months[] = $monthName;
    
    $revenueData[] = getMonthlyRevenue($monthNum, $year);
    $manualIncomeData[] = getMonthlyIncome($monthNum, $year);
    $expenseData[] = getMonthlyExpenses($monthNum, $year);
    $monthlyIncome = getMonthlyRevenue($monthNum, $year) + getMonthlyIncome($monthNum, $year);
    $profitData[] = $monthlyIncome - getMonthlyExpenses($monthNum, $year);
}

// Category breakdowns
$expenseCategoryTotals = [];
if (is_array($expenses) && count($expenses) > 0) {
    foreach($expenses as $expense) {
        $cat = $expense['category'] ?? 'Other';
        if (!isset($expenseCategoryTotals[$cat])) {
            $expenseCategoryTotals[$cat] = 0;
        }
        $expenseCategoryTotals[$cat] += $expense['amount'];
    }
}

// Recent transactions
$recentTransactions = [];
if (is_array($orders) && count($orders) > 0) {
    foreach($orders as $order) {
        if ($order['payment_method'] !== 'Custom Request') {
            $recentTransactions[] = [
                'type' => 'order',
                'id' => $order['id'],
                'name' => $order['client_name'],
                'amount' => $order['total_amount'],
                'status' => $order['payment_status'] ?? 'Pending',
                'date' => $order['created_at'],
                'is_income' => true
            ];
        }
    }
}

if (is_array($incomeRecords) && count($incomeRecords) > 0) {
    foreach($incomeRecords as $income) {
        $recentTransactions[] = [
            'type' => 'manual_income',
            'id' => $income['id'],
            'name' => $income['name'],
            'amount' => $income['amount'],
            'source' => $income['source'] ?? 'Cash',
            'date' => $income['created_at'],
            'is_income' => true
        ];
    }
}

if (is_array($expenses) && count($expenses) > 0) {
    foreach($expenses as $expense) {
        $recentTransactions[] = [
            'type' => 'expense',
            'id' => $expense['id'],
            'name' => $expense['description'] ?? $expense['name'] ?? 'Expense',
            'amount' => $expense['amount'],
            'category' => $expense['category'] ?? 'Other',
            'date' => $expense['created_at'],
            'is_income' => false
        ];
    }
}

usort($recentTransactions, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recentTransactions = array_slice($recentTransactions, 0, 15);

$successMessage = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'income_added') $successMessage = 'Income added successfully!';
    if ($_GET['success'] === 'expense_added') $successMessage = 'Expense added successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Finance | Wrapped by Vee</title>
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

        .finance-icon {
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
        .stat-card .stat-value.green { color: #059669; }
        .stat-card .stat-value.rose { color: #C2697E; }
        .stat-card .stat-value.amber { color: #D97706; }
        .stat-card .stat-value.purple { color: #7C3AED; }
        .stat-card .stat-value.red { color: #DC2626; }

        .progress-bar {
            height: 4px; background: #EDE4E8; border-radius: 10px; overflow: hidden; margin-top: 6px;
        }
        .progress-fill {
            height: 100%; border-radius: 10px; transition: width 0.8s ease;
        }

        .transaction-item {
            background: white; border-radius: 12px; padding: 12px 14px;
            border: 0.5px solid rgba(194,105,126,0.06);
            margin-bottom: 6px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .transaction-item:active { transform: scale(0.99); }

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

        .category-badge {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 500;
        }
        .cat-supplies { background: #FFE0EC; color: #C2697E; }
        .cat-rent { background: #E8E6F0; color: #6B4C8A; }
        .cat-utilities { background: #E6F0E8; color: #4A8A6B; }
        .cat-marketing { background: #FEF3C7; color: #D97706; }
        .cat-salaries { background: #DBEAFE; color: #2563EB; }
        .cat-other { background: #E5E7EB; color: #6B7280; }

        .empty-state {
            text-align: center; padding: 40px 20px; background: white;
            border-radius: 16px; border: 0.5px solid rgba(194,105,126,0.06);
        }

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

        .action-btn {
            padding: 10px 20px; border-radius: 40px; font-weight: 600;
            font-size: 13px; border: none; cursor: pointer; transition: all 0.2s ease;
            flex: 1; text-align: center;
        }
        .action-btn:active { transform: scale(0.96); }

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

        .btn-green {
            background: #10B981; color: white; border: none;
            padding: 12px 20px; border-radius: 12px; font-size: 13px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-green:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(16,185,129,0.25); }
        .btn-green:active { transform: scale(0.97); }

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
            max-width: 500px; width: 100%; max-height: 92vh;
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

        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #EDE4E8; border-radius: 12px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            background: white; transition: all 0.2s; outline: none;
        }
        .form-control:focus { border-color: #C2697E; box-shadow: 0 0 0 3px rgba(194,105,126,0.06); }

        @media (max-width: 480px) {
            .stat-card .stat-value { font-size: 18px; }
            .finance-icon { width: 40px; height: 40px; font-size: 18px; }
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
    <a href="index.php" class="nav-item"><span class="nav-icon">✦</span><span>Home</span></a>
    <a href="orders.php" class="nav-item"><span class="nav-icon">◌</span><span>Orders</span></a>
    <a href="products.php" class="nav-item"><span class="nav-icon">◍</span><span>Studio</span></a>
    <a href="finance.php" class="nav-item active"><span class="nav-icon">◊</span><span>Finance</span></a>
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
        <a href="index.php"><span class="nav-dot"></span>Dashboard</a>
        <a href="orders.php"><span class="nav-dot"></span>Orders</a>
        <a href="products.php"><span class="nav-dot"></span>Studio</a>
        <a href="finance.php" class="active"><span class="nav-dot"></span>Finance</a>
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
            <div class="finance-icon">◊</div>
            <div>
                <h1 class="text-2xl font-bold text-rose-700 tracking-tight">Financial Analytics</h1>
                <p class="text-sm text-gray-400 mt-0.5">Track income, expenses & profitability</p>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div style="background:#D1FAE5;border-radius:12px;padding:12px 16px;margin-bottom:16px;color:#065F46;font-weight:500;font-size:13px;border:0.5px solid #A7F3D0;">
                ✓ <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="flex gap-3 mb-6">
            <button onclick="openAddIncomeModal()" class="action-btn btn-green shadow-sm">+ Add Income</button>
            <button onclick="openAddExpenseModal()" class="action-btn btn-primary shadow-sm">+ Add Expense</button>
        </div>
        
        <!-- Key Metrics -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-label">Total Income</div>
                <div class="stat-value green">TZS <?php echo number_format(floatval($totalIncome), 0); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Expenses</div>
                <div class="stat-value red">TZS <?php echo number_format(floatval($totalExpenses), 0); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Net Profit</div>
                <div class="stat-value <?php echo $netProfit >= 0 ? 'green' : 'red'; ?>">
                    TZS <?php echo number_format(floatval($netProfit), 0); ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Profit Margin</div>
                <div class="stat-value purple"><?php echo floatval($profitMargin); ?>%</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min($profitMargin, 100); ?>%; background: linear-gradient(90deg, #C2697E, #D98E9F);"></div>
                </div>
            </div>
        </div>
        
        <!-- Income Breakdown -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="stat-card text-center">
                <div class="stat-label">From Orders</div>
                <div class="stat-value green" style="font-size:20px;">TZS <?php echo number_format(floatval($totalRevenue), 0); ?></div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-label">Manual Income</div>
                <div class="stat-value purple" style="font-size:20px;">TZS <?php echo number_format(floatval($totalManualIncome), 0); ?></div>
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
                    <canvas id="profitTrendChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Expense Categories -->
        <?php if(is_array($expenseCategoryTotals) && count($expenseCategoryTotals) > 0): ?>
        <div class="chart-container mb-6">
            <div class="chart-header">
                <div>
                    <div class="title">Expense Categories</div>
                    <div class="subtitle">Where your money goes</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <?php 
                $totalExpenseAmount = array_sum($expenseCategoryTotals);
                $colors = ['#C2697E', '#D98E9F', '#F59E0B', '#10B981', '#8B5CF6', '#6B7280'];
                $i = 0;
                foreach($expenseCategoryTotals as $cat => $amount): 
                    $percentage = $totalExpenseAmount > 0 ? round(($amount / $totalExpenseAmount) * 100, 1) : 0;
                    $catClass = 'cat-other';
                    if ($cat == 'Supplies') $catClass = 'cat-supplies';
                    elseif ($cat == 'Rent') $catClass = 'cat-rent';
                    elseif ($cat == 'Utilities') $catClass = 'cat-utilities';
                    elseif ($cat == 'Marketing') $catClass = 'cat-marketing';
                    elseif ($cat == 'Salaries') $catClass = 'cat-salaries';
                    $color = $colors[$i % count($colors)];
                    $i++;
                ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span style="color:#6B5A62;font-weight:500;"><?php echo htmlspecialchars($cat); ?></span>
                        <span style="font-weight:600;color:#3B2229;"><?php echo $percentage; ?>%</span>
                    </div>
                    <div style="height:6px;background:#F5EBED;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo $percentage; ?>%;background:<?php echo $color; ?>;border-radius:4px;transition:width 1s ease;"></div>
                    </div>
                    <span style="font-size:10px;color:#B8A0A8;margin-top:2px;display:block;">TZS <?php echo number_format(floatval($amount), 0); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- All Transactions -->
        <div class="mb-5">
            <div class="flex justify-between items-center mb-3">
                <p class="font-semibold text-gray-800 text-sm">All Transactions</p>
            </div>
            <div class="space-y-2">
                <?php if(count($recentTransactions) > 0): ?>
                    <?php foreach($recentTransactions as $tx): ?>
                        <div class="transaction-item">
                            <div>
                                <p style="font-size:13px;font-weight:500;color:#3B2229;"><?php echo htmlspecialchars($tx['name']); ?></p>
                                <p style="font-size:10px;color:#B8A0A8;margin-top:1px;">
                                    <?php 
                                        if ($tx['type'] == 'order') echo 'Order #' . $tx['id'];
                                        elseif ($tx['type'] == 'manual_income') echo 'Manual • ' . htmlspecialchars($tx['source']);
                                        else echo 'Expense • ' . htmlspecialchars($tx['category']);
                                    ?> • <?php echo date('M d, Y', strtotime($tx['date'])); ?>
                                </p>
                            </div>
                            <div style="text-align:right;">
                                <p style="font-size:14px;font-weight:600;color:<?php echo $tx['is_income'] ? '#059669' : '#D97706'; ?>;">
                                    <?php echo $tx['is_income'] ? '+' : '-'; ?>
                                    TZS <?php echo number_format(abs(floatval($tx['amount'])), 0); ?>
                                </p>
                                <?php if($tx['type'] == 'order'): ?>
                                    <p style="font-size:9px;color:<?php echo $tx['status'] == 'Paid' ? '#059669' : '#D97706'; ?>;">
                                        <?php echo $tx['status'] == 'Paid' ? 'Completed' : 'Pending'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size:28px;margin-bottom:6px;">◊</div>
                        <p style="color:#B8A0A8;font-size:13px;">No transactions yet</p>
                        <button onclick="openAddIncomeModal()" style="color:#C2697E;font-size:13px;margin-top:8px;background:none;border:none;cursor:pointer;font-weight:500;">
                            Add your first transaction →
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div id="income-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg text-stone-800">Add Income</h2>
            <button onclick="closeAddIncomeModal()" class="text-gray-400 text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">&times;</button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_income">
            
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Income Name *</label>
                <input type="text" name="income_name" class="form-control" placeholder="e.g., Consultation Fee" required>
            </div>
            
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Amount (TZS) *</label>
                <input type="number" name="income_amount" class="form-control" placeholder="e.g., 25000" required>
            </div>
            
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Category</label>
                <select name="income_category" class="form-control">
                    <option value="Order">Order Revenue</option>
                    <option value="Consultation">Consultation Fee</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Donation">Donation</option>
                    <option value="Other">Other Income</option>
                </select>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Source</label>
                <select name="income_source" class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="M-Pesa">M-Pesa</option>
                    <option value="Tigo Pesa">Tigo Pesa</option>
                    <option value="Airtel Money">Airtel Money</option>
                </select>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Note (optional)</label>
                <textarea name="income_note" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="btn-green flex-1">Add Income</button>
                <button type="button" class="btn-secondary flex-1" onclick="closeAddIncomeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Expense Modal -->
<div id="expense-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg text-stone-800">Add Expense</h2>
            <button onclick="closeAddExpenseModal()" class="text-gray-400 text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">&times;</button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_expense">
            
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Expense Name *</label>
                <input type="text" name="expense_name" class="form-control" placeholder="e.g., Flower supplies" required>
            </div>
            
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Amount (TZS) *</label>
                <input type="number" name="expense_amount" class="form-control" placeholder="e.g., 15000" required>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Category</label>
                <select name="expense_category" class="form-control">
                    <option value="Supplies">Supplies</option>
                    <option value="Rent">Rent</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Salaries">Salaries</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:600;color:#6B5A62;display:block;margin-bottom:4px;">Note (optional)</label>
                <textarea name="expense_note" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">Add Expense</button>
                <button type="button" class="btn-secondary flex-1" onclick="closeAddExpenseModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddIncomeModal() {
    document.getElementById('income-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeAddIncomeModal() {
    document.getElementById('income-modal').classList.remove('open');
    document.body.style.overflow = '';
}

function openAddExpenseModal() {
    document.getElementById('expense-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeAddExpenseModal() {
    document.getElementById('expense-modal').classList.remove('open');
    document.body.style.overflow = '';
}

// ============================================
// UNIQUE & PROFESSIONAL CHARTS
// ============================================

// Colors
const roseColor = '#C2697E';
const roseLight = 'rgba(194, 105, 126, 0.15)';
const greenColor = '#10B981';
const greenLight = 'rgba(16, 185, 129, 0.15)';

// 1. Income vs Expenses - Stacked Area Chart (Unique)
const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
            {
                label: 'Income',
                data: <?php echo json_encode(array_map(function($a, $b) { return floatval($a + $b); }, $revenueData, $manualIncomeData)); ?>,
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
                data: <?php echo json_encode($expenseData); ?>,
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

// 2. Profit Trend - Enhanced Line Chart
const ctx2 = document.getElementById('profitTrendChart').getContext('2d');

// Create gradient for line
const gradient2 = ctx2.createLinearGradient(0, 0, 0, 200);
gradient2.addColorStop(0, 'rgba(194, 105, 126, 0.3)');
gradient2.addColorStop(0.5, 'rgba(194, 105, 126, 0.08)');
gradient2.addColorStop(1, 'rgba(194, 105, 126, 0)');

new Chart(ctx2, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Net Profit',
            data: <?php echo json_encode($profitData); ?>,
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

// Close modals on outside click
document.getElementById('income-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAddIncomeModal();
});
document.getElementById('expense-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAddExpenseModal();
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddIncomeModal();
        closeAddExpenseModal();
    }
});
</script>
</body>
</html>
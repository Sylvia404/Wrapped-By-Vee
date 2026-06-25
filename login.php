<?php
// login.php
require_once 'db.php';
require_once 'functions.php';

session_start();

// If already logged in, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

$error = '';
$settings = getGeneralSettings();
$siteName = $settings['site_name'] ?? 'Wrapped by Vee';
$branding = getBrandingSettings();
$brandColor = $branding['brand_color'] ?? '#C2697E';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $admin = verifyAdminLogin($username, $password);
        
        if ($admin) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'] ?? 'Admin';
            
            header('Location: admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #FFF8F9 0%, #FFF0F5 50%, #FFE8EE 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container { max-width: 400px; width: 100%; }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #f0e8ea;
        }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo h1 { font-size: 24px; color: <?php echo $brandColor; ?>; }
        .logo p { color: #999; font-size: 14px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #f0e8ea;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-group input:focus {
            border-color: <?php echo $brandColor; ?>;
            box-shadow: 0 0 0 3px rgba(194,105,126,0.1);
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: <?php echo $brandColor; ?>;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: <?php echo $brandColor; ?>dd;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(194,105,126,0.35);
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover { color: <?php echo $brandColor; ?>; }
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: 8px;
            color: <?php echo $brandColor; ?>;
            text-decoration: none;
            font-size: 13px;
        }
        .forgot-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>✦ <?php echo htmlspecialchars($siteName); ?></h1>
                <p>Admin Login</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error">● <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <a href="reset-password.php" class="forgot-link">Forgot password?</a>
                <button type="submit" class="btn" style="margin-top:16px;">Sign In</button>
            </form>
            
            <a href="index.php" class="back-link">← Back to Website</a>
        </div>
    </div>
</body>
</html>
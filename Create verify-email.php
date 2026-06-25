<?php
require_once 'db.php';
require_once 'functions.php';

$message = '';
$error = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $result = verifyEmailChange($token);
    
    if ($result['success']) {
        $message = $result['message'];
        $step = 'success';
    } else {
        $error = $result['error'];
        $step = 'error';
    }
} else {
    $error = 'No verification token provided';
    $step = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Verify Email | Wrapped by Vee</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #FAF5F7;
        }
        .wrapper { width: 100%; max-width: 360px; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 0 4px 24px rgba(194,105,126,0.06);
            border: 1px solid rgba(255,224,236,0.3);
            text-align: center;
        }
        .icon { font-size: 48px; margin-bottom: 12px; }
        .title { font-size: 20px; font-weight: 600; color: #2A1520; margin-bottom: 4px; }
        .sub { font-size: 14px; color: #B8909A; margin-bottom: 16px; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #C2697E, #D98E9F);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
        }
        .error { color: #DC2626; }
        .success { color: #065F46; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <?php if ($step === 'success'): ?>
                <div class="icon">✓</div>
                <div class="title success">Email Verified!</div>
                <div class="sub"><?php echo $message; ?></div>
                <a href="login.php" class="btn">Go to Login</a>
            <?php else: ?>
                <div class="icon">✗</div>
                <div class="title error">Verification Failed</div>
                <div class="sub"><?php echo $error; ?></div>
                <a href="settings.php" class="btn">Back to Settings</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
// reset_password.php - Complete working version
require_once 'db.php';
require_once 'functions.php';

// Start session
startSecureSession();

$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$message = '';
$error = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle request reset
    if (isset($_POST['action']) && $_POST['action'] === 'request_reset') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email exists in admin_users
            $admin = getAdminByEmail($email);
            
            if ($admin) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                
                // Store in password_resets table
                if (createPasswordReset($email, $token)) {
                    // Send email with reset link
                    sendPasswordResetEmail($email, $token);
                    
                    $message = "Password reset instructions have been sent to your email address.";
                    $step = 'sent';
                } else {
                    $error = 'Failed to create reset request. Please try again.';
                }
            } else {
                // Don't reveal that email doesn't exist for security
                $message = "If an account exists with this email, you will receive a password reset link.";
                $step = 'sent';
            }
        }
    }
    
    // Handle password reset
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($token)) {
            $error = 'Invalid reset token.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            if (completePasswordReset($token, $password)) {
                $message = 'Password reset successfully! You can now login with your new password.';
                $step = 'success';
            } else {
                $error = 'Invalid or expired reset link. Please request a new one.';
                $step = 'request';
            }
        }
    }
}

// Check for token in URL
if ($token && $step !== 'sent' && $step !== 'success') {
    $reset = verifyPasswordResetToken($token);
    if ($reset) {
        $step = 'reset';
    } else {
        $error = 'Invalid or expired reset link. Please request a new one.';
        $step = 'request';
    }
}

// Get site settings for branding
$settings = getGeneralSettings();
$siteName = $settings['site_name'] ?? 'Wrapped by Vee';
$branding = getBrandingSettings();
$brandColor = $branding['brand_color'] ?? '#C2697E';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo htmlspecialchars($siteName); ?></title>
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
        .container { max-width: 420px; width: 100%; }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #f0e8ea;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo h1 {
            font-size: 22px;
            color: <?php echo $brandColor; ?>;
        }
        .logo p {
            color: #999;
            font-size: 14px;
        }
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
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #a7f3d0;
        }
        .back-link {
            display: inline-block;
            margin-top: 16px;
            color: #999;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: <?php echo $brandColor; ?>;
        }
        .text-center { text-align: center; }
        .success-icon {
            width: 64px;
            height: 64px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>✦ <?php echo htmlspecialchars($siteName); ?></h1>
                <p>Password Reset</p>
            </div>

            <?php if ($step === 'request'): ?>
                <h2 style="margin-bottom:8px;font-size:18px;">Reset Password</h2>
                <p style="color:#666;font-size:14px;margin-bottom:20px;">Enter your email address and we'll send you a link to reset your password.</p>
                
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="request_reset">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="admin@<?php echo strtolower(str_replace(' ', '', $siteName)); ?>.com">
                    </div>
                    <button type="submit" class="btn">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <?php if ($step === 'sent'): ?>
                <div class="text-center">
                    <div class="success-icon">✓</div>
                    <h2 style="font-size:18px;margin-bottom:8px;">Check Your Email</h2>
                    <p style="color:#666;font-size:14px;"><?php echo htmlspecialchars($message); ?></p>
                    <p style="color:#999;font-size:12px;margin-top:8px;">If you don't see the email, check your spam folder.</p>
                    <a href="login.php" class="back-link">← Back to Login</a>
                </div>
            <?php endif; ?>

            <?php if ($step === 'reset'): ?>
                <h2 style="margin-bottom:8px;font-size:18px;">Create New Password</h2>
                <p style="color:#666;font-size:14px;margin-bottom:20px;">Enter your new password below.</p>
                
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="8" placeholder="Confirm your password">
                    </div>
                    <button type="submit" class="btn">Reset Password</button>
                </form>
            <?php endif; ?>

            <?php if ($step === 'success'): ?>
                <div class="text-center">
                    <div class="success-icon">✓</div>
                    <h2 style="font-size:18px;margin-bottom:8px;">Password Reset Complete!</h2>
                    <p style="color:#666;font-size:14px;"><?php echo htmlspecialchars($message); ?></p>
                    <a href="login.php" class="btn" style="display:inline-block;text-decoration:none;text-align:center;margin-top:16px;">Login Now</a>
                </div>
            <?php endif; ?>

            <?php if ($step !== 'success'): ?>
                <div class="text-center" style="margin-top:16px;">
                    <a href="login.php" class="back-link">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
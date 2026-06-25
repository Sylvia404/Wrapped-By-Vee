<?php
require_once 'db.php';
require_once 'functions.php';

$step = $_GET['step'] ?? 'request';
$message = '';
$error = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'request_reset') {
        $email = trim($_POST['email'] ?? '');
        
        // Check if email exists in admin_users (admin only)
        $admin = getAdminByEmail($email);
        
        if ($admin) {
            // Generate secure token
            $token = bin2hex(random_bytes(32));
            
            // Store in password_resets table
            createPasswordReset($email, $token);
            
            // Send email with reset link
            $resetLink = sendAdminResetEmail($email, $token);
            
            // For demo, show success message (in production, don't show the link)
            $message = "If an account exists with this email, you will receive a password reset link.";
            
            // In production, uncomment this and remove the link display
            // $message = "Password reset instructions have been sent to your email address.";
        } else {
            // Don't reveal that email doesn't exist for security
            $message = "If an account exists with this email, you will receive a password reset link.";
        }
        $step = 'sent';
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirm) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            // Verify token is valid
            $reset = verifyPasswordResetToken($token);
            
            if ($reset) {
                if (completePasswordReset($token, $password)) {
                    $message = 'Password reset successfully! You can now login with your new password.';
                    $step = 'success';
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
            } else {
                $error = 'Invalid or expired reset link. Please request a new one.';
            }
        }
    }
}

$token = $_GET['token'] ?? '';
if ($token && $step !== 'sent' && $step !== 'success') {
    $reset = verifyPasswordResetToken($token);
    if ($reset) {
        $step = 'reset';
    } else {
        $error = 'Invalid or expired reset link. Please request a new one.';
        $step = 'request';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reset Password | Wrapped by Vee</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #FFF8F9 0%, #FFF0F5 50%, #FFE8EE 100%); min-height: 100vh; }
        .input-field { transition: all 0.3s ease; border: 2px solid #FFE0EC; }
        .input-field:focus { border-color: #C2697E; box-shadow: 0 0 0 3px rgba(194,105,126,0.1); transform: scale(1.01); }
        .reset-card { animation: slideUp 0.6s ease forwards; opacity: 0; transform: translateY(30px); }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
        .back-link { transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px; }
        .back-link:hover { color: #C2697E; transform: translateX(-3px); }
    </style>
</head>
<body class="flex items-center justify-center p-5 min-h-screen">
    <div class="max-w-md w-full">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <span class="text-2xl text-rose-500">✧</span>
            </div>
            <h1 class="font-bold text-2xl text-rose-700">Reset Password</h1>
        </div>
        
        <?php if ($step === 'request'): ?>
        <div class="reset-card bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-rose-100">
            <p class="text-gray-600 text-sm text-center mb-6">Enter the email address associated with your admin account and we'll send you a link to reset your password.</p>
            
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-5 text-sm">● <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="request_reset">
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-400 mb-2">Admin Email Address</label>
                    <input type="email" name="email" required class="input-field w-full px-4 py-3 bg-white rounded-xl focus:outline-none" placeholder="admin@wrappedbyvee.com">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 rounded-xl">Send Reset Link</button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($step === 'sent'): ?>
        <div class="reset-card bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-rose-100 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl text-green-600">✓</span>
            </div>
            <h3 class="font-semibold text-xl text-gray-800 mb-2">Check Your Email</h3>
            <p class="text-gray-500 text-sm mb-6"><?php echo $message; ?></p>
            <a href="login.php" class="text-rose-500 text-sm">Return to Login</a>
        </div>
        <?php endif; ?>
        
        <?php if ($step === 'reset'): ?>
        <div class="reset-card bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-rose-100">
            <p class="text-gray-600 text-sm text-center mb-6">Create a new password for your admin account.</p>
            
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-5 text-sm">● <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-400 mb-2">New Password</label>
                    <input type="password" name="password" required class="input-field w-full px-4 py-3 bg-white rounded-xl focus:outline-none" placeholder="Minimum 8 characters">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-400 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="input-field w-full px-4 py-3 bg-white rounded-xl focus:outline-none" placeholder="Confirm your password">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 rounded-xl">Reset Password</button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($step === 'success'): ?>
        <div class="reset-card bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-rose-100 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl text-green-600">✓</span>
            </div>
            <h3 class="font-semibold text-xl text-gray-800 mb-2">Password Reset Complete!</h3>
            <p class="text-gray-500 text-sm mb-6"><?php echo $message; ?></p>
            <a href="login.php" class="inline-block bg-rose-500 text-white px-6 py-2 rounded-full text-sm">Login Now</a>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-6">
            <a href="login.php" class="back-link text-gray-400 text-sm">
                <span>←</span> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
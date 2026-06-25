<?php
if (!isset($hideNav)) { 
    // This file is included, don't add extra navigation here
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?php echo $page_title ?? 'Admin'; ?> - Wrapped by Vee</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #FAF9F6; }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #FFE0EC;
            padding: 12px 20px;
            display: flex;
            justify-content: space-around;
            z-index: 50;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            border-radius: 16px;
            font-size: 11px;
            color: #9CA3AF;
        }
        .nav-item.active { background: #FFF0F5; color: #C2697E; }
        .nav-icon { font-size: 22px; }
        @media (min-width: 768px) { .bottom-nav { display: none; } }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: white;
            border: 1px solid #FFE0EC;
            border-radius: 40px;
            color: #C2697E;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .back-btn:hover {
            background: #FFF0F5;
            transform: translateX(-2px);
        }
    </style>
</head>
<body>
<?php
// admin/view_proof.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit;
}

$filename = $_GET['file'] ?? '';
if (empty($filename)) {
    header('HTTP/1.0 404 Not Found');
    echo 'No file specified';
    exit;
}

// Clean filename - prevent directory traversal
$filename = basename($filename);

// Allowed file types
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    header('HTTP/1.0 403 Forbidden');
    echo 'File type not allowed';
    exit;
}

// Search in ALL possible locations
$searchPaths = [
    '../uploads/payment/' . $filename,
    '../uploads/payments/' . $filename,
    'uploads/payment/' . $filename,
    'uploads/payments/' . $filename,
    '../../uploads/payment/' . $filename,
    '../../uploads/payments/' . $filename,
    '../uploads/' . $filename,
    'uploads/' . $filename
];

foreach ($searchPaths as $path) {
    if (file_exists($path)) {
        // Serve the file
        $mime = mime_content_type($path);
        if (!$mime) {
            $mime = 'image/jpeg';
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }
}

// If we get here, file not found
header('HTTP/1.0 404 Not Found');
echo 'File not found: ' . $filename;
?>
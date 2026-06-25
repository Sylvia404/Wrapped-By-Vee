<?php
require_once 'functions.php';
header('Content-Type: application/json');

$method = $_GET['method'] ?? 'M-Pesa';
$instructions = getPaymentInstructions($method);

if ($instructions) {
    echo json_encode([
        'success' => true,
        'instruction_text' => $instructions['instruction_text'],
        'phone_number' => $instructions['phone_number'],
        'account_number' => $instructions['account_number']
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>
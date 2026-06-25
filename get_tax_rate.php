<?php
// get_tax_rate.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'functions.php';

try {
    $general = getGeneralSettings();
    $taxRate = isset($general['tax_rate']) ? floatval($general['tax_rate']) : 18;
    
    echo json_encode([
        'success' => true,
        'tax_rate' => $taxRate
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
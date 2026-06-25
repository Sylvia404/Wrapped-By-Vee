<?php
require_once 'functions.php';
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '';

if ($phone) {
    // Search by phone number - show all orders for this customer
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, client_name, client_email, total_amount, tracking_status, payment_status, created_at, items 
                           FROM orders 
                           WHERE phone LIKE ? AND payment_method != 'Custom Request'
                           ORDER BY created_at DESC");
    $stmt->execute(["%$phone%"]);
    $orders = $stmt->fetchAll();
    
    if (count($orders) > 0) {
        $formattedOrders = [];
        foreach ($orders as $order) {
            $items = json_decode($order['items'], true);
            $itemCount = count($items);
            $itemNames = array_slice(array_map(function($item) { return $item['name']; }, $items), 0, 2);
            $itemSummary = implode(', ', $itemNames);
            if ($itemCount > 2) $itemSummary .= " +" . ($itemCount - 2) . " more";
            
            $formattedOrders[] = [
                'id' => $order['id'],
                'client_name' => $order['client_name'],
                'total_amount' => $order['total_amount'],
                'tracking_status' => $order['tracking_status'],
                'payment_status' => $order['payment_status'],
                'created_at' => $order['created_at'],
                'item_count' => $itemCount,
                'items_summary' => $itemSummary,
                'items' => $items
            ];
        }
        echo json_encode(['success' => true, 'orders' => $formattedOrders, 'phone' => $phone]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No orders found for this phone number']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Please provide phone number']);
}
?>
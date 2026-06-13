<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$cart = $data['cart'];
$payment = $data['payment'];
$user_id = $_SESSION['user_id'];

try {
    $subtotal = 0;
    foreach($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.10;
    $total = $subtotal + $tax;

    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_status) VALUES (?, ?, 'pending', 'unpaid')");
    $stmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // Add order items & update stock
    foreach($cart as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }

    // Generate invoice
    $invoice = 'SM-'.date('Ymd').'-'.$order_id;
    $stmt = $pdo->prepare("INSERT INTO billing (order_id, invoice_number, amount, tax, discount, total, payment_method) VALUES (?, ?, ?, ?, 0, ?, ?)");
    $stmt->execute([$order_id, $invoice, $subtotal, $tax, $total, $payment]);

    echo json_encode(['success' => true, 'invoice' => $invoice]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

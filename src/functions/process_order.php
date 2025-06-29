<?php
include('../../db.php');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$order_id_in = $data['order_id'] ?? null;
$cashier = $data['cashier'] ?? 'Unknown';
$status = $data['status'] ?? 'Paid'; // or "Pending"
$tax = $data['tax'] ?? 0;
$total = $data['total'] ?? 0;
$items = $data['items'] ?? [];

// We’ll reset order_date to now on both create & update:
$order_date = date("Y-m-d H:i:s");

if ($order_id_in) {
    // 1) Ensure this order exists:
    $check = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    $check->bind_param("i", $order_id_in);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // No such order → treat as new
        $order_id_in = null;
    }
    $check->close();
}

if ($order_id_in) {
    // === UPDATE existing order ===

    // 2) Remove old items
    $del = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $del->bind_param("i", $order_id_in);
    $del->execute();
    $del->close();

    // 3) Update the order row
    $upd = $conn->prepare("
        UPDATE orders
           SET order_date   = ?,
               cashier      = ?,
               status       = ?,
               tax_amount   = ?,
               total_amount = ?
         WHERE order_id = ?
    ");
    $upd->bind_param(
        "sssddi",
        $order_date,
        $cashier,
        $status,
        $tax,
        $total,
        $order_id_in
    );
    $upd->execute();
    $upd->close();

    $order_id = $order_id_in;

} else {
    // === INSERT new order ===
    $ins = $conn->prepare("
        INSERT INTO orders
            (order_date, cashier, status, tax_amount, total_amount)
         VALUES
            (?, ?, ?, ?, ?)
    ");
    $ins->bind_param(
        "sssdd",
        $order_date,
        $cashier,
        $status,
        $tax,
        $total
    );
    $ins->execute();
    $order_id = $ins->insert_id;
    $ins->close();
}

// 4) Insert all items under $order_id
$itemStmt = $conn->prepare("
    INSERT INTO order_items
        (order_id, product_name, quantity, price)
    VALUES
        (?, ?, ?, ?)
");

foreach ($items as $name => $it) {
    $qty = (int) $it['quantity'];
    $price = (float) $it['price'];
    $itemStmt->bind_param("isid", $order_id, $name, $qty, $price);
    $itemStmt->execute();
}

$itemStmt->close();

echo json_encode(['success' => true, 'order_id' => $order_id]);

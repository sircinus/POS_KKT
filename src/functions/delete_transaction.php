<?php
include('../../db.php');

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $id = $_GET['order_id'];

    $sql = "DELETE FROM orders WHERE order_id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../screens/transaction.php?message=Order deleted successfully");
        exit;
    } else {
        echo "Error deleting order: " . $conn->error;
    }
} else {
    header("Location: ../screens/transaction.php?message=Invalid order ID");
    exit;
}

$conn->close();
?>
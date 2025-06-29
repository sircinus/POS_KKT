<?php
include('../../db.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM produk WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../screens/product.php?message=Product deleted successfully");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    header("Location: ../screens/product.php?message=Invalid product ID");
    exit;
}

$conn->close();
?>
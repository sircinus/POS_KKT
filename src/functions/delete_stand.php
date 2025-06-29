<?php
include('../../db.php');

// Check if 'id' is set and it's a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $standId = $_GET['id'];

    // Fetch the stand name before deleting
    $getName = $conn->prepare("SELECT nama_stand FROM stand WHERE id = ?");
    $getName->bind_param("i", $standId);
    $getName->execute();
    $getName->bind_result($standName);
    $getName->fetch();
    $getName->close();

    if ($standName) {
        // Update all products with this stand name to 'not set'
        $updateProduk = $conn->prepare("UPDATE produk SET nama_stand = 'Not Set' WHERE nama_stand = ?");
        $updateProduk->bind_param("s", $standName);
        $updateProduk->execute();
        $updateProduk->close();

        // Delete the stand
        $stmt = $conn->prepare("DELETE FROM stand WHERE id = ?");
        $stmt->bind_param("i", $standId);

        if ($stmt->execute()) {
            header("Location: ../screens/product.php");
            exit();
        } else {
            echo "Error deleting stand: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Stand not found.";
    }
} else {
    echo "Invalid stand ID.";
}

$conn->close();
?>
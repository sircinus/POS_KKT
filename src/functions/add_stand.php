<?php
include('../../db.php');

// Handle adding a new stand
if (isset($_GET['add_stand']) && $_GET['add_stand'] == 'true') {
    // Get the current number of stands
    $count = $conn->query("SELECT COUNT(*) as total FROM stand")->fetch_assoc()['total'];

    // Create the name for the new stand (Stand 1, Stand 2, etc.)
    $newStandName = 'Stand ' . ($count + 1);

    // Insert the new stand into the database
    $stmt = $conn->prepare("INSERT INTO stand (nama_stand) VALUES (?)");
    $stmt->bind_param("s", $newStandName);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the stand list page
    header("Location: ../screens/product.php");
    exit();
}

?>
<?php
include('../../db.php');

// Get the stand ID from the URL
$standId = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch the stand data for editing
$standResult = $conn->query("SELECT * FROM stand WHERE id = $standId");
$stand = $standResult->fetch_assoc();

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newStandName = $_POST['nama_stand'];
    $oldStandName = $stand['nama_stand']; // existing name from DB

    // Check if new name already exists (exclude current stand)
    $checkDuplicate = $conn->prepare("SELECT COUNT(*) FROM stand WHERE nama_stand = ? AND id != ?");
    $checkDuplicate->bind_param("si", $newStandName, $standId);
    $checkDuplicate->execute();
    $checkDuplicate->bind_result($duplicateCount);
    $checkDuplicate->fetch();
    $checkDuplicate->close();

    if ($duplicateCount > 0) {
        echo "<script>alert('Nama stand sudah digunakan!'); window.history.back();</script>";
        exit();
    }

    // Update the stand name in the stand table
    $stmt = $conn->prepare("UPDATE stand SET nama_stand = ? WHERE id = ?");
    $stmt->bind_param("si", $newStandName, $standId);
    $stmt->execute();
    $stmt->close();

    // Update all products that used the old stand name
    $updateProduk = $conn->prepare("UPDATE produk SET nama_stand = ? WHERE nama_stand = ?");
    $updateProduk->bind_param("ss", $newStandName, $oldStandName);
    $updateProduk->execute();
    $updateProduk->close();

    // Redirect after update
    header("Location: ../screens/product.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stand</title>
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
</head>

<body>
    <?php include('../functions/navbar.php'); ?>
    <form method="POST" action="">
        <label for="nama_stand">Nama Stand:</label>
        <input class="inputTax" type="text" name="nama_stand"
            value="<?php echo htmlspecialchars($stand['nama_stand']); ?>" required><br>
        <button id="updateButton" type="submit">Update</button>
    </form>
</body>

</html>

<?php $conn->close(); ?>
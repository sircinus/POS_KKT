<?php
include('../../db.php');

$kode = $nama = $kategori = $nama_stand = "";
$modal = $jual = 0;
$foto = null;

// Fetch all stands
$standOptions = [];
$standQuery = $conn->query("SELECT nama_stand FROM stand");
while ($row = $standQuery->fetch_assoc()) {
    $standOptions[] = $row['nama_stand'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $modal = $_POST['modal'];
    $jual = $_POST['jual'];
    $nama_stand = $_POST['nama_stand'] ?? 'Not Set';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
    } else {
        $foto = null;
    }

    // Check for duplicate kode
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM produk WHERE kode = ?");
    $checkStmt->bind_param("s", $kode);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<script>alert('Kode Produk Sudah Digunakan.'); window.history.back();</script>";
        exit();
    }

    // Insert new product
    $stmt = $conn->prepare("INSERT INTO produk (kode, nama, kategori, modal, jual, foto, nama_stand) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddss", $kode, $nama, $kategori, $modal, $jual, $foto, $nama_stand);

    if ($stmt->execute()) {
        header("Location: ../screens/product.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <title>Tambah Produk Baru</title>
</head>

<body>
    <h1 class="titleText">Tambah Produk Baru</h1>
    <form method="POST" action="add_product.php" enctype="multipart/form-data">
        <label for="kode">Kode:</label><br>
        <input type="text" id="kode" name="kode" required><br>

        <label for="nama">Nama:</label><br>
        <input type="text" id="nama" name="nama" required><br>

        <label for="kategori">Kategori:</label><br>
        <select id="kategori" name="kategori" required>
            <option value="Minuman">Minuman</option>
            <option value="Makanan">Makanan</option>
        </select><br>

        <label for="nama_stand">Stand:</label><br>
        <select id="nama_stand" name="nama_stand" required>
            <option value="">-- Pilih Stand --</option>
            <?php foreach ($standOptions as $stand): ?>
                <option value="<?php echo htmlspecialchars($stand); ?>">
                    <?php echo htmlspecialchars($stand); ?>
                </option>
            <?php endforeach; ?>
            <option value="Not Set">Not Set</option>
        </select><br>

        <label for="modal">Modal:</label><br>
        <input type="number" id="modal" name="modal" step="1000" value="0" required><br>

        <label for="jual">Harga Jual:</label><br>
        <input type="number" id="jual" name="jual" step="1000" value="0" required><br>

        <label for="foto">Foto Produk:</label><br>
        <input type="file" id="foto" name="foto" accept="image/*"><br><br>

        <input type="submit" value="Tambah Produk">
    </form>
</body>

</html>
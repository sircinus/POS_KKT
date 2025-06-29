<?php
include('../../db.php');

$searchQuery = $_GET['search'] ?? '';
$standFilter = $_GET['nama_stand'] ?? '';
$kategoriFilter = $_GET['kategori'] ?? '';

// Prepare kategori options
$kategoriOptions = ['Makanan', 'Minuman']; // Extend if needed

// Prepare base query
$sql = "SELECT * FROM produk WHERE 1=1";

// Add search filter if available
if (!empty($searchQuery)) {
    $escapedSearch = $conn->real_escape_string($searchQuery);
    $sql .= " AND (kode LIKE '%$escapedSearch%' OR nama LIKE '%$escapedSearch%' OR kategori LIKE '%$escapedSearch%' OR nama_stand LIKE '%$escapedSearch%')";
}

// Add stand filter if selected
if (!empty($standFilter)) {
    $escapedStand = $conn->real_escape_string($standFilter);
    $sql .= " AND nama_stand = '$escapedStand'";
}

// Add kategori filter if selected
if (!empty($kategoriFilter)) {
    $escapedKategori = $conn->real_escape_string($kategoriFilter);
    $sql .= " AND kategori = '$escapedKategori'";
}

$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

$standResult = $conn->query('SELECT * FROM stand');

// Get list of stand options for dropdown
$standOptions = [];
$standQuery = $conn->query("SELECT nama_stand FROM stand");
while ($row = $standQuery->fetch_assoc()) {
    $standOptions[] = $row['nama_stand'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Produk Kedai Kopi Kita</title>
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include('../functions/navbar.php'); ?>

    <div class="wrapContainer stand">
        <div class="standLeftContainer">
            <h2>Pengaturan Stand</h2>
            <button class="addButton" onclick="window.location.href='../functions/add_stand.php?add_stand=true'"><i
                    class="fas fa-plus"></i> Tambah Stand</button>
            <button class="taxButton" onclick="window.location.href='../functions/tax.php'"><i
                    class="fas fa-hand-holding-dollar"></i> Atur Pajak Restoran</button>
        </div>

        <div class="standRightContainer">
            <table border="1" class="table">
                <tr>
                    <th>No</th>
                    <th>Stand</th>
                    <th>Aksi</th>
                </tr>
                <?php
                if ($standResult->num_rows > 0) {
                    $no = 1;
                    while ($row = $standResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama_stand']) . "</td>";
                        echo "<td>
                                <a href='../functions/edit_stand.php?id=" . $row['id'] . "'><button class='editButton'>Edit</button></a>
                                <a href='../functions/delete_stand.php?id=" . $row['id'] . "' onclick='return confirm(\"Yakin Menghapus Stand Ini?\");'><button class='deleteButton'>Hapus</button></a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Belum ada stand</td></tr>";
                }
                ?>
            </table>
        </div>
        <br>
    </div>

    <div class="separator">
        <hr style="border: 1px solid #000; margin: 30px;">
    </div>

    <div class="productContainer">
        <div class="wrapContainer spaceBetween">
            <form method="GET" onchange="this.submit()">
                <input type="text" name="search" id="searchInput" placeholder="Cari Produk"
                    value="<?php echo htmlspecialchars($searchQuery); ?>" autocomplete="off">

                <button type="submit" class="searchButton"><i class="fas fa-search"></i> Cari</button>


                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="clearButton">
                    <i class=" fas fa-times-circle"></i> Hapus Filter
                </a>


                <select id="nama_stand" name="nama_stand" class="standSelect">
                    <option value="">Stand</option>
                    <?php foreach ($standOptions as $stand): ?>
                        <option value="<?php echo htmlspecialchars($stand); ?>" <?php echo ($stand === $standFilter) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stand); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <select id="kategori" name="kategori" class="kategoriSelect">
                    <option value="">Kategori</option>
                    <?php foreach ($kategoriOptions as $kategori): ?>
                        <option value="<?php echo htmlspecialchars($kategori); ?>" <?php echo ($kategori === $kategoriFilter) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kategori); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>


            </form>
            <div style="margin-top: 3px;">
                <button class="addButton" onclick="window.location.href='../functions/add_product.php'">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
        </div>


        <table class="table" border="1">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Stand</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Modal</th>
                <th>Jual</th>
                <th>Foto</th>
                <th colspan="2">Aksi</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['kode']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_stand']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                    echo "<td>Rp" . number_format($row['modal']) . "</td>";
                    echo "<td>Rp" . number_format($row['jual']) . "</td>";
                    echo "<td>";
                    if (!empty($row['foto'])) {
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($row['foto']) . '" width="160" height="90">';
                    } else {
                        echo 'No Image';
                    }
                    echo "</td>";
                    echo "<td><a href='../functions/edit_product.php?id=" . $row['id'] . "'><button class='editButton'>Edit</button></a></td>";
                    echo "<td><a href='../functions/delete_product.php?id=" . $row['id'] . "' onclick='return confirm(\"Yakin Menghapus Produk Ini?\");'><button class='deleteButton'>Hapus</button></a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>Tidak Ada Produk</td></tr>";
            }
            ?>
        </table>
    </div>

</body>

</html>

<?php
$conn->close();
?>
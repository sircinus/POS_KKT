<?php
include('../../db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the product details
    $query = "SELECT * FROM produk WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit();
    }
} else {
    echo "Invalid ID!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $modal = $_POST['modal'];
    $jual = $_POST['jual'];
    $removeImage = isset($_POST['remove_image']) ? 1 : 0;
    $nama_stand = $_POST['nama_stand'];  // Get the selected stand name from the form

    // Check if an image is uploaded
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Handle file upload
        $imageData = file_get_contents($_FILES['foto']['tmp_name']);
        $imageType = $_FILES['foto']['type'];

        // Validate if it's an image
        if (strpos($imageType, 'image') === false) {
            echo "Invalid file type! Please upload an image.";
        } else {
            // Update product details with the new image and selected stand name
            $updateQuery = "UPDATE produk SET kode=?, nama=?, kategori=?, modal=?, jual=?, foto=?, nama_stand=? WHERE id=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('sssdssis', $kode, $nama, $kategori, $modal, $jual, $imageData, $nama_stand, $id);  // Bind all parameters
            $stmt->execute();
            echo "Product updated successfully!";
            header("Location: ../screens/product.php");
            exit();
        }
    }

    // Check if the 'remove_image' action button is clicked
    if ($removeImage) {
        // Remove the image from the database
        $updateQuery = "UPDATE produk SET foto=NULL WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo "Image removed successfully!";
            header("Location: edit_product.php?id=$id");
            exit();
        } else {
            echo "Error removing image: " . $conn->error;
        }
    } else {
        // Update the product details without changing the image
        $updateQuery = "UPDATE produk SET kode=?, nama=?, kategori=?, modal=?, jual=?, nama_stand=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('sssdssi', $kode, $nama, $kategori, $modal, $jual, $nama_stand, $id);  // Bind all parameters
        if ($stmt->execute()) {
            echo "Product updated successfully!";
            header("Location: ../screens/product.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <title>Edit Product</title>
</head>

<body>

    <h2 class="titleText">Edit Product</h2>
    <div class="editContainer">
        <div class="editLeftContainer">
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="kode">Kode:</label><br>
                <input type="text" id="kode" name="kode" value="<?php echo $row['kode']; ?>" required><br><br>

                <label>Stand:</label><br>
                <?php
                // Fetch stands from the stand table
                $standQuery = "SELECT * FROM stand";
                $standResult = $conn->query($standQuery);

                if ($standResult->num_rows > 0) {
                    echo '<select name="nama_stand" required>';
                    echo '<option value="">Select Stand</option>';  // Placeholder option
                
                    // Populate the dropdown with stand options
                    while ($stand = $standResult->fetch_assoc()) {
                        // Set the selected option based on the stand name
                        echo '<option value="' . $stand['nama_stand'] . '"';
                        if ($row['nama_stand'] == $stand['nama_stand']) {
                            echo ' selected'; // If the current stand name matches, set it as selected
                        }
                        echo '>' . $stand['nama_stand'] . '</option>';
                    }

                    echo '</select>';
                } else {
                    echo "No stands available.";
                }
                ?><br><br>

                <label for="nama">Nama:</label><br>
                <input type="text" id="nama" name="nama" value="<?php echo $row['nama']; ?>" required><br><br>

                <label for="kategori">Kategori:</label><br>
                <select id="kategori" name="kategori" required><br>
                    <option value="Minuman" <?php echo ($row['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman
                    </option>
                    <option value="Makanan" <?php echo ($row['kategori'] == 'Makanan') ? 'selected' : ''; ?>>Makanan
                    </option>
                </select><br><br>

                <label for="modal">Modal:</label><br>
                <input type="number" id="modal" name="modal" value="<?php echo $row['modal']; ?>" step="1000"
                    required><br><br>

                <label for="jual">Harga Jual:</label><br>
                <input type="number" id="jual" name="jual" value="<?php echo $row['jual']; ?>" step="1000"
                    required><br><br>

        </div>
        <div class="editRightContainer">
            <label>Foto:</label><br>
            <?php if (!empty($row['foto'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['foto']); ?>" width="300" height="300"
                    style="object-fit: cover; margin: 10px"><br><br>

            <?php else: ?>
                No Image<br><br>
            <?php endif; ?>

            <input type="file" name="foto" id="foto" class="editPic" accept="image/*"><br><br>
            <button type="submit" id="remove_image" name="remove_image">Remove Image</button><br><br>

        </div>
    </div>

    <button type="submit" id="updateButton">Update Product</button>

    </form>

</body>

</html>

<?php
$conn->close();
?>
<?php
include('../../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tax_percentage = $_POST['tax_percentage'];

    $query = "SELECT * FROM tax LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $update_query = "UPDATE tax SET percentage = $tax_percentage WHERE id = 0";
        $conn->query($update_query);
    } else {
        $insert_query = "INSERT INTO tax (percentage) VALUES ($tax_percentage)";
        $conn->query($insert_query);
    }

    header("Location: ../screens/product.php");
    exit();
}

$query = "SELECT * FROM tax LIMIT 1";
$result = $conn->query($query);
$current_tax = ($result->num_rows > 0) ? $result->fetch_assoc() : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <title>Persen Pajak</title>
</head>

<body>
    <h1 class="titleText">Pengaturan Persentase Pajak</h1>

    <form action="" method="post">
        <div class="percentSymbol">
            <input class="inputTax" type="number" name="tax_percentage" min="0" max="100" step="1"
                placeholder="Persentase Pajak" required
                value="<?php echo $current_tax ? $current_tax['percentage'] : ''; ?>" />
            <p class="percentSymbolText">%</p>
        </div>

        <button id="updateButton" type="submit">Save</button>
    </form>
</body>

</html>

<?php
$conn->close();
?>
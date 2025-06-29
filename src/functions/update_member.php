<?php
include('../../db.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid user ID");
}

// Fetch user data
$query = "SELECT * FROM user WHERE id = $id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update user data
    $update_query = "UPDATE user SET nama = '$nama', email = '$email', jabatan = '$role' WHERE id = $id";
    if ($conn->query($update_query)) {
        header("Location: ../screens/member.php");
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <title>Update Member</title>
</head>

<body>
    <div class="updateContainer">
        <h1 class="updateText">Ubah Data</h1>
        <form method="POST">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                required><br>

            <label for="nama">Nama:</label><br>
            <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($user['nama']); ?>"
                required><br>

            <label for="role">Jabatan:</label><br>
            <select name="role" id="role" required>
                <option value="Owner" <?php if ($user['jabatan'] == 'Owner') {
                    echo 'selected';
                } ?>>Owner</option>
                <option value="Cashier" <?php if ($user['jabatan'] == 'Cashier') {
                    echo 'selected';
                } ?>>Cashier</option>
            </select><br>

            <div class="centerButtonContainer">
                <button type="submit" id="centerButton">Update</button>
            </div>
        </form>

        <?php if (isset($error_message)) { ?>
            <p style="color: red;"> <?php echo $error_message; ?> </p>
        <?php } ?>
    </div>
</body>

</html>
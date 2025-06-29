<?php
include('../../db.php');
include('../functions/navbar.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $check_query = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        $error_message = "Email already registered. Please use a different email.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO user (nama, email, password, jabatan) VALUES ('$nama', '$email', '$hashed_password', '$role')";

        if ($conn->query($insert_query) === TRUE) {
            $success_message = "Registration successful!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

$user_query = "SELECT * FROM user WHERE jabatan != 'admin' ORDER BY FIELD(jabatan, 'Owner', 'Cashier'), nama ASC";
$users = $conn->query($user_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>User Management</title>
</head>

<body>
    <div class="addBackContainer">
        <div class="addLeftContainer">
            <h1 class="addTitleText">Tambah User</h1>
            <form method="POST" action="member.php">
                <input type="email" name="email" class="addInput" id="email" placeholder="Email" required><br>
                <input class="addInput" type="text" name="nama" id="nama" placeholder="Nama" required><br>
                <input type="password" name="password" class="addInput" id="password" placeholder="Password"
                    required><br>
                <div class="bottomRow">
                    <div>
                        <select name="role" id="role" required>
                            <option value="Owner">Owner</option>
                            <option value="Cashier">Cashier</option>
                        </select><br>
                    </div>
                    <div>
                        <button type="submit" id="register">Register</button>
                    </div>
                </div>
            </form>
            <?php if ($error_message) { ?>
                <p style="color: red; margin-left: 20px;"><?php echo $error_message; ?></p>
            <?php } ?>
            <?php if ($success_message) { ?>
                <p style="color: green; margin-left: 20px;"><?php echo $success_message; ?></p>
            <?php } ?>
        </div>
        <div class="addRightContainer">
            <h2 class="addTitleText">Daftar User</h2>
            <table border="1">
                <tr>
                    <th>Email</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th colspan="3">Aksi</th>
                </tr>
                <?php while ($user = $users->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['nama']; ?></td>
                        <td><?php echo $user['jabatan']; ?></td>
                        <td><button class="editButton"
                                onclick="window.location.href='../functions/update_member.php?id=<?php echo $user['id']; ?>'">Update</button>
                        </td>
                        <td>
                            <button class="changePasswordButton"
                                onclick="window.location.href='../functions/password_member.php?id=<?php echo $user['id'] ?>'">Ubah
                                Password</button>
                        </td>
                        <td>
                            <button class="deleteButton" onclick="confirmDelete(<?php echo $user['id']; ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>

<script>
    function confirmDelete(userId) {
        if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
            window.location.href = '../functions/delete_member.php?id=' + userId;
        }
    }
</script>

</html>
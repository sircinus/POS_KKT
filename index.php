<?php
session_start();
include('db.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $selected_role = $_POST['role'];

    $query = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            if ($selected_role === $user['jabatan']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_role'] = $user['jabatan'];

                if ($user['jabatan'] == 'Admin' || $user['jabatan'] == 'Owner') {
                    header("Location: ./src/screens/dashboard.php");
                } else {
                    header("Location: ./src/screens/cashier.php");
                }
                exit();
            } else {
                $error_message = "Invalid email, password, or role.";
            }
        } else {
            $error_message = "Invalid email, password, or role.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Kopi Kita - Login</title>
    <link rel="stylesheet" type="text/css" href="./src/css/index.css">
</head>

<body>
    <div id="splash-screen">
        <img src="assets/logo.png" alt="Loading..." class="splash-logo" />
    </div>

    <div class="centerContainer">
        <img src="assets/logo.png" alt="Kedai Kopi Kita Logo" width="400">

        <form method="POST" action="index.php">
            <input class="emailContainer" type="email" name="email" placeholder="Email" required><br>
            <input class="passwordContainer" type="password" name="password" placeholder="Password" required><br>

            <div class="bottomRow">
                <select name="role" class="roleDropdown" required>
                    <option value="Admin">Super Admin</option>
                    <option value="Owner">Owner</option>
                    <option value="Cashier">Kasir</option>
                </select><br>

                <button class="loginButton" type="submit">Login</button>
            </div>
        </form>

        <div class="forgotPass">
            <a class="forgotPassText" href="./src/functions/forgot_pass.php">Lupa Password?</a>
        </div>

        <?php if ($error_message) { ?>
            <p style="color: blue;"><?php echo $error_message; ?></p>
        <?php } ?>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                const splash = document.getElementById('splash-screen');
                splash.classList.add('hidden');
            }, 2000);
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>
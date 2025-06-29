<?php

session_start();
include('../../db.php');

// Get the user ID from the URL
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['password_conf'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        die("Passwords must match.");
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $sql = "UPDATE user SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $user_id);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo "Password successfully updated.";
        // Redirect to the user list or another page
        header("Location: ../screens/member.php");
    } else {
        die("Error updating password. Please try again.");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <title>Admin Reset Password</title>
</head>

<body>

    <div class="centerContainer">
        <h1>Reset User Password</h1>
        <form method="post">
            <input type="hidden" id="user_id" name="user_id" value="<?php echo $id; ?>" required><br>

            <label for="password">New Password:</label><br>
            <input type="password" class="sendEmailContainer" id="password" name="password" required><br>

            <label for="password_conf">Confirm Password:</label><br>
            <input type="password" class="sendEmailContainer" id="password_conf" name="password_conf" required><br>

            <button type="submit" class="sendButton">Update Password</button>
        </form>
    </div>

</body>

</html>
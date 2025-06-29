<?php

include('../../db.php');

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

$sql = "SELECT * FROM user WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("Token not found.");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token expired.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <title>Reset Password</title>
</head>

<body>

    <div class="centerContainer">
        <h1>Reset Password</h1>
        <form method="post" action="process_reset_password.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

<label for="password">New Password:</label><br> <input class="sendEmailContainer" type="password" id="password"
        name="password" required><br>

    <label for="password_conf">Confirm Password:</label><br>
    <input class="sendEmailContainer" type="password" id="password_conf" name="password_conf" required><br>

    <button type="submit" class="sendButton">Submit</button>
    </form>
    </div>

    </body>

    </html>
<?php

include('../../db.php');

$token = $_POST["token"] ?? null;
if (!$token) {
    die("Invalid token.");
}

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

if ($_POST['password'] !== $_POST['password_conf']) {
    die("Passwords must match.");
}

$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "UPDATE user 
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $password, $user["id"]);
$stmt->execute();

header("Location: ../../index.php?message=Password reset successful. Please login.");
exit();
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos_kkt";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed");
}
?>
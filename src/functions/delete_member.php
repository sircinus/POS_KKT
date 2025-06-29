<?php

include('../../db.php');

$id = $_GET['id'];
$sql = "DELETE FROM user
        WHERE id='$id'";

if ($conn->query($sql) == TRUE) {
    header("location: ../screens/member.php");
}

$conn->close();

?>
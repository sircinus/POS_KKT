<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'];

$token = bin2hex(random_bytes(16));

$token_hash = hash('sha256', $token);

$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

require '../../db.php';

$sql = "UPDATE user
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $token_hash, $expiry, $email);

$stmt->execute();

if ($stmt->affected_rows > 0) {

    $mail = require 'mailer.php';

    $mail->setFrom('romeokurniawan1847@gmail.com');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset';
    $mail->Body = <<<END

    Click <a href=http://localhost/POS_KKT/src/functions/reset_pass.php?token=$token>here</a> to reset your password.
    
    END;

    try {
        $mail->send();
        header("location:../../index.php");
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
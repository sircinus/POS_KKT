<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="../css/index.css">
</head>

<body>
    <div class="centerContainer">
        <form method="POST" action="send_pass.php">
            <label for="email"><b>Enter your email to reset password</b></label><br>
            <input type="email" name="email" class="sendEmailContainer" id="email" placeholder="Email" required><br>
            <button type="submit" class="sendButton">Send</button>
        </form>
    </div>
</body>

</html>
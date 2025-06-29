<?php
session_start();
?>

<nav class="navbar">
    <div>
        <img src="../../assets/logo.png" alt="Kedai Kopi Kita Logo" width="200" style="padding: 10px;">
    </div>
    <div>
        <ul>
            <?php if ($_SESSION['user_role'] !== 'Cashier') { ?>
                <li><a href="../../src/screens/dashboard.php">Dashboard</a></li>
                <li><a href="../../src/screens/product.php">Produk</a></li>
            <?php } ?>

            <?php if ($_SESSION['user_role'] == 'Admin') { ?>
                <li><a href="../../src/screens/member.php">User</a></li>
            <?php } ?>

            <?php if ($_SESSION['user_role'] == 'Cashier') { ?>
                <li><a href="../../src/screens/cashier.php">Kasir</a></li>
            <?php } ?>

            <li><a href="../../src/screens/transaction.php">Transaksi</a></li>
        </ul>
    </div>
</nav>

<div class="wrapContainer spaceBetween">
    <span id="current-datetime"></span>
    <div class="wrapContainer flexEnd">
        <p class="username">
            <i class="fas fa-user"></i>
            Welcome, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : "Guest"; ?>
        </p>
        <button onclick="window.location.href='../../src/functions/logout.php'" class="logout">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
    </div>
</div>

<script>
    const datetimeSpan = document.getElementById('current-datetime');

    function updateDateTime() {
        const now = new Date();
        const datetime = now.toLocaleString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
            timeZoneName: 'short'
        });
        datetimeSpan.textContent = datetime;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>
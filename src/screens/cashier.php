<?php
include('../../db.php');
include('../functions/navbar.php');

$pending_order = null;

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $order_query = "SELECT * FROM orders WHERE order_id = '$order_id'";
    $order_result = $conn->query($order_query);

    if ($order_result && $order_result->num_rows > 0) {
        $pending_order = $order_result->fetch_assoc();

        $details_query = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        $details_result = $conn->query($details_query);

        $pending_items = [];
        while ($item = $details_result->fetch_assoc()) {
            $pending_items[] = [
                'product_name' => $item['product_name'], // Make sure this exists in DB
                'unit_price' => (float) $item['price'],
                'quantity' => (int) $item['quantity']
            ];
        }
    }
}

$product_makanan = "SELECT id, nama, foto, jual FROM produk WHERE kategori = 'makanan'";
$products_makanan = $conn->query($product_makanan);

$product_minuman = "SELECT id, nama, foto, jual FROM produk WHERE kategori = 'minuman'";
$products_minuman = $conn->query($product_minuman);

$tax_query = "SELECT percentage FROM tax LIMIT 1";
$tax_result = $conn->query($tax_query);
$tax_percentage = ($tax_result->num_rows > 0) ? $tax_result->fetch_assoc()['percentage'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Cashier</title>
</head>

<body>

    <div class="cashierContainer">
        <!-- Left side - Products -->
        <div class="leftContainer">
            <h1 class="menuText">Makanan</h1>
            <div class="productsContainer">
                <?php while ($product = $products_makanan->fetch_assoc()) { ?>
                    <div class="product_box"
                        onclick="addToCart('<?php echo htmlspecialchars($product['nama']); ?>', <?php echo $product['jual']; ?>)">
                        <div class="product_image">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['foto']); ?>"
                                alt="Product Image">
                        </div>
                        <p><?php echo htmlspecialchars($product['nama']); ?></p><br>
                    </div>

                <?php } ?>
            </div>

            <h1 class="menuText">Minuman</h1>
            <div class="productsContainer">
                <?php while ($product = $products_minuman->fetch_assoc()) { ?>
                    <div class="product_box"
                        onclick="addToCart('<?php echo htmlspecialchars($product['nama']); ?>', <?php echo $product['jual']; ?>)">
                        <div class="product_image">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['foto']); ?>"
                                alt="Product Image">
                        </div>
                        <p><?php echo htmlspecialchars($product['nama']); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Right side - Ticket and Charge -->
        <div class="rightContainer">
            <div class="topTicketContainer">
                <p>Ticket</p>
            </div>

            <div class="ticketWrapper">
                <div id="ticketList"></div>
                <div class="bottomWrapper">
                    <div class="taxContainer">
                        <p class="totalTaxText" id="taxAmount">Tax: Rp 0</p>
                    </div>
                    <div class="totalContainer">
                        <p class="totalAmountText" id="totalAmount">Total: Rp 0</p>
                        <div>
                            <button id="saveTicketButton" onclick="saveTicket()">SAVE</button>
                            <button id="chargeButton" onclick="openPaymentModal()">CHARGE</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modalContent">
            <div id="topModalPart">
                <h2 class="titleText">Enter Amount Of Payment</h2>
            </div>

            <!-- Inside the modal -->
            <div id="paymentMessage" style="display:none;">
                <p id="paymentSuccessMessage">Payment Successful!</p>
            </div>

            <div class="paymentWrapper">
                <label for="modalTotalAmount" class="leftLabelContainer">Total: </label><br>
                <div class="rightLabelContainer">
                    <p id="modalTotalAmount" class="modalTotalText">Rp 0</p>
                </div>

            </div>
            <div class="paymentWrapper">
                <label for="paymentAmount" class="leftLabelContainer">Bayar: </label><br>
                <input class="rightLabelContainer" type="number" step="1000" id="paymentAmount"
                    style="font-size: 40px; padding: 5px; font-weight:bolder;" />
            </div>
            <div class="paymentWrapper">
                <label for="returnAmountText" class="leftLabelContainer">Kembali: </label><br>
                <div class="rightLabelContainer">
                    <p id="returnAmountText" class="modalTotalText">Rp 0</p>
                </div>
            </div>
            <div class="modalButtonsContainer">
                <button class="modalButtonsCancel" onclick="closePaymentModal()">Batal</button>
                <button class="modalButtons" onclick="processPayment()">Bayar</button>
            </div>

            <button class="nextButton" onclick="closeAndResetPaymentModal()">Tutup</button>


        </div>
    </div>

    <script>
        const loadedOrderItems = <?php echo json_encode($pending_items ?? []); ?>;
        const orderId = <?php echo isset($order_id) ? json_encode($order_id) : 'null'; ?>;
        let cart = {};
        const taxPercentage = <?php echo $tax_percentage; ?>;
        const modal = document.getElementById("paymentModal");
        const returnAmountText = document.getElementById("returnAmountText");
        const paymentAmountInput = document.getElementById("paymentAmount");

        // 1) FUNCTION DECLARATIONS

        function renderItem(productName) {
            const itemId = "item-" + productName.replace(/\s+/g, '-');
            const el = document.getElementById(itemId);
            if (!el) return;
            const { quantity, price } = cart[productName];
            const total = quantity * price;
            el.innerHTML = `
      <span class="itemName">${productName} x${quantity}</span>
      <span>
        <span class="itemPrice">Rp ${total.toLocaleString('id-ID')}</span>
        <button onclick="decrementItem('${productName}')">−</button>
        <button onclick="incrementItem('${productName}')">+</button>
      </span>
    `;
        }

        function updateTotalAmount() {
            let subtotal = Object.values(cart)
                .reduce((sum, { quantity, price }) => sum + quantity * price, 0);
            const tax = subtotal * (taxPercentage / 100);
            const grand = subtotal + tax;
            document.getElementById("totalAmount").innerText =
                `Total: Rp ${grand.toLocaleString('id-ID')}`;
            document.getElementById("taxAmount").innerText =
                `Tax: Rp ${tax.toLocaleString('id-ID')}`;
            return grand;
        }

        function addToCart(name, price) {
            if (cart[name]) cart[name].quantity++;
            else {
                cart[name] = { quantity: 1, price };
                const el = document.createElement("div");
                el.className = "ticketItems";
                el.id = "item-" + name.replace(/\s+/g, '-');
                document.getElementById("ticketList").appendChild(el);
            }
            renderItem(name);
            updateTotalAmount();
        }

        function saveTicket() {
            if (!Object.keys(cart).length) return alert("Cart is empty.");
            const total = updateTotalAmount();
            const orderData = {
                total,
                tax: total * (taxPercentage / 100),
                cashier: '<?php echo $_SESSION["user_name"]; ?>',
                items: cart,
                status: "Pending",
                order_id: orderId
            };
            fetch('../functions/process_order.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            })
                .then(r => r.json()).then(d => {
                    if (d.success) {
                        alert("Ticket tersimpan.");
                        cart = {}; document.getElementById("ticketList").innerHTML = "";
                        updateTotalAmount();
                    } else alert("Gagal menyimpan.");
                });
        }

        function processPayment() {
            const paid = parseFloat(paymentAmountInput.value);
            if (isNaN(paid) || paid <= 0) return alert("Enter valid amount.");
            const grand = updateTotalAmount();
            const change = paid - grand;
            if (change < 0) return alert("Insufficient payment.");
            returnAmountText.innerText = `Rp ${change.toLocaleString('id-ID')}`;
            const orderData = {
                total: grand,
                tax: grand - (grand / (1 + taxPercentage / 100)),
                cashier: '<?php echo $_SESSION["user_name"]; ?>',
                items: cart,
                status: "Paid",
                <?php if (isset($_GET['order_id'])): ?>
            order_id: <?php echo (int) $_GET['order_id']; ?>,
                <?php endif; ?>
            };

            fetch('../functions/process_order.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            })
                .then(r => r.json()).then(d => {
                    if (d.success) {
                        document.getElementById("paymentMessage").style.display = "block";
                        document.getElementsByClassName("nextButton")[0].style.display = "block";
                        document.getElementById("topModalPart").style.display = "none";
                        document.getElementsByClassName("modalButtonsContainer")[0].style.display = "none";
                    }
                });
        }

        function openPaymentModal() {
            if (!Object.keys(cart).length) return alert("Cart is empty.");
            document.getElementById("modalTotalAmount").innerText =
                `Rp ${updateTotalAmount().toLocaleString('id-ID')}`;
            modal.style.display = "block";
            paymentAmountInput.value = "";
            returnAmountText.innerText = "Rp 0";
        }

        function closePaymentModal() {
            modal.style.display = "none";
            document.getElementById("paymentMessage").style.display = "none";
            document.getElementById("topModalPart").style.display = "block";
            document.getElementsByClassName("modalButtonsContainer")[0].style.display = "block";
            document.getElementsByClassName("nextButton")[0].style.display = "none";
        }

        function closeAndResetPaymentModal() {
            closePaymentModal();
            cart = {}; document.getElementById("ticketList").innerHTML = "";
            updateTotalAmount();
        }

        function decrementItem(name) {
            if (cart[name].quantity > 1) cart[name].quantity--;
            else {
                delete cart[name];
                const el = document.getElementById("item-" + name.replace(/\s+/g, '-'));
                if (el) el.remove();
            }
            if (cart[name]) renderItem(name);
            updateTotalAmount();
        }

        function incrementItem(name) {
            cart[name].quantity++;
            renderItem(name);
            updateTotalAmount();
        }


        // 2) NOW that every function exists, populate any pending order:
        loadedOrderItems.forEach(item => {
            const name = item.product_name;
            const price = parseFloat(item.unit_price) || 0;
            const qty = parseInt(item.quantity, 10) || 0;
            if (!name || price <= 0 || qty <= 0) return;
            cart[name] = { quantity: qty, price };
            const el = document.createElement("div");
            el.className = "ticketItems";
            el.id = "item-" + name.replace(/\s+/g, '-');
            document.getElementById("ticketList").appendChild(el);
            renderItem(name);
        });

        // 3) Finally update your total
        updateTotalAmount();

    </script>

</body>

</html>
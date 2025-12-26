<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'food_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info
$userStmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Initialize address_display so it always has a value to avoid undefined errors
$address_display = '';

// Only proceed if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'] ?? '';

    if ($address_id === 'new') {
        // New address details from POST
        $new_label = trim($_POST['new_label'] ?? '');
        $new_full_address = trim($_POST['new_full_address'] ?? '');
        $new_city = trim($_POST['new_city'] ?? '');
        $new_postal_code = trim($_POST['new_postal_code'] ?? '');

        // Validate new address fields (optional but recommended)
        if ($new_label === '' || $new_full_address === '' || $new_city === '' || $new_postal_code === '') {
            die("Please fill all new address fields.");
        }

        // Insert the new address into the database
        $insertAddrStmt = $conn->prepare("INSERT INTO addresses (user_id, label, full_address, city, postal_code) VALUES (?, ?, ?, ?, ?)");
        $insertAddrStmt->bind_param("issss", $user_id, $new_label, $new_full_address, $new_city, $new_postal_code);

        if ($insertAddrStmt->execute()) {
            // Get the newly inserted address ID (optional)
            $address_id = $conn->insert_id;
        } else {
            die("Failed to save new address.");
        }

        $address_display = htmlspecialchars("$new_label, $new_full_address, $new_city, $new_postal_code");

    } elseif (is_numeric($address_id)) {
        // Fetch existing address
        $addrStmt = $conn->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE address_id = ? AND user_id = ?");
        $addrStmt->bind_param("ii", $address_id, $user_id);
        $addrStmt->execute();
        $addr = $addrStmt->get_result()->fetch_assoc();

        if (!$addr) {
            die("Invalid address");
        }

        $address_display = htmlspecialchars("{$addr['label']}, {$addr['full_address']}, {$addr['city']}, {$addr['postal_code']}");

    } else {
        die("No valid address selected.");
    }
} else {
    // No POST request — probably page first load or direct visit without data
    die("No address information received.");
}

// Fetch cart items for the user
$query = "
    SELECT m.name, m.price, c.quantity
    FROM cart c
    JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
$delivery_fee = 96;
$gst = round($total * 0.18, 2);
$grand_total = $total + $delivery_fee + $gst;

// Handle payment submission
if (isset($_POST['payment_method'])) {
    // Process payment, save order, etc.
    ?>
    <script>
        alert('Your order is placed!');
        window.location.href = 'thankyou.php';
    </script>
    <?php
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Payment - Food App</title>
<style>
    body { font-family: Arial, sans-serif; background:#f3f4f6; padding: 40px; }
    .container { max-width: 600px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #f97316; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    .payment-options { margin-top: 10px; }
    .payment-options input { margin-right: 10px; }
    .order-summary { background: #f9fafb; padding: 15px; margin-top: 20px; }
    .order-summary div { margin-bottom: 8px; }
    button { margin-top: 20px; background: #f97316; color: white; border: none; padding: 12px; width: 100%; font-size: 16px; cursor: pointer; }
    button:hover { background: #ea580c; }
</style>
</head>
<body>

<div class="container">
    <h2>Confirm Payment</h2>

    <p><strong>User:</strong> <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</p>
    <p><strong>Delivery Address:</strong> <?= $address_display ?></p>

    <div class="order-summary">
        <h3>Order Summary</h3>
        <?php foreach ($items as $item): ?>
            <div><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> = ₹<?= $item['price'] * $item['quantity'] ?></div>
        <?php endforeach; ?>
        <div>Subtotal: ₹<?= $total ?></div>
        <div>Delivery Fee: ₹<?= $delivery_fee ?></div>
        <div>GST (18%): ₹<?= $gst ?></div>
        <div><strong>Total: ₹<?= $grand_total ?></strong></div>
    </div>

    <form method="POST">
        <label>Select Payment Method:</label>
        <div class="payment-options">
            <input type="radio" id="upi" name="payment_method" value="UPI" required />
            <label for="upi">UPI</label><br/>

            <input type="radio" id="netbanking" name="payment_method" value="Netbanking" />
            <label for="netbanking">Netbanking</label><br/>

            <input type="radio" id="card" name="payment_method" value="Card" />
            <label for="card">Card</label><br/>

            <input type="radio" id="wallet" name="payment_method" value="Wallet" />
            <label for="wallet">Wallet</label>
        </div>

        <button type="submit">Pay ₹<?= $grand_total ?></button>
    </form>
</div>

</body>
</html>

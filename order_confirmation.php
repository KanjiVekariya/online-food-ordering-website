<?php
session_start();
include 'config.php';

// Check user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check required session data is set
if (!isset($_SESSION['payment_method']) || !isset($_SESSION['address_id'])) {
    header("Location: checkout.php"); // or wherever your checkout form is
    exit;
}

$payment_method = $_SESSION['payment_method'];
$address_id = $_SESSION['address_id'] ?? null;
$new_address = $_SESSION['new_address'] ?? null; // optional if user entered new address

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'food_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Fetch delivery address: if new address entered, use that, else fetch from DB
if ($new_address) {
    $address = $new_address; // array with keys: label, full_address, city, postal_code
} else {
    $stmt = $conn->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $addressResult = $stmt->get_result();
    $address = $addressResult->fetch_assoc();
}

// Fetch cart items
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
$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

$delivery_fee = 96;
$gst = round($subtotal * 0.18, 2);
$grand_total = $subtotal + $delivery_fee + $gst;

// Map payment method code to readable text
$payment_methods_map = [
    'upi' => 'UPI',
    'card' => 'Credit/Debit Card',
    'wallet' => 'Wallet',
    'netbanking' => 'Netbanking',
    'cod' => 'Cash on Delivery (COD)'
];
$payment_method_text = $payment_methods_map[$payment_method] ?? 'Unknown';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Confirmation - DailyBite</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 40px 20px;
            max-width: 700px;
            margin: auto;
            color: #333;
        }
        h1 {
            color: #f97316;
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            background: white;
            padding: 20px 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section h2 {
            border-left: 5px solid #f97316;
            padding-left: 10px;
            margin-top: 0;
            margin-bottom: 15px;
            color: #f97316;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f97316;
            color: white;
        }
        .total-row {
            font-weight: bold;
            background: #fff4e6;
        }
        .summary {
            text-align: right;
            margin-top: 15px;
            font-size: 1.1em;
        }
        .btn-confirm {
            display: block;
            width: 100%;
            background: #f97316;
            color: white;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 25px;
            text-align: center;
        }
        .btn-confirm:hover {
            background: #ea580c;
        }
    </style>
</head>
<body>

<h1>Confirm Your Order</h1>

<div class="section">
    <h2>Account Info</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
</div>

<div class="section">
    <h2>Delivery Address</h2>
    <p><strong>Label:</strong> <?= htmlspecialchars($address['label'] ?? 'N/A') ?></p>
    <p><?= nl2br(htmlspecialchars($address['full_address'] ?? '')) ?></p>
    <p><?= htmlspecialchars($address['city'] ?? '') ?> - <?= htmlspecialchars($address['postal_code'] ?? '') ?></p>
</div>

<div class="section">
    <h2>Order Details</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price (₹)</th>
                <th>Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">Subtotal</td>
                <td><?= number_format($subtotal, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3">Delivery Fee</td>
                <td><?= number_format($delivery_fee, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3">GST (18%)</td>
                <td><?= number_format($gst, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3"><strong>Total Payable</strong></td>
                <td><strong><?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h2>Payment Method</h2>
    <p><strong><?= htmlspecialchars($payment_method_text) ?></strong></p>
</div>

<form action="finalize_order.php" method="POST">
    <!-- Optionally include hidden inputs with order/payment info -->
    <button type="submit" class="btn-confirm">Confirm Payment & Place Order</button>
</form>

</body>
</html>

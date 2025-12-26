<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'food_app';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$userStmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Check if user has saved addresses
$checkAddrStmt = $conn->prepare("SELECT address_id FROM addresses WHERE user_id = ? LIMIT 1");
$checkAddrStmt->bind_param("i", $user_id);
$checkAddrStmt->execute();
$addrResult = $checkAddrStmt->get_result();

$address_id = $_POST['address_id'] ?? null;
$new_address_provided = isset($_POST['new_full_address']) && trim($_POST['new_full_address']) !== '';

function insertAddress($conn, $user_id, $label, $full_address, $city, $postal_code) {
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, label, full_address, city, postal_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $label, $full_address, $city, $postal_code);
    if (!$stmt->execute()) {
        die("Failed to save address: " . $stmt->error);
    }
    return $conn->insert_id;
}

// Determine address to use
if ($addrResult->num_rows === 0) {
    // No saved address, require new address data
    $label = $_POST['new_label'] ?? 'New Address';
    $full_address = trim($_POST['new_full_address'] ?? '');
    $city = trim($_POST['new_city'] ?? '');
    $postal_code = trim($_POST['new_postal_code'] ?? '');

    if (!$full_address || !$city || !$postal_code) {
        die("Please provide all required address fields.");
    }

    $address_id = insertAddress($conn, $user_id, $label, $full_address, $city, $postal_code);
    $address = ['label' => $label, 'full_address' => $full_address, 'city' => $city, 'postal_code' => $postal_code];
} else {
    // User has saved addresses
    if ($address_id === 'new' && $new_address_provided) {
        // Insert new address and use it
        $label = $_POST['new_label'] ?? 'New Address';
        $full_address = trim($_POST['new_full_address']);
        $city = trim($_POST['new_city']);
        $postal_code = trim($_POST['new_postal_code']);

        if (!$full_address || !$city || !$postal_code) {
            die("Please provide all required address fields.");
        }

        $address_id = insertAddress($conn, $user_id, $label, $full_address, $city, $postal_code);
        $address = ['label' => $label, 'full_address' => $full_address, 'city' => $city, 'postal_code' => $postal_code];
    } elseif ($address_id) {
        // Fetch selected address
        $addrStmt = $conn->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE address_id = ? AND user_id = ?");
        $addrStmt->bind_param("ii", $address_id, $user_id);
        $addrStmt->execute();
        $address = $addrStmt->get_result()->fetch_assoc();

        if (!$address) {
            die("Invalid address selected.");
        }
    } else {
        // Fallback to first saved address
        $addrStmt = $conn->prepare("SELECT address_id, label, full_address, city, postal_code FROM addresses WHERE user_id = ? LIMIT 1");
        $addrStmt->bind_param("i", $user_id);
        $addrStmt->execute();
        $address = $addrStmt->get_result()->fetch_assoc();
        $address_id = $address['address_id'] ?? null;

        if (!$address) {
            die("No saved address found.");
        }
    }
}

// Fetch cart items
$cartQuery = "
    SELECT m.item_id, m.name, m.price, c.quantity
    FROM cart c
    JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = ?
";
$cartStmt = $conn->prepare($cartQuery);
$cartStmt->bind_param("i", $user_id);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();

$items = [];
$subtotal = 0.0;
while ($row = $cartResult->fetch_assoc()) {
    $items[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

if (empty($items)) {
    die("Your cart is empty.");
}

$delivery_fee = 96.00;
$gst = round($subtotal * 0.18, 2);
$grand_total = $subtotal + $delivery_fee + $gst;

// Payment methods for the form
$payment_methods = [
    'upi' => 'UPI',
    'netbanking' => 'Netbanking',
    'card' => 'Credit/Debit Card',
    'wallet' => 'Wallet'
];

// Validate payment method if posted
$payment_method = $_POST['payment_method'] ?? '';
if (!array_key_exists($payment_method, $payment_methods)) {
    $payment_method = '';
}

// Get restaurant_id from first cart item
$restQuery = "
    SELECT mi.restaurant_id
    FROM cart c
    JOIN menu_items mi ON c.item_id = mi.item_id
    WHERE c.user_id = ?
    LIMIT 1
";
$restStmt = $conn->prepare($restQuery);
$restStmt->bind_param("i", $user_id);
$restStmt->execute();
$restResult = $restStmt->get_result();

if ($restResult->num_rows === 0) {
    die("No restaurant found for your cart items.");
}

$restaurant_id = $restResult->fetch_assoc()['restaurant_id'];

// Handle order placement on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment_method) {
    // Insert order
    $orderStmt = $conn->prepare(
        "INSERT INTO orders (user_id, restaurant_id, address_id, total_price) VALUES (?, ?, ?, ?)"
    );
    $orderStmt->bind_param("iiid", $user_id, $restaurant_id, $address_id, $grand_total);
    if (!$orderStmt->execute()) {
        die("Failed to create order: " . $orderStmt->error);
    }
    $order_id = $orderStmt->insert_id;

    // Insert order items
    $itemStmt = $conn->prepare(
        "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)"
    );
    foreach ($items as $item) {
        $itemStmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
        $itemStmt->execute();
    }

    // Clear cart
    $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearStmt->bind_param("i", $user_id);
    $clearStmt->execute();

    // Redirect to thank you page or confirmation page
    header("Location: thankyou.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Confirm & Pay - DailyBite</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #f97316;
        }
        section {
            margin-bottom: 25px;
            border-left: 5px solid #f97316;
            padding-left: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #f97316;
        }
        .payment-methods label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-confirm {
            background-color: #f97316;
            color: white;
            border: none;
            padding: 14px 25px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 6px;
            margin-top: 15px;
        }
        .btn-confirm:hover {
            background-color: #ea580c;
        }
        .info-row {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<h1>Confirm Your Order & Payment</h1>

<section>
    <h2>Account Information</h2>
    <p class="info-row"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p class="info-row"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
</section>

<section>
    <h2>Delivery Address</h2>
    <p><strong><?= htmlspecialchars($address['label']) ?></strong></p>
    <p><?= nl2br(htmlspecialchars($address['full_address'])) ?></p>
    <p><?= htmlspecialchars($address['city']) ?> - <?= htmlspecialchars($address['postal_code']) ?></p>
</section>

<section>
    <h2>Order Details</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th><th>Qty</th><th>Price (₹)</th><th>Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3">Subtotal</td>
                <td><?= number_format($subtotal, 2) ?></td>
            </tr>
            <tr>
                <td colspan="3">Delivery Fee</td>
                <td><?= number_format($delivery_fee, 2) ?></td>
            </tr>
            <tr>
                <td colspan="3">GST (18%)</td>
                <td><?= number_format($gst, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3">Total Payable</td>
                <td><?= number_format($grand_total, 2) ?></td>
            </tr>
        </tbody>
    </table>
</section>

<section>
    <h2>Choose Payment Method</h2>
    <form action="" method="POST" id="paymentForm">
        <input type="hidden" name="address_id" value="<?= htmlspecialchars($address_id) ?>">
        <input type="hidden" name="new_label" value="<?= htmlspecialchars($_POST['new_label'] ?? '') ?>">
        <input type="hidden" name="new_full_address" value="<?= htmlspecialchars($_POST['new_full_address'] ?? '') ?>">
        <input type="hidden" name="new_city" value="<?= htmlspecialchars($_POST['new_city'] ?? '') ?>">
        <input type="hidden" name="new_postal_code" value="<?= htmlspecialchars($_POST['new_postal_code'] ?? '') ?>">

        <?php foreach ($payment_methods as $key => $label): ?>
            <label>
                <input type="radio" name="payment_method" value="<?= $key ?>" required
                    <?= $payment_method === $key ? 'checked' : '' ?>>
                <?= $label ?>
            </label>
        <?php endforeach; ?>

        <button type="submit" class="btn-confirm">Confirm Payment & Place Order</button>
		





    </form>
</section>

</body>
</html>

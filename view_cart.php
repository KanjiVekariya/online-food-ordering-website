<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
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

// AJAX handler for quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
    $item_name = $_POST['item_name'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);

    // Get item_id and price for item_name
    $itemStmt = $conn->prepare("SELECT item_id, price FROM menu_items WHERE name = ?");
    $itemStmt->bind_param("s", $item_name);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();
    $item = $itemResult->fetch_assoc();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    $item_id = $item['item_id'];

    if ($quantity > 0) {
        // Update quantity in cart or insert if not exists
        $checkStmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?");
        $checkStmt->bind_param("ii", $user_id, $item_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?");
            $updateStmt->bind_param("iii", $quantity, $user_id, $item_id);
            $updateStmt->execute();
        } else {
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $user_id, $item_id, $quantity);
            $insertStmt->execute();
        }
    } else {
        // Quantity 0 means remove item from cart
        $deleteStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND item_id = ?");
        $deleteStmt->bind_param("ii", $user_id, $item_id);
        $deleteStmt->execute();
    }

    // Recalculate totals and fetch updated cart
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

    if ($subtotal > 0) {
        $delivery_fee = 96;
        $gst = round($subtotal * 0.18, 2);
    } else {
        $delivery_fee = 0;
        $gst = 0;
    }

    $grand_total = $subtotal + $delivery_fee + $gst;

    echo json_encode([
        'success' => true,
        'items' => $items,
        'subtotal' => $subtotal,
        'delivery_fee' => $delivery_fee,
        'gst' => $gst,
        'grand_total' => $grand_total
    ]);
    exit;
}

// Fetch user details
$userStmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

// Fetch cart for initial page load
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

if ($total > 0) {
    $delivery_fee = 96;
    $gst = round($total * 0.18, 2);
} else {
    $delivery_fee = 0;
    $gst = 0;
}

$grand_total = $total + $delivery_fee + $gst;

// Fetch addresses
$addressStmt = $conn->prepare("SELECT address_id, label, full_address, city, postal_code FROM addresses WHERE user_id = ?");
$addressStmt->bind_param("i", $user_id);
$addressStmt->execute();
$addressResult = $addressStmt->get_result();

$addresses = [];
while ($addr = $addressResult->fetch_assoc()) {
    $addresses[] = $addr;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Checkout - Food App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            display: flex;
            gap: 20px;
            padding-top: 2rem;
        }
        .left, .right {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .left {
            flex: 1;
        }
        .right {
            width: 350px;
        }
        .step {
            margin-bottom: 30px;
            border-left: 4px solid #AF3E3E;
            padding-left: 15px;
        }
        .step h3 {
            margin: 0 0 10px;
            color: #333;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #444;
            align-items: center;
        }
        .item-row.total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }
        .btn1 {
            background: #AF3E3E;
            color: #fff;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 15px;
        }
        .btn1:hover:not(:disabled) {
            background: #8a2d2d;
        }
        .btn1:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .quantity {
            padding: 2px 8px;
            font-size: 16px;
            color: #AF3E3E;
            background-color: white;
            border: 1px solid lightgrey;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
        }
        .quantity:active {
            background-color: #fcdaca;
        }
        .item-name-qty {
            flex: 1;
            margin: 0 10px;
        }
        input:not([type="button"]):not([type="submit"]):not([type="reset"]),
        textarea,
        select {
            width: 100%;
            padding: 15px 12px;
            font-size: 16px;
            border: 1.5px solid #ccc;
            background-color: #fff;
            color: #333;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
            outline-offset: 2px;
            margin-bottom: 15px;
        }
        input:not([type="button"]):not([type="submit"]):not([type="reset"]):focus,
        textarea:focus,
        select:focus {
            border-color: #f97316;
            box-shadow: 0 0 5px rgba(249, 115, 22, 0.5);
            outline: none;
        }
        input::placeholder,
        textarea::placeholder {
            color: #999;
            font-style: italic;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input:disabled,
        textarea:disabled,
        select:disabled {
            background-color: #f0f0f0;
            color: #999;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<?php include 'utils/navbar.php';?>
<form action="place_order.php" method="POST" id="orderForm">
<div class="container">

    <!-- Left Section -->
    <div class="left">
        <div class="step">
            <h3>1. Account</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="step">
            <h3>2. Delivery Address</h3>

            <?php if (count($addresses) > 0): ?>
                <label for="address_id"><strong>Select Delivery Address</strong></label><br>
                <select name="address_id" id="address_id" required>
                    <option value="" disabled selected>-- Select an address --</option>
                    <?php foreach ($addresses as $addr): ?>
                        <option value="<?= $addr['address_id'] ?>">
                            <?= htmlspecialchars($addr['label'] ?: 'Address') ?> - <?= htmlspecialchars($addr['full_address']) ?>, <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['postal_code']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="new">Add New Address</option>
                </select>

                <div id="new-address-form" style="display:none; margin-top:15px;">
                    <label>Label (optional)</label><br>
                    <input type="text" name="new_label" disabled /><br><br>

                    <label>Full Address *</label><br>
                    <textarea name="new_full_address" disabled></textarea><br><br>

                    <label>City *</label><br>
                    <input type="text" name="new_city" disabled required /><br><br>

                    <label>Postal Code *</label><br>
                    <input type="text" name="new_postal_code" disabled required />
                </div>

            <?php else: ?>
                <!-- No addresses saved, show new address form enabled by default -->
                <p><em>No saved addresses found. Please add your delivery address.</em></p>
                <label>Label (optional)</label><br>
                <input type="text" name="new_label" placeholder="e.g. Home, Office" /><br><br>

                <label>Full Address *</label><br>
                <textarea name="new_full_address" placeholder="Enter full address" required></textarea><br><br>

                <label>City *</label><br>
                <input type="text" name="new_city" required /><br><br>

                <label>Postal Code *</label><br>
                <input type="text" name="new_postal_code" required />
            <?php endif; ?>
        </div>
        <div class="step">
            <h3>3. Payment</h3>
            <p><em>Proceed to pay after confirming cart.</em></p>
        </div>
    </div>

    <!-- Right Section -->
    <div class="right">
        <h3>Your Cart</h3>
        <?php foreach ($items as $item): ?>
            <div class="item-row" data-item-name="<?= htmlspecialchars($item['name']) ?>">
                <div>
                    <button type="button" class="quantity" data-action="decrease">-</button>
                    <span class="item-name-qty"><?= htmlspecialchars($item['name']) ?> x <span class="quantity-value"><?= $item['quantity'] ?></span></span>
                    <button type="button" class="quantity" data-action="increase">+</button>
                </div>
                <div>
                    ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="item-row total">
            <div>Subtotal</div>
            <div id="subtotal">₹<?= number_format($total, 2) ?></div>
        </div>
        <div class="item-row total">
            <div>Delivery Fee</div>
            <div id="delivery_fee">₹<?= $delivery_fee > 0 ? '96.00' : '0.00' ?></div>
        </div>
        <div class="item-row total">
            <div>GST (18%)</div>
            <div id="gst">₹<?= number_format($gst, 2) ?></div>
        </div>
        <div class="item-row total" style="font-size: 1.2em; color: #AF3E3E;">
            <div>Grand Total</div>
            <div id="grand_total">₹<?= number_format($grand_total, 2) ?></div>
        </div>

        <button type="submit" class="btn1" id="payBtn" <?= $total == 0 ? 'disabled' : '' ?>>Place Order</button>
    </div>
</div>
</form>

<script>
    // Handle address dropdown toggle for new address form
    const addressSelect = document.getElementById('address_id');
    if (addressSelect) {
        addressSelect.addEventListener('change', () => {
            const newAddressForm = document.getElementById('new-address-form');
            const isNew = addressSelect.value === 'new';
            if (newAddressForm) {
                newAddressForm.style.display = isNew ? 'block' : 'none';
                // Enable/disable new address inputs accordingly
                newAddressForm.querySelectorAll('input, textarea').forEach(el => {
                    el.disabled = !isNew;
                    if (!isNew) el.value = '';
                });
            }
        });
    }

    // Quantity buttons event listener
    document.querySelectorAll('.quantity').forEach(button => {
        button.addEventListener('click', () => {
            const action = button.getAttribute('data-action');
            const itemRow = button.closest('.item-row');
            const itemName = itemRow.getAttribute('data-item-name');
            const quantitySpan = itemRow.querySelector('.quantity-value');
            let quantity = parseInt(quantitySpan.textContent);

            if (action === 'increase') {
                quantity++;
            } else if (action === 'decrease') {
                quantity = Math.max(0, quantity - 1); // allow zero to remove item
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    ajax_update: '1',
                    item_name: itemName,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (quantity === 0) {
                        // Remove item row from DOM
                        itemRow.remove();
                    } else {
                        // Update quantity and price for this item
                        quantitySpan.textContent = quantity;
                        const priceDiv = itemRow.querySelector('div:last-child');
                        const item = data.items.find(i => i.name === itemName);
                        if (item) {
                            priceDiv.textContent = `₹${(item.price * item.quantity).toFixed(2)}`;
                        }
                    }

                    // Update totals
                    document.getElementById('subtotal').textContent = '₹' + data.subtotal.toFixed(2);
                    document.getElementById('delivery_fee').textContent = '₹' + (data.subtotal > 0 ? '96.00' : '0.00');
                    document.getElementById('gst').textContent = '₹' + data.gst.toFixed(2);
                    document.getElementById('grand_total').textContent = '₹' + data.grand_total.toFixed(2);

                    // Disable pay button if cart empty
                    document.getElementById('payBtn').disabled = (data.subtotal === 0);
                } else {
                    alert('Failed to update cart.');
                }
            })
            .catch(err => {
                alert('Error updating cart.');
                console.error(err);
            });
        });
    });
</script>
</body>
</html>

<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$userstmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$userstmt->execute([$user_id]);
$user = $userstmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['name'] : 'Guest';

$message = '';

// Handle cancelling individual item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_item_id'])) {
    $cancel_order_item_id = (int)$_POST['cancel_order_item_id'];

    // Check if this order item belongs to a valid order of this user and is cancellable
    $checkItemStmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.status, oi.restaurant_canceled, o.user_id
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ? AND o.user_id = ?
    ");
    $checkItemStmt->execute([$cancel_order_item_id, $user_id]);
    $itemCheck = $checkItemStmt->fetch(PDO::FETCH_ASSOC);

    if ($itemCheck) {
        if (!in_array($itemCheck['status'], ['cancelled', 'delivered']) && !$itemCheck['restaurant_canceled']) {
            $updateItemStmt = $pdo->prepare("UPDATE order_items SET status = 'cancelled', user_canceled = 1 WHERE order_item_id = ?");
            $updateItemStmt->execute([$cancel_order_item_id]);
            $message = "Item has been cancelled successfully.";
        } elseif ($itemCheck['status'] === 'cancelled') {
            $message = "This item has already been cancelled.";
        } elseif ($itemCheck['restaurant_canceled']) {
            $message = "This item has been cancelled by the restaurant.";
        } else {
            $message = "Item cannot be cancelled at this stage.";
        }
    } else {
        $message = "Invalid item or permission denied.";
    }
}

// Fetch user orders
$stmt = $pdo->prepare("
    SELECT o.order_id, o.placed_at, o.total_price
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.placed_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$order_ids = array_column($orders, 'order_id');

// Fetch user address (just one)
$addrStmt = $pdo->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE user_id = ? LIMIT 1");
$addrStmt->execute([$user_id]);
$address = $addrStmt->fetch(PDO::FETCH_ASSOC);

// Fetch order items with restaurant name and status
$items = [];
if (!empty($order_ids)) {
    $inOrderIds = str_repeat('?,', count($order_ids) - 1) . '?';
    $itemsStmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.order_id, m.name AS item_name, oi.quantity, oi.price, oi.status, oi.user_canceled, oi.restaurant_canceled,
               r.name AS restaurant_name
        FROM order_items oi
        JOIN menu_items m ON oi.item_id = m.item_id
        JOIN restaurants r ON m.restaurant_id = r.restaurant_id
        WHERE oi.order_id IN ($inOrderIds)
        ORDER BY oi.order_id
    ");
    $itemsStmt->execute($order_ids);
    $itemsRaw = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itemsRaw as $item) {
        $items[$item['order_id']][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>My Orders - <?= htmlspecialchars($user_name) ?></title>
<style>
    body { font-family: Arial, sans-serif; background: #f0f2f5;}
    h1 { 
	text-align: center;
	color: #AF3E3E;
	margin:1rem 0rem;
	}
    .message { max-width: 900px; margin: 0 auto 20px; padding: 15px; background-color: #e6ffed; border: 1px solid #22c55e; color: #166534; border-radius: 6px; font-weight: 600; text-align: center; }
    .order-card { max-width: 900px; margin: 0 auto 40px; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px #ccc; }
    .order-header { font-weight: bold; margin-bottom: 10px; }
    .order-details { font-size: 0.9em; color: #555; margin-bottom: 10px; }
    .items-list { list-style: none; padding-left: 0; color: #444; }
    .items-list li { margin-bottom: 12px; }
    .restaurant-name { font-style: italic; color: #666; font-size: 0.9em; margin-left: 10px; }
    .total-price { font-weight: 700; margin-top: 10px; color: #111; }
    .address-box { background: #f9fafb; padding: 10px; border-radius: 6px; font-size: 0.9em; color: #555; margin-top: 10px; }

    .cancel-item-btn {
        margin-left: 15px;
        padding: 4px 8px;
        background: #ef4444;
        border: none;
        border-radius: 4px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: 0.85em;
    }
    .cancel-item-btn:hover {
        background: #b91c1c;
    }

    .status-label {
        display: inline-block;
        padding: 4px 10px;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 4px;
        margin-left: 10px;
        color: white;
        text-transform: capitalize;
    }

    .status-pending { background: #fbbf24; }
    .status-preparing { background: #3b82f6; }
    .status-on_the_way { background: #0ea5e9; }
    .status-delivered { background: #22c55e; }
    .status-cancelled { background: #ef4444; }
</style>
</head>
<body>
<?php include 'utils/navbar.php';?>
<div class="orders-container">
    <h1>Your Orders, <?= htmlspecialchars($user_name) ?></h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p style="text-align:center; color:#777; font-size:1.1em;">You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">Order #<?= $order['order_id'] ?></div>
                <div class="order-details"><strong>Placed at:</strong> <?= date('d M Y, H:i', strtotime($order['placed_at'])) ?></div>

                <ul class="items-list">
                    <?php foreach ($items[$order['order_id']] as $item): ?>
                        <li>
                            <strong><?= htmlspecialchars($item['item_name']) ?></strong> × <?= $item['quantity'] ?> — ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            <span class="restaurant-name">(<?= htmlspecialchars($item['restaurant_name']) ?>)</span>
                            <span class="status-label status-<?= htmlspecialchars($item['status']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $item['status'])) ?>
                            </span>

                            <?php if (!in_array($item['status'], ['cancelled', 'delivered']) && !$item['restaurant_canceled']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this item?');">
                                    <input type="hidden" name="cancel_order_item_id" value="<?= $item['order_item_id'] ?>" />
                                    <button type="submit" class="cancel-item-btn">Cancel</button>
                                </form>
                            <?php elseif ($item['restaurant_canceled']): ?>
                                <span style="color: #b91c1c; font-weight:600;">[Cancelled by Restaurant]</span>
                            <?php elseif ($item['status'] === 'cancelled'): ?>
                                <span style="color: #ef4444; font-weight:600;">[Cancelled]</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="total-price">Total: ₹<?= number_format($order['total_price'], 2) ?></div>

                <?php if ($address): ?>
                    <div class="address-box">
                        <strong>Delivery Address:</strong><br>
                        <?= htmlspecialchars($address['label']) ?><br>
                        <?= htmlspecialchars($address['full_address']) ?><br>
                        <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const msgDiv = document.querySelector('.message');
    if (msgDiv) {
      setTimeout(() => {
        msgDiv.style.transition = 'opacity 0.5s ease';
        msgDiv.style.opacity = '0';
        setTimeout(() => {
          msgDiv.remove();
        }, 500);
      }, 3000);
    }
  });
</script>
</body>
</html>

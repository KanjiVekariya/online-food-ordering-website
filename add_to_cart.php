<?php
session_start();
header('Content-Type: application/json'); // JSON response header

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add items.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate POST data
if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid item.']);
    exit;
}

$item_id = intval($_POST['item_id']);

$conn = new mysqli('localhost', 'root', '', 'food_app');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Check if item exists and is available
$stmt = $conn->prepare("SELECT is_available FROM menu_items WHERE item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Item does not exist.']);
    exit;
}

$item = $result->fetch_assoc();

if (!$item['is_available']) {
    echo json_encode(['status' => 'error', 'message' => 'This item is currently unavailable.']);
    exit;
}

// Check if item is already in cart
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?");
$stmt->bind_param("ii", $user_id, $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Item exists in cart, update quantity (+1)
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + 1;

    $stmt_update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?");
    $stmt_update->bind_param("iii", $new_quantity, $user_id, $item_id);
    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update cart.']);
    }
} else {
    // Item not in cart, insert new row
    $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, 1)");
    $stmt_insert->bind_param("ii", $user_id, $item_id);

    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item to cart.']);
    }
}
?>
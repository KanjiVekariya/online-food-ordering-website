<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'];
  $assigned_order_id = $_POST['assigned_order_id'] ?: null;
  $is_available = $_POST['is_available'];

  $stmt = $pdo->prepare("INSERT INTO delivery_staff (user_id, assigned_order_id, is_available) VALUES (?, ?, ?)");
  $stmt->execute([$user_id, $assigned_order_id, $is_available]);

  header('Location: admin_delivery_staff.php');
  exit;
}
?>

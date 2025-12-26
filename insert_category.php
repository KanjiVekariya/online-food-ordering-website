<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
  $name = trim($_POST['name']);
  $restaurantIds = $_POST['restaurant_ids'] ?? [];

  // Insert category
  $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
  $stmt->execute([$name]);
  $categoryId = $pdo->lastInsertId();

  // Insert into pivot table
  if (!empty($restaurantIds)) {
    $pivot = $pdo->prepare("INSERT INTO restaurant_categories (restaurant_id, category_id) VALUES (?, ?)");
    foreach ($restaurantIds as $restId) {
      $pivot->execute([$restId, $categoryId]);
    }
  }

  header("Location: view_category.php");
  exit;
}

<?php
include 'config.php';

$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    echo "Invalid request.";
    exit;
}

$stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->execute([$category_id]);

header("Location: admin_categories.php");
exit;

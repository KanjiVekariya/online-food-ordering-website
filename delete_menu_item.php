<?php
// delete_menu_item.php
$host = '127.0.0.1';
$db   = 'food_app';
$user = 'root'; // change as needed
$pass = '';     // change as needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid item ID.");
    }

    $itemId = (int) $_GET['id'];

    // Delete the item
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $stmt->execute([$itemId]);

    header('Location: menu_items_view.php');
    exit;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $restaurant_id = $_POST['restaurant_id'];
    $category_id = $_POST['category_id'];

    // Image upload logic
    $targetDir = "uploads/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName; // Prevent name collisions

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // File uploaded, insert record
        $stmt = $pdo->prepare("INSERT INTO menu_items 
            (name, description, price, image_url, restaurant_id, category_id) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $price, $targetFile, $restaurant_id, $category_id]);

        header("Location: dashboard.php");
        exit;
    } else {
        echo "❌ Failed to upload image.";
    }
}
?>

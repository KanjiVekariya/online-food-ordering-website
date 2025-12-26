<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data with basic sanitization
    $restaurant_id = $_POST['restaurant_id'] ?? null;

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $open_time = $_POST['open_time'] ?? null;
    $close_time = $_POST['close_time'] ?? null;

    if (!$restaurant_id || !$name) {
        die('Restaurant ID and Name are required.');
    }

    // Directory where photos will be saved
    $uploadDir = 'uploads/';

    // Initialize photo path as null
    $photoPath = null;

    // Check if a new photo file is uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif','image/webp','image/avif'];
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (in_array($fileMimeType, $allowedMimeTypes)) {
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate a unique filename
            $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('rest_') . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $filePath)) {
                $photoPath = $filePath;
            } else {
                die('Error uploading the photo.');
            }
        } else {
            echo "<script>alert('Uploaded file is not a valid image.');</script>";

        }
    }

    try {
        if ($photoPath) {
            // Update with new photo
            $stmt = $pdo->prepare("UPDATE restaurants SET name = ?, description = ?, address = ?, phone = ?, open_time = ?, close_time = ?, photo = ? WHERE restaurant_id = ?");
            $stmt->execute([$name, $description, $address, $phone, $open_time, $close_time, $photoPath, $restaurant_id]);
        } else {
            // Update without changing photo
            $stmt = $pdo->prepare("UPDATE restaurants SET name = ?, description = ?, address = ?, phone = ?, open_time = ?, close_time = ? WHERE restaurant_id = ?");
            $stmt->execute([$name, $description, $address, $phone, $open_time, $close_time, $restaurant_id]);
        }

        // Redirect back to the page showing the restaurants (adjust URL as needed)
        header('Location: view_restaurants.php');
        exit;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    // If accessed directly without POST data, redirect or show error
    header('Location: view_restaurants.php');
    exit;
}
?>

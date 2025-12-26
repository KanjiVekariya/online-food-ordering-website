<?php
// edit_menu_item.php
$host = '127.0.0.1';
$db   = 'food_app';
$user = 'root'; // change if needed
$pass = '';     // change if needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid item ID.");
    }
    $itemId = (int) $_GET['id'];

    // Fetch categories for dropdown
    $catStmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $category_id = $_POST['category_id'] ?? null;

        // Basic validation
        if (empty($name) || !is_numeric($price) || !$category_id) {
            throw new Exception("Please provide valid name, price, and category.");
        }

        // First get current item to know old image path
        $stmt = $pdo->prepare("SELECT image_url FROM menu_items WHERE item_id = ?");
        $stmt->execute([$itemId]);
        $currentItem = $stmt->fetch();
        $currentImage = $currentItem['image_url'] ?? '';

        // Handle file upload
        $uploadDir = 'uploads/menu_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $newImagePath = $currentImage; // default to current image if no new upload

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];

            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif','image/avif','image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG, and GIF images are allowed.");
            }
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
                throw new Exception("Image size must be less than 2MB.");
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('menu_') . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception("Failed to upload image.");
            }

            // Optionally delete old image file if it exists and is different
            if ($currentImage && file_exists($currentImage) && $currentImage !== $destination) {
                @unlink($currentImage);
            }

            $newImagePath = $destination;
        }

        // Update DB record
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, is_available = ?, category_id = ?, image_url = ? WHERE item_id = ?");
        $stmt->execute([$name, $description, $price, $is_available, $category_id, $newImagePath, $itemId]);

        header('Location: menu_items_view.php?updated=1');
        exit;
    } else {
        // Load current data
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Menu item not found.");
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Menu Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Make sure your sidebar.php outputs a div with class 'sidebar' */
        .sidebar {
            width: 250px; /* adjust to your sidebar width */
            background-color: #f8f9fa;
            padding: 20px;
            box-sizing: border-box;
            border-right: 1px solid #ddd;
        }

        .content-wrapper {
            flex: 1; /* take the remaining space */
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* center horizontally */
            box-sizing: border-box;
        }

        form {
            max-width: 600px;
            width: 100%;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input[type="text"], textarea, input[type="number"], select {
            width: 100%;
            padding: 6px;
            margin-top: 4px;
            box-sizing: border-box;
        }

        input[type="checkbox"] {
            margin-top: 10px;
        }

        input[type="file"] {
            margin-top: 8px;
        }

        button {
            margin-top: 20px;
            padding: 10px 15px;
            background: #2980b9;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        button:hover {
            background: #2471a3;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #555;
        }

        a:hover {
            text-decoration: underline;
        }

        img.current-image {
            max-width: 150px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="content-wrapper">
            <h1>Edit Menu Item #<?= htmlspecialchars($itemId) ?></h1>

            <form method="POST" action="" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($item['name']) ?>" required />

                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="4"><?= htmlspecialchars($item['description']) ?></textarea>

                <label for="price">Price (₹):</label>
                <input type="number" name="price" id="price" step="0.01" value="<?= htmlspecialchars($item['price']) ?>" required />

                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"
                            <?= $category['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>
                    <input type="checkbox" name="is_available" <?= $item['is_available'] ? 'checked' : '' ?> />
                    Available
                </label>

                <label for="image">Image (leave empty to keep current):</label><br>
                <?php if ($item['image_url'] && file_exists($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Current Image" class="current-image" /><br>
                <?php endif; ?>
                <input type="file" name="image" id="image" accept="image/*" />

                <button type="submit">Save Changes</button>
            </form>

            <a href="menu_items_view.php">&larr; Back to Menu Items</a>
        </main>
    </div>
</body>
</html>

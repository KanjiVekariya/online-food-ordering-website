<?php
// Prevent caching so success message won't persist incorrectly
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'config.php';

// Fetch all categories for dropdown
$all_categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$selected_category_id = $_GET['category_id'] ?? null;
$category = null;
$associated_restaurants = [];
$all_restaurants = $pdo->query("SELECT restaurant_id, name FROM restaurants ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

if ($selected_category_id) {
    // Fetch category info
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$selected_category_id]);
    $category = $stmt->fetch();

    if ($category) {
        // Fetch associated restaurants
        $stmt = $pdo->prepare("SELECT restaurant_id FROM restaurant_categories WHERE category_id = ?");
        $stmt->execute([$selected_category_id]);
        $associated_restaurants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $restaurant_ids = $_POST['restaurant_ids'] ?? [];

    // Update category name
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
    $stmt->execute([$name, $category_id]);

    // Update associated restaurants:
    // 1. Delete old associations
    $stmt = $pdo->prepare("DELETE FROM restaurant_categories WHERE category_id = ?");
    $stmt->execute([$category_id]);

    // 2. Insert new associations
    if (!empty($restaurant_ids)) {
        $insert_stmt = $pdo->prepare("INSERT INTO restaurant_categories (category_id, restaurant_id) VALUES (?, ?)");
        foreach ($restaurant_ids as $rid) {
            $insert_stmt->execute([$category_id, $rid]);
        }
    }

    // Redirect to avoid form resubmission and show success message
    header("Location: edit_category.php?category_id=$category_id&success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            margin-left: 260px; /* Sidebar width */
            box-sizing: border-box;
            background-color: #f9f9f9;
            min-height: 100vh;
        }

        h1 {
            margin-bottom: 40px;
            text-align: center;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.06);
        }

        form {
            margin-top: 20px;
        }

        select,
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        fieldset {
            border: 1px solid #ccc;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #fff;
        }

        legend {
            font-weight: bold;
            padding: 0 5px;
        }

        .checkbox-list label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            user-select: none;
        }

        button {
            background: #2980b9;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #1f6391;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }

        label[for="category_id"] {
            margin-bottom: 5px;
            font-weight: bold;
            display: block;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<h1>Edit Category</h1>

<div class="form-container">

    <!-- Category Select Dropdown -->
    <form method="GET" action="">
        <label for="category_id">Select Category to Edit:</label>
        <select id="category_id" name="category_id" onchange="this.form.submit()" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($all_categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $selected_category_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selected_category_id && $category): ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="message" id="success-message">✅ Category updated successfully!</div>
            <script>
                setTimeout(() => {
                    const msg = document.getElementById('success-message');
                    if (msg) {
                        msg.style.display = 'none';
                        // Remove success param from URL to avoid repeated messages
                        const url = new URL(window.location);
                        url.searchParams.delete('success');
                        window.history.replaceState(null, '', url.toString());
                    }
                }, 3000);
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['category_id']) ?>">

            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>

            <fieldset>
                <legend>Associate Restaurants</legend>
                <div class="checkbox-list">
                    <?php foreach ($all_restaurants as $rest): ?>
                        <label>
                            <input
                                type="checkbox"
                                name="restaurant_ids[]"
                                value="<?= $rest['restaurant_id'] ?>"
                                <?= in_array($rest['restaurant_id'], $associated_restaurants) ? 'checked' : '' ?>
                            >
                            <?= htmlspecialchars($rest['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <button type="submit">Update Category</button>
        </form>

    <?php elseif ($selected_category_id): ?>
        <p style="color: red; font-weight: bold;">Category not found.</p>
    <?php endif; ?>

</div>

</body>
</html>

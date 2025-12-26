
<?php

// DB connection settings — update with your own credentials

$host = 'localhost';
$db   = 'food_app';
$user = 'root';        // change as needed
$pass = '';            // change as needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // SQL with JOIN to get category and restaurant names
    $sql = "SELECT m.item_id, m.name as item_name, m.description, m.price, m.image_url, m.is_available,
                   c.name AS category_name,
                   r.name AS restaurant_name
            FROM menu_items m
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN restaurants r ON m.restaurant_id = r.restaurant_id
            ORDER BY m.item_id ASC";

    $stmt = $pdo->query($sql);
    $menuItems = $stmt->fetchAll();

} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
?>

	

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Menu Items</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        margin: 0;
        padding: 0;
    }

    /* Content styling */
    .content {
		margin-left:20rem;
        flex: 1;
        padding: 15px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 16px;
        table-layout: fixed;
        word-wrap: break-word;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 6px 5px;
        vertical-align: top;
        text-align: left;
    }
	

    th {
        background-color: #2980b9;
        color: white;
        font-weight: 600;
    }
	td:nth-child(3) {
    width:20rem;
}
th:nth-child(3) {
    width:20rem;
}
td:nth-child(1) {
    width:3rem;
}
th:nth-child(1) {
    width:3rem;
}
    img {
        max-width: 60px;
        height: auto;
        display: block;
        border-radius: 4px;
    }
</style>

</head>
<body>

    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
<script>
    alert('Menu item updated successfully!');
</script>
<?php endif; ?>

    <div class="content">
        <h1>Menu Items</h1>
        <?php if (count($menuItems) > 0): ?>
            <table>
                <thead>
    <tr>
        <th>Item ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price (₹)</th>
        <th>Image</th>
        <th>Category</th>
        <th>Restaurant</th>
        <th>Available</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($menuItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['item_id']) ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td>
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" />
                <?php else: ?>
                    No image
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($item['restaurant_name'] ?? 'N/A') ?></td>
            <td><?= $item['is_available'] ? 'Yes' : 'No' ?></td>
            <td>
                <a href="edit_menu_item.php?id=<?= $item['item_id'] ?>" style="margin-right: 8px;">Edit</a>
                <a href="delete_menu_item.php?id=<?= $item['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');" style="color: red;">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        <?php else: ?>
            <p>No menu items found.</p>
        <?php endif; ?>
    </div>

</body>
</html>


<?php
include 'config.php';

// Fetch categories with all associated restaurants concatenated
$sql = "
  SELECT 
    c.category_id,
    c.name AS category_name,
    GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS restaurant_names
  FROM categories c
  LEFT JOIN restaurant_categories rc ON c.category_id = rc.category_id
  LEFT JOIN restaurants r ON rc.restaurant_id = r.restaurant_id
  GROUP BY c.category_id, c.name
  ORDER BY c.category_id ASC
";

$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Categories</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
    }

    .main {
      margin-left: 240px; /* Assuming sidebar width */
      padding: 40px;
      flex: 1;
      background: #f9f9f9;
      min-height: 100vh;
    }

    h1 {
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      border-radius: 6px;
      overflow: hidden;
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #2980b9;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .actions a {
      margin-right: 10px;
      text-decoration: none;
      color: #007BFF;
      font-weight: bold;
    }

    .actions a.delete {
      color: red;
    }

    .success-msg {
      background: #dff0d8;
      color: #3c763d;
      padding: 10px;
      border: 1px solid #d6e9c6;
      margin-bottom: 20px;
      border-radius: 5px;
      max-width: 600px;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <h1>View Categories</h1>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
      <div class="success-msg">✅ Category updated successfully!</div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Restaurants</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($categories) > 0): ?>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td><?= $cat['category_id'] ?></td>
              <td><?= htmlspecialchars($cat['category_name']) ?></td>
              <td><?= $cat['restaurant_names'] ? htmlspecialchars($cat['restaurant_names']) : '<em>None</em>' ?></td>
              <td class="actions">
                <a href="edit_category.php?category_id=<?= $cat['category_id'] ?>">Edit</a>
                <a href="delete_category.php?id=<?= $cat['category_id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No categories found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

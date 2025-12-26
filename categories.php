<?php include 'config.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Categories</title>
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
    }

    .sidebar {
      position: fixed;
      width: 220px;
      height: 100%;
      background: #333;
      color: #fff;
      padding: 20px;
      top: 0;
      left: 0;
    }

    .sidebar h2 {
      margin-bottom: 30px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 10px 0;
    }

    .main {
      margin-left: 240px;
      padding: 40px;
    }

    form input, form button {
      display: block;
      width: 100%;
      margin-bottom: 15px;
      padding: 10px;
      font-size: 16px;
    }

    fieldset {
      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 20px;
    }

    fieldset legend {
      font-weight: bold;
    }

    .message {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    table th, table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    table th {
      background-color: #f0f0f0;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <h1>Manage Categories</h1>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="message">Category added successfully!</div>
    <?php endif; ?>

    <form action="insert_category.php" method="POST">
      <input type="text" name="name" placeholder="Category Name" required>

      <fieldset>
        <legend>Select Restaurants</legend>
        <?php
          $restaurants = $pdo->query("SELECT restaurant_id, name FROM restaurants ORDER BY name")->fetchAll();
          foreach ($restaurants as $rest):
        ?>
          <label>
            <input type="checkbox" name="restaurant_ids[]" value="<?= htmlspecialchars($rest['restaurant_id']) ?>">
            <?= htmlspecialchars($rest['name']) ?>
          </label><br>
        <?php endforeach; ?>
      </fieldset>

      <button type="submit">Add Category</button>
    </form>

    <h2>Existing Categories</h2>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Category Name</th>
          <th>Associated Restaurants</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $stmt = $pdo->query("
            SELECT c.category_id, c.name AS category_name, 
                   GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS restaurant_names
            FROM categories c
            LEFT JOIN restaurant_categories rc ON c.category_id = rc.category_id
            LEFT JOIN restaurants r ON rc.restaurant_id = r.restaurant_id
            GROUP BY c.category_id, c.name
            ORDER BY c.name
          ");

          $index = 1;
          while ($cat = $stmt->fetch()):
            $categoryName = htmlspecialchars($cat['category_name']);
            $restaurantNames = $cat['restaurant_names'] ? htmlspecialchars($cat['restaurant_names']) : '<em>None</em>';
        ?>
          <tr>
            <td><?= $index++ ?></td>
            <td><?= $categoryName ?></td>
            <td><?= $restaurantNames ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

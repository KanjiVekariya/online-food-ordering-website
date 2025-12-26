<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Categories</title>
  <style>
    /* Reset and base */
    
    body {
      margin: 0;
      background: #fafafa;
      color: #333;
      line-height: 1.5;
    }

    

    /* Main content */
    .main {
      margin-left: 240px;
      padding: 2.5rem 3rem;
      max-width: 100%;
      margin-top: 20px;
    }
    h1, h2 {
      font-weight: 700;
      margin-bottom: 1rem;
      color: #2c3e50;
    }
    h2 {
      margin-top: 3rem;
    }

    /* Form styles */
    form {
      background: #fff;
      padding: 1.5rem 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgb(0 0 0 / 0.08);
      margin-bottom: 2rem;
    }

    input[type="text"],
    select {
      width: 100%;
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border: 1.5px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
      margin-bottom: 1.5rem;
      font-weight: 400;
    }
    input[type="text"]:focus,
    select:focus {
      border-color: #3498db;
      outline: none;
    }

    /* Restaurants grid for checkboxes */
    fieldset {
      border: none;
      margin-bottom: 1.5rem;
      padding: 0;
    }
    legend {
      font-weight: 600;
      margin-bottom: 1rem;
      color: #34495e;
      font-size: 1.1rem;
    }
    .restaurants-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 0.8rem 1rem;
    }
    .restaurants-grid label {
      display: flex;
      align-items: center;
      font-weight: 500;
      cursor: pointer;
      user-select: none;
      font-size: 0.95rem;
      color: #34495e;
    }
    .restaurants-grid input[type="checkbox"] {
      margin-right: 0.5rem;
      width: 16px;
      height: 16px;
      cursor: pointer;
    }

    /* Button */
    button {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 0.6rem 1.5rem;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      font-weight: 600;
      width: max-content;
	  
    }
    button:hover {
      background-color: #2980b9;
    }

    /* Success message */
    .message {
      background: #d4edda;
      color: #155724;
      padding: 0.8rem 1.2rem;
      margin-bottom: 1.5rem;
      border-radius: 6px;
      font-weight: 600;
      border: 1px solid #c3e6cb;
      max-width: 400px;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
      border-radius: 5px;
      overflow: hidden;
    }
    thead {
      background: #3498db;
      font-weight: 600;
      color: white;
    }
    th, td {
      text-align: left;
      padding: 0.7rem 1rem;
      font-size: 0.95rem;
      border-bottom: 1px solid #e1e8ed;
      vertical-align: middle;
    }
    tbody tr:hover {
      background: #f9faff;
    }
    a {
      color: #3498db;
      text-decoration: none;
      font-weight: 600;
    }
    a:hover {
      text-decoration: underline;
    }

    /* Responsive tweaks */
    @media (max-width: 600px) {
      .main {
        margin-left: 0;
        padding: 1.5rem;
      }
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding: 1rem 1rem 2rem;
        text-align: center;
      }
      .sidebar a {
        display: inline-block;
        margin: 0 0.7rem;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tbody tr {
        margin-bottom: 1.2rem;
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.1);
      }
      tbody td {
        padding: 0.3rem 0;
        border: none;
        position: relative;
        padding-left: 50%;
      }
      tbody td::before {
        position: absolute;
        top: 0.3rem;
        left: 1rem;
        width: 45%;
        white-space: nowrap;
        font-weight: 600;
        color: #34495e;
        content: attr(data-label);
      }
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <h1>Manage Categories</h1>

    <!-- Success Message -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="message">Category added successfully!</div>
    <?php endif; ?>

    <!-- Form to add new category -->
    <form action="insert_category.php" method="POST" novalidate>
      <input type="text" name="name" placeholder="Category Name" required autocomplete="off" />

      <fieldset>
        <legend>Select Restaurants</legend>
        <div class="restaurants-grid">
          <?php
            $restaurants = $pdo->query("SELECT restaurant_id, name FROM restaurants ORDER BY name")->fetchAll();
            foreach ($restaurants as $rest):
          ?>
            <label>
              <input type="checkbox" name="restaurant_ids[]" value="<?= htmlspecialchars($rest['restaurant_id']) ?>">
              <?= htmlspecialchars($rest['name']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <button type="submit">Add Category</button>
    </form>

    <h2>Existing Categories</h2>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Category Name</th>
          <th>Associated Restaurants</th>
          <th>Actions</th>
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
            <td data-label="No"><?= $index++ ?></td>
            <td data-label="Category Name"><?= $categoryName ?></td>
            <td data-label="Associated Restaurants"><?= $restaurantNames ?></td>
            <td data-label="Actions"><a href="edit_category.php?category_id=<?= $cat['category_id'] ?>">Edit</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>

  <script>
    // Auto-remove success message after 5 seconds
    const msg = document.querySelector('.message');
    if (msg) {
      setTimeout(() => {
        msg.style.transition = 'opacity 0.5s ease';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
      }, 5000);
    }
  </script>

</body>
</html>

<?php
include 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $address = $_POST['address'];
  $phone = $_POST['phone'];
  $open_time = $_POST['open_time'];
  $close_time = $_POST['close_time'];

  // Handle file upload
  $photo = null;
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['photo']['name']);
    $filePath = $uploadDir . $fileName;

    // Move uploaded file to the server
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
      $photo = $filePath; // Store file path
    }
  }

  // Insert restaurant details into the database
  $stmt = $pdo->prepare("INSERT INTO restaurants (name, description, address, phone, open_time, close_time, photo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$name, $description, $address, $phone, $open_time, $close_time, $photo]);

  // Get the last inserted restaurant ID
  $restaurant_id = $pdo->lastInsertId();

  // Insert selected categories into the restaurant_categories table
  if (isset($_POST['categories']) && !empty($_POST['categories'])) {
    $categories = $_POST['categories'];
    foreach ($categories as $category_id) {
      $stmt = $pdo->prepare("INSERT INTO restaurant_categories (restaurant_id, category_id) VALUES (?, ?)");
      $stmt->execute([$restaurant_id, $category_id]);
    }
  }

  // Redirect to avoid form resubmission
  header('Location: admin_restaurants.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Restaurants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f9f9f9;
      margin: 0;
      color: #333;
    }

    .main {
      margin-left: 35rem; /* matches sidebar width */
      padding: 40px 60px;
      max-width: 800px;
      background: #fff;
      min-height: 100vh;
      box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
      border-radius: 8px;
    }

    h1 {
      color: #222;
      margin-bottom: 24px;
      font-weight: 700;
    }

    form {
      margin-bottom: 40px;
    }

    form input[type="text"],
    form input[type="time"],
    form textarea,
    form input[type="file"] {
      width: 80%;
      padding: 12px 15px;
      margin-bottom: 20px;
      border: 1.5px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      transition: border-color 0.3s ease;
      resize: vertical;
    }

    form input[type="text"]:focus,
    form input[type="time"]:focus,
    form textarea:focus,
    form input[type="file"]:focus {
      outline: none;
      border-color: #5c9ded;
      box-shadow: 0 0 8px rgba(92, 157, 237, 0.4);
    }

    form button {
      background-color: #5c9ded;
      color: white;
      padding: 14px 0;
      border: none;
      border-radius: 6px;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.25s ease;
    }

    form button:hover {
      background-color: #3a74d3;
    }

    ul {
      list-style-type: none;
      padding-left: 0;
    }

    ul li {
      padding: 10px 15px;
      margin-bottom: 12px;
      background: #f1f5fa;
      border-radius: 6px;
      box-shadow: inset 1px 1px 3px #e1e7f0;
      font-weight: 600;
    }

    .restaurant-photo {
      max-width: 50px;
      max-height: 50px;
      object-fit: cover;
    }

    .category-checkboxes {
      margin-top: 20px;
    }

    @media (max-width: 900px) {
      .main {
        margin-left: 0;
        padding: 20px;
        max-width: 100%;
        border-radius: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <h1>Manage Restaurants</h1>

  <!-- Form to Add New Restaurant -->
  <form action="admin_restaurants.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Restaurant Name" required>
    <textarea name="description" placeholder="Description"></textarea>
    <textarea name="address" placeholder="Address"></textarea>
    <input type="text" name="phone" placeholder="Phone">
    <input type="time" name="open_time" placeholder="Open Time">
    <input type="time" name="close_time" placeholder="Close Time">
    <input type="file" name="photo" class="form-control" accept="image/*">

    <!-- Categories Selection -->
    <div class="category-checkboxes">
      <h5>Select Categories</h5>
      <?php
      // Fetch all categories
      $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
      while ($category = $stmt->fetch()) {
          echo '<div class="form-check">';
          echo '<input type="checkbox" name="categories[]" value="' . $category['category_id'] . '" class="form-check-input">';
          echo '<label class="form-check-label">' . htmlspecialchars($category['name']) . '</label>';
          echo '</div>';
      }
      ?>
    </div>

    
	<button type="submit" class="btn btn-warning ">Add Restaurant</button>
  </form>

  <h2>Existing Restaurants</h2>
  <ul>
    <?php
    // Fetch and display restaurants with their images and categories
    $stmt = $pdo->query("SELECT * FROM restaurants");

    while ($res = $stmt->fetch()) {
      echo "<li>";
      echo "<strong>" . htmlspecialchars($res['name']) . "</strong> - " . htmlspecialchars($res['phone']) . "<br>";

      // Display photo if available
      if ($res['photo']) {
        echo "<img src='" . htmlspecialchars($res['photo']) . "' class='restaurant-photo' alt='Restaurant Photo'><br>";
      }

      // Fetch and display associated categories
      $stmt_categories = $pdo->prepare("SELECT c.name FROM categories c 
                                        JOIN restaurant_categories rc ON c.category_id = rc.category_id 
                                        WHERE rc.restaurant_id = ?");
      $stmt_categories->execute([$res['restaurant_id']]);
      $categories = $stmt_categories->fetchAll();
      
      if ($categories) {
          echo "<strong>Categories:</strong> ";
          foreach ($categories as $category) {
              echo htmlspecialchars($category['name']) . " ";
          }
      }
      echo "</li>";
    }
    ?>
  </ul>
</div>

</body>
</html>

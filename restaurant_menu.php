<?php 
include 'config.php';

// Get restaurant_id from URL, sanitize and validate
$restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;

if ($restaurant_id <= 0) {
    echo "Invalid restaurant ID.";
    exit;
}

// Fetch restaurant info + categories
$sql_restaurant = "
  SELECT r.restaurant_id, r.name, r.description, r.photo,
         GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
  FROM restaurants r
  LEFT JOIN restaurant_categories rc ON r.restaurant_id = rc.restaurant_id
  LEFT JOIN categories c ON rc.category_id = c.category_id
  WHERE r.restaurant_id = :restaurant_id
  GROUP BY r.restaurant_id
  LIMIT 1
";
$stmt = $pdo->prepare($sql_restaurant);
$stmt->execute(['restaurant_id' => $restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    echo "Restaurant not found.";
    exit;
}

// Fetch menu items
$sql_menu = "
  SELECT *
  FROM menu_items
  WHERE restaurant_id = :restaurant_id AND is_available = 1
  ORDER BY name ASC
";
$stmt_menu = $pdo->prepare($sql_menu);
$stmt_menu->execute(['restaurant_id' => $restaurant_id]);
$menu_items = $stmt_menu->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Menu - <?php echo htmlspecialchars($restaurant['name']); ?> | FoodZone</title>
  <link rel="stylesheet" href="assets/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #fafafa;
    }
    .restaurant-header {
      max-width: 900px;
      margin: 30px auto 50px;
      background: white;
      padding: 20px;
      border-radius: 12px;
      //box-shadow: 0 2px 10px rgb(0 0 0 / 0.1);
      display: flex;
      gap: 20px;
      align-items: center;
    }
    .restaurant-header img {
      width: 180px;
      height: 140px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
    }
    .restaurant-info {
      flex-grow: 1;
    }
    .restaurant-info h1 {
      margin: 0 0 10px;
      font-size: 2rem;
    }
    .restaurant-info p.description {
      color: #555;
      font-size: 1rem;
      margin-bottom: 8px;
    }
    .restaurant-info p.categories {
      color: #ff6f61;
      font-weight: 600;
      font-size: 0.95rem;
      margin: 0;
    }

    /* Vertical list container */
    main#menu {
      max-width: 700px;
      margin: 0 auto 60px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Rectangular menu item card */
    .menu-item {
      display: flex;
      background: white;
      border-radius: 12px;
      //box-shadow: 0 2px 10px rgb(0 0 0 / 0.1);
      overflow: hidden;
      cursor: default;
      transition: box-shadow 0.3s ease;
    }
    .menu-item:hover {
      box-shadow: 0 4px 18px rgb(0 0 0 / 0.15);
    }

    .menu-item img {
      width: 220px;
      height: 150px;
      object-fit: cover;
      flex-shrink: 0;
    }

    .menu-item-content {
      padding: 15px 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex-grow: 1;
    }

    .menu-item-title {
      font-weight: 700;
      font-size: 1.4rem;
      margin: 0 0 8px 0;
	  text-align:right;
    }

    .menu-item-description {
      color: #555;
      font-size: 1rem;
      margin-bottom: 10px;
      flex-grow: 1;
    }

    .menu-item-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .menu-item-price {
      font-weight: 700;
      font-size: 1.2rem;
      color: #333;
    }

    .btn-order {
      background-color: #28a745;
      color: white;
      text-align: center;
      padding: 8px 15px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .btn-order:hover {
      background-color: #218838;
      color: white;
    }

    nav {
      background: #ff6f61;
      padding: 12px 30px;
      color: white;
      font-weight: 700;
      font-size: 1.3rem;
      text-align: center;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <nav>FoodZone 🍽️ - <?php echo htmlspecialchars($restaurant['name']); ?></nav>

  <section class="restaurant-header" aria-label="Restaurant Info">
    <?php 
      $photo = !empty($restaurant['photo']) ? $restaurant['photo'] : 'assets/restaurant_placeholder.jpg'; 
    ?>
    <!--<img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>"-->
    <div class="restaurant-info">
      <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
      <p class="description"><?php echo htmlspecialchars($restaurant['description']); ?></p>
      <p class="categories"><?php echo htmlspecialchars($restaurant['categories'] ?? 'No categories'); ?></p>
    </div>
  </section>

  <main id="menu" aria-label="Menu Items">
    <?php if (count($menu_items) > 0): ?>
      <?php foreach ($menu_items as $item): ?>
        <article class="menu-item">
          <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
          <div class="menu-item-content">
            <h2 class="menu-item-title"><?php echo htmlspecialchars($item['name']); ?></h2>
            <p class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
            <div class="menu-item-footer">
              <div class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></div>
              <a href="search_dishes.php?query=<?php echo urlencode($item['name']); ?>" class="btn-order">Order Now</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; font-size:1.2rem; color:#777;">No menu items available for this restaurant.</p>
    <?php endif; ?>
  </main>

  <footer style="text-align:center; margin: 40px 0; color:#aaa;">
    &copy; 2025 FoodZone
  </footer>

</body>
</html>

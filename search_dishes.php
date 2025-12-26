<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Search Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #fafafa;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
        Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
      color: #222;
    }
    .restaurant-block {
      margin-bottom: 40px;
    }
    .card {
      max-width: 700px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
      background: #fff;
    }
    .card-img-top {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 8px 0 0 8px;
      display: block;
    }
    .col-md-4 {
      max-width: 220px;
      flex: 0 0 220px;
    }
    .card-body {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 1rem 1.5rem;
    }
    .card-title {
      font-weight: 600;
      font-size: 1.2rem;
      margin-bottom: 0.25rem;
      color: #111;
    }
    .card-text {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 1rem;
      line-height: 1.4;
    }
    .price {
      font-weight: 700;
      font-size: 1.1rem;
      color: #2c3e50;
      margin: 0;
    }
    .btn-success {
      background: transparent;
      border: 1.5px solid #2c3e50;
      color: #2c3e50;
      padding: 7px 22px;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
      font-size: 0.85rem;
    }
    .btn-success:hover {
      background: #2c3e50;
      color: #fff;
      border-color: #2c3e50;
      text-decoration: none;
    }
    .row.g-0.align-items-center {
      align-items: center;
    }
    .restaurant-block > h4 {
      font-weight: 700;
      margin-bottom: 0.25rem;
      color: #2c3e50;
    }
    .restaurant-block > p {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 1rem;
    }
    /* Popup styling */
    #cart-popup {
      display: none;
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #28a745;
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      z-index: 9999;
      font-weight: bold;
    }
    #cart-popup a {
      color: #d4edda;
      text-decoration: underline;
      margin-left: 10px;
    }
  </style>
</head>
<body>
<?php include 'utils/navbar.php'; ?>

<div class="container mt-5">
  <?php
  if (isset($_GET['query'])):
    $search = trim($_GET['query']);
  ?>
    <h2 class="mb-4">Restaurants Serving: <span style="color:#2c3e50;"><?= htmlspecialchars($search) ?></span></h2>

    <?php
    $stmt = $pdo->prepare("
      SELECT 
        r.restaurant_id,
        r.name AS restaurant_name,
        r.address,
        r.phone,
        m.item_id,
        m.name AS dish_name,
        m.description,
        m.price,
        m.image_url
      FROM menu_items m
      JOIN restaurants r ON m.restaurant_id = r.restaurant_id
      WHERE m.name LIKE :search
        AND m.is_available = 1
        AND r.is_open = 1
      ORDER BY r.restaurant_id
    ");
    $stmt->execute(['search' => '%' . $search . '%']);

    if ($stmt->rowCount() > 0):
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $row):
  ?>
        <div class="restaurant-block">
          <h4><?= htmlspecialchars($row['restaurant_name']) ?></h4>
          <p>
            <strong>Address:</strong> <?= htmlspecialchars($row['address']) ?><br>
            <strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?>
          </p>
          <div class="card mb-3">
            <div class="row g-0 align-items-center">
              <div class="col-md-4">
                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="Dish Image" />
              </div>
              <div class="col-md-8">
                <div class="card-body">
                  <div>
                    <h5 class="card-title"><?= htmlspecialchars($row['dish_name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <p class="price mb-0">₹<?= number_format($row['price'], 2) ?></p>
                    <form class="add-to-cart-form" data-item-name="<?= htmlspecialchars($row['dish_name']) ?>">
                      <input type="hidden" name="item_id" value="<?= htmlspecialchars($row['item_id']) ?>">
                      <input type="hidden" name="restaurant_id" value="<?= htmlspecialchars($row['restaurant_id']) ?>">
                      <button type="submit" class="btn btn-sm btn-success mt-2 px-4 fw-bold">ADD</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  <?php
      endforeach;
    else:
      echo "<p>No restaurants found serving <strong>" . htmlspecialchars($search) . "</strong>.</p>";
    endif;
  else:
    echo "<p>No search query provided.</p>";
  endif;
  ?>
</div>

<?php include 'utils/footer.php'; ?>

<div id="cart-popup"></div>

<script>
  document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const itemName = this.getAttribute('data-item-name');

      fetch('add_to_cart.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          showPopup(`${itemName} added to your cart! <a href="view_cart.php">Go to Cart</a>`);
        } else {
          alert(data.message || 'Failed to add item.');
        }
      })
      .catch(() => alert('Error adding item to cart.'));
    });
  });

  function showPopup(message) {
    const popup = document.getElementById('cart-popup');
    popup.innerHTML = message;
    popup.style.display = 'block';

    setTimeout(() => {
      popup.style.display = 'none';
      popup.innerHTML = '';
    }, 4000);
  }
</script>

</body>
</html>

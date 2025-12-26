<?php
$restaurant_id = intval($_GET['id']); // Sanitize input
$conn = new mysqli('localhost', 'root', '', 'food_app');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch restaurant details
$resQuery = $conn->query("SELECT * FROM restaurants WHERE restaurant_id = $restaurant_id");
$restaurant = $resQuery->fetch_assoc();

if (!$restaurant) {
    echo "<p>Restaurant not found.</p>";
    exit;
}

// Fetch menu items
$menuQuery = $conn->query("SELECT * FROM menu_items WHERE restaurant_id = $restaurant_id");
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  /* Simple popup style */
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
<?php include_once 'utils/navbar.php';?>
<div class="d-flex justify-content-center">
  <div class="container py-5" style="max-width: 900px;">
      <h1 class="mb-2 text-center"><?= htmlspecialchars($restaurant['name']) ?></h1>
      <p class="text-center"><?= htmlspecialchars($restaurant['description']) ?></p>
      <p class="text-center"><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>

      <h2 class="mt-5 mb-4 text-center">Menu</h2>
      <div class="row g-4 justify-content-center">
          <?php while ($item = $menuQuery->fetch_assoc()) { ?>
              <div class="col-9">
                <div class="card p-3 d-flex flex-row align-items-center justify-content-between" style="border-radius: 12px;">
                    <!-- Left: Text -->
                    <div class="flex-grow-1 pe-3">
                      <div class="d-flex align-items-center mb-1">
                        <span class="me-2" style="width: 16px; height: 16px; border: 1px solid green; border-radius: 2px;"></span>
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($item['name']) ?></h5>
                      </div>
                      <p class="mb-1 fw-bold">₹<?= number_format($item['price'], 2) ?></p>
                      <p class="mb-0 text-muted" style="font-size: 0.9rem; max-width: 90%;">
                        <?= htmlspecialchars(mb_strimwidth($item['description'], 0, 120, "...")) ?>
                      </p>
                    </div>
                    <!-- Right: Image & Button -->
                    <div class="d-flex flex-column align-items-center">
                      <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                          alt="<?= htmlspecialchars($item['name']) ?>" 
                          style="width: 120px; height: 100px; object-fit: cover; border-radius: 10px;">
                      
                      <form class="add-to-cart-form mt-2" data-item-name="<?= htmlspecialchars($item['name']) ?>">
                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant_id ?>">
                        <button type="submit" class="btn btn-sm btn-success px-4 fw-bold">ADD</button>
                      </form>

                    </div>
                </div>
              </div>
          <?php } ?>
      </div>
  </div>
</div>

<!-- Popup notification -->
<div id="cart-popup"></div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const itemName = this.getAttribute('data-item-name');

      fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
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

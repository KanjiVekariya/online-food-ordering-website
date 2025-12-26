<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 0) {
    header("Location: login_admin.php"); // Adjust name if using login_admin.php
    exit();
}


// Fetch last 5 orders with customer name
$stmt = $pdo->query("
  SELECT 
    o.order_id, 
    u.name AS customer, 
    r.name AS restaurant,
    o.order_status, 
    o.total_price,
    o.placed_at
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.user_id
  LEFT JOIN restaurants r ON o.restaurant_id = r.restaurant_id
  ORDER BY o.placed_at ASC
  LIMIT 5
");


// Fetch total orders count
$stmt1 = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt1->fetchColumn();

// Fetch total customers count
$stmt2 = $pdo->query("SELECT COUNT(*) FROM users");
$total_customers = $stmt2->fetchColumn();

// Fetch total restaurants count
$stmt3 = $pdo->query("SELECT COUNT(*) FROM restaurants");
$total_restaurants = $stmt3->fetchColumn();

$stmt3 = $pdo->query("SELECT COUNT(*) FROM menu_items");
$total_menu = $stmt3->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }

    .main-content {
      margin-left: 240px; /* same as sidebar width */
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    /* Adjust when sidebar is collapsed */
    .sidebar.collapsed ~ .main-content {
      margin-left: 80px;
    }

    /* Adjust for mobile */
    @media (max-width: 767px) {
      .main-content {
        margin-left: 0;
      }
      .sidebar.active ~ .main-content {
        margin-left: 240px;
      }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main content -->
  <div class="main-content">
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row g-5">
  <!-- Total Orders Card -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-basket me-2"></i> Total Orders</h5>
        <p class="card-text display-6"><?= htmlspecialchars($total_orders) ?></p>
      </div>
    </div>
  </div>

  <!-- Total Menu Card -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-menu-button-wide me-2"></i> Total Menu</h5>
        <p class="card-text display-6"><?= htmlspecialchars($total_menu) ?></p>
      </div>
    </div>
  </div>

  <!-- Total Customers Card -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-people me-2"></i> Customers</h5>
        <p class="card-text display-6"><?= htmlspecialchars($total_customers) ?></p>
      </div>
    </div>
  </div>

  <!-- Total Restaurants Card -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-shop me-2"></i> Restaurants</h5>
        <p class="card-text display-6"><?= htmlspecialchars($total_restaurants) ?></p>
      </div>
    </div>
  </div>
</div>



    <!-- Recent Orders Table -->
    <div class="card shadow-sm mt-5">
      <div class="card-header bg-dark text-white">
        <i class="bi bi-clock-history me-2"></i> Recent Orders
      </div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Restaurant</th>
              <th>Total</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stmt as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['order_id']) ?></td>
                <td><?= htmlspecialchars($row['customer']) ?: 'Guest' ?></td>
                <td>
                  <?php
                    $badge = match($row['order_status']) {
                      'pending' => 'warning',
                      'preparing' => 'info',
                      'on_the_way' => 'primary',
                      'delivered' => 'success',
                      'cancelled' => 'danger',
                      default => 'secondary'
                    };
                  ?>
                  <span class="badge bg-<?= $badge ?>">
                    <?= ucfirst(str_replace('_',' ', htmlspecialchars($row['order_status']))) ?>
                  </span>
                </td>
				<td><?= htmlspecialchars($row['restaurant']) ?></td>
                <td>₹<?= number_format($row['total_price'], 2) ?></td>
                <td>
  <?php
    $date = new DateTime($row['placed_at']);
    echo $date->format('d-m-Y g:i'); // Example: 27-08-2025 3:30
  ?>
</td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div> <!-- end main-content -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

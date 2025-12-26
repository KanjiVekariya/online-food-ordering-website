<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 0) {
    header("Location: login.php");
    exit();
}

// Initialize variables for edit form and messages
$editUser = null;
$editUserId = null;
$errors = [];
$successMessage = "";

// Handle form submission for updating customer details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $editUserId = (int)$_POST['edit_user_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($password === '') {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Update the user in the database
        $stmtUpdate = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE user_id = ?");
        $stmtUpdate->execute([$name, $email, $password, $editUserId]);

        $successMessage = "Customer details updated successfully.";

        // Redirect to avoid form resubmission & clear query param
        header("Location: manage_customer.php");
        exit();
    }
}

// Check if edit mode (GET param)
if (isset($_GET['edit'])) {
    $editUserId = (int)$_GET['edit'];

    // Fetch the user to edit
    $stmtEdit = $pdo->prepare("SELECT user_id, name, email, password FROM users WHERE user_id = ?");
    $stmtEdit->execute([$editUserId]);
    $editUser = $stmtEdit->fetch(PDO::FETCH_ASSOC);

    if (!$editUser) {
        $errors[] = "User not found.";
        $editUserId = null;
    }
}

// Fetch all users (excluding admin user)
$stmt = $pdo->query("SELECT name, email,password, created_at, user_id FROM users WHERE user_id != 0 ORDER BY created_at ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare address fetch statement once
$stmtAddr = $pdo->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE user_id = ?");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Customers</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <style>
    body {
      display: flex;
      min-height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
    }
    .content {
      flex-grow: 1;
      padding: 20px;
      background-color: #f8f9fa;
      margin-left: 240px;
    }
    @media (max-width: 767px) {
      .content {
        margin-left: 0;
        padding: 15px;
      }
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="shadow-sm mt-5 content">
<h1>Manage Customers</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>

<?php if ($editUser): ?>
  <!-- Edit form -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Edit Customer Details</div>
    <div class="card-body">
      <form method="POST" action="">
        <input type="hidden" name="edit_user_id" value="<?= htmlspecialchars($editUser['user_id']) ?>">
        
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($editUser['name']) ?>">
        </div>
        
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($editUser['email']) ?>">
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="text" id="password" name="password" class="form-control" required value="<?= htmlspecialchars($editUser['password']) ?>">
          <small class="form-text text-muted">You can update the password here.</small>
        </div>
        
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="manage_customer.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="card-header bg-dark text-white">
  <i class="bi bi-clock-history me-2"></i> All customers
</div>

<table class="table table-striped mb-0">
  <thead class="table-light">
    <tr>
      <th>SR no </th>
      <th>Name</th>
      <th>Email</th>
      <th>Password</th>
      <th>Registered On</th>
      <th>Address(es)</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($users): 
      $i=0;
      ?>

      <?php foreach ($users as $user): ?>
        <?php $i=$i+1 ?>
        <?php
          // Fetch addresses for the user
          $stmtAddr->execute([$user['user_id']]);
          $addresses = $stmtAddr->fetchAll(PDO::FETCH_ASSOC);

          if ($addresses) {
              $formattedAddresses = [];
              foreach ($addresses as $addr) {
                  $formattedAddresses[] = 
                      htmlspecialchars($addr['label']) . ': ' .
                      htmlspecialchars($addr['full_address']) . ', ' .
                      htmlspecialchars($addr['city']) . ' - ' .
                      htmlspecialchars($addr['postal_code']);
              }
              $addressList = implode('<br>', $formattedAddresses);
          } else {
              $addressList = 'No address';
          }
        ?>
        <tr>
          <td><?= htmlspecialchars($i) ?></td>
          <td><?= htmlspecialchars($user['name']) ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td><?= htmlspecialchars($user['password']) ?></td>
          <td><?= htmlspecialchars(date('d-m-Y', strtotime($user['created_at']))) ?></td>
          <td><?= $addressList ?></td>
          <td>
            <a href="?edit=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="text-center">No customers found.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include 'config.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_restaurant_id'])) {
    $delete_id = $_POST['delete_restaurant_id'];
    // Delete restaurant by id
    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
    $stmt->execute([$delete_id]);

    // Also remove from restaurant_categories
    $stmt2 = $pdo->prepare("DELETE FROM restaurant_categories WHERE restaurant_id = ?");
    $stmt2->execute([$delete_id]);

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle edit/update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_restaurant_id'])) {
    $id = $_POST['edit_restaurant_id'];
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $open_time = $_POST['open_time'] ?? '';
    $close_time = $_POST['close_time'] ?? '';

    // Handle photo upload if any
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $tmp_name = $_FILES['photo']['tmp_name'];
        $filename = basename($_FILES['photo']['name']);
        $target_file = $upload_dir . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        if (move_uploaded_file($tmp_name, $target_file)) {
            $photo_path = $target_file;
        }
    }

    // Fetch current photo path to delete if replaced
    $stmtOld = $pdo->prepare("SELECT photo FROM restaurants WHERE restaurant_id = ?");
    $stmtOld->execute([$id]);
    $old = $stmtOld->fetch();
    $old_photo = $old['photo'] ?? null;

    // Build update query with or without photo
    if ($photo_path) {
        $stmt = $pdo->prepare("
            UPDATE restaurants SET 
                name = ?, description = ?, address = ?, phone = ?, open_time = ?, close_time = ?, photo = ?
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$name, $description, $address, $phone, $open_time, $close_time, $photo_path, $id]);

        // Delete old photo file if exists and is different
        if ($old_photo && file_exists($old_photo) && $old_photo !== $photo_path) {
            unlink($old_photo);
        }
    } else {
        $stmt = $pdo->prepare("
            UPDATE restaurants SET 
                name = ?, description = ?, address = ?, phone = ?, open_time = ?, close_time = ?
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$name, $description, $address, $phone, $open_time, $close_time, $id]);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all restaurants + categories (concatenated)
$stmt = $pdo->query("
    SELECT r.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
    FROM restaurants r
    LEFT JOIN restaurant_categories rc ON r.restaurant_id = rc.restaurant_id
    LEFT JOIN categories c ON rc.category_id = c.category_id
    GROUP BY r.restaurant_id
    ORDER BY r.name
");
$restaurants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Restaurants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        table {
            border-collapse: separate !important;
            border: none !important;
        }
        table th, table td {
            border: none !important;
        }
        table img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
        }
        .edit-form {
            display: none;
            margin-top: 15px;
        }
        
        .action-buttons {
  display: flex;
  gap: 8px; /* spacing between buttons */
  justify-content: center; /* center buttons horizontally */
  align-items: center; /* vertical alignment */
}

    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar: Fixed 260px width -->
        <div class="col-auto" style="width: 260px; padding-left: 0;">
            <?php include "sidebar.php"; ?>
        </div>

        <!-- Main Content: Centered, taking up remaining space -->
        <div class="col" style="display: flex; justify-content: center;">
            <div class="container my-5">
                <h1 class="mb-4 text-center">Manage Restaurants</h1>

                <?php if (count($restaurants) > 0): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Open Time</th>
                                <th>Close Time</th>
                                <th>Categories</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants as $r): ?>
                                <tr id="row-<?= $r['restaurant_id'] ?>">
                                    <td>
                                        <?php if ($r['photo']): ?>
                                            <img src="<?= htmlspecialchars($r['photo']) ?>" alt="Photo of <?= htmlspecialchars($r['name']) ?>" />
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/120x80?text=No+Image" alt="No Image" />
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['name']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($r['address'])) ?></td>
                                    <td><?= htmlspecialchars($r['phone']) ?></td>
                                    <td><?= htmlspecialchars($r['open_time']) ?></td>
                                    <td><?= htmlspecialchars($r['close_time']) ?></td>
                                    <td><?= htmlspecialchars($r['categories'] ?? 'None') ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="toggleEditForm(<?= $r['restaurant_id'] ?>)">Edit</button>
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this restaurant?');">
                                            <input type="hidden" name="delete_restaurant_id" value="<?= $r['restaurant_id'] ?>" />
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="edit-row-<?= $r['restaurant_id'] ?>" class="edit-form">
                                    <td colspan="9">
                                        <form method="POST" enctype="multipart/form-data" class="row g-3">
                                            <input type="hidden" name="edit_restaurant_id" value="<?= $r['restaurant_id'] ?>" />
                                            <div class="col-md-6">
                                                <label for="name-<?= $r['restaurant_id'] ?>" class="form-label">Name</label>
                                                <input type="text" id="name-<?= $r['restaurant_id'] ?>" name="name" class="form-control" value="<?= htmlspecialchars($r['name']) ?>" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone-<?= $r['restaurant_id'] ?>" class="form-label">Phone</label>
                                                <input type="text" id="phone-<?= $r['restaurant_id'] ?>" name="phone" class="form-control" value="<?= htmlspecialchars($r['phone']) ?>" />
                                            </div>
                                            <div class="col-md-12">
                                                <label for="description-<?= $r['restaurant_id'] ?>" class="form-label">Description</label>
                                                <textarea id="description-<?= $r['restaurant_id'] ?>" name="description" class="form-control" rows="2"><?= htmlspecialchars($r['description']) ?></textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="address-<?= $r['restaurant_id'] ?>" class="form-label">Address</label>
                                                <textarea id="address-<?= $r['restaurant_id'] ?>" name="address" class="form-control" rows="2"><?= htmlspecialchars($r['address']) ?></textarea>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="open_time-<?= $r['restaurant_id'] ?>" class="form-label">Open Time</label>
                                                <input type="time" id="open_time-<?= $r['restaurant_id'] ?>" name="open_time" class="form-control" value="<?= htmlspecialchars($r['open_time']) ?>" />
                                            </div>
                                            <div class="col-md-3">
                                                <label for="close_time-<?= $r['restaurant_id'] ?>" class="form-label">Close Time</label>
                                                <input type="time" id="close_time-<?= $r['restaurant_id'] ?>" name="close_time" class="form-control" value="<?= htmlspecialchars($r['close_time']) ?>" />
                                            </div>
                                            <div class="col-md-6">
                                                <label for="photo-<?= $r['restaurant_id'] ?>" class="form-label">Photo (upload to replace)</label>
                                                <input type="file" id="photo-<?= $r['restaurant_id'] ?>" name="photo" class="form-control" accept="image/*" />
                                                <?php if ($r['photo']): ?>
                                                    <img src="<?= htmlspecialchars($r['photo']) ?>" alt="Current photo" class="img-fluid mt-2" style="max-width: 200px;" />
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">Save Changes</button>
                                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?= $r['restaurant_id'] ?>)">Cancel</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center">No restaurants found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
    function toggleEditForm(id) {
        const editRow = document.getElementById('edit-row-' + id);
        if (!editRow) return;

        if (editRow.style.display === 'table-row') {
            editRow.style.display = 'none';
        } else {
            // Hide other open edit forms
            document.querySelectorAll('.edit-form').forEach(row => row.style.display = 'none');
            editRow.style.display = 'table-row';
            // Scroll to the edit form smoothly
            editRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

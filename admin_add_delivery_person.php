<?php
include 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $is_available = $_POST['is_available'];

    if (!$name || !$email || !$phone || !$password) {
        $message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM delivery_persons WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $message = "Email already exists. Please use a different email.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO delivery_persons (name, email, phone, gender, password_hash, is_available) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $gender, $password_hash, $is_available])) {
                $message = "Delivery person added successfully!";
            } else {
                $message = "Error inserting data. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Delivery Persons</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div style="margin-left: 240px; padding: 40px; max-width: 500px;">
        <h1>Add Delivery Person</h1>

        <?php if ($message): ?>
            <p style="color: <?= strpos($message, 'successfully') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required style="width: 100%; margin-bottom: 10px;" />
            <input type="email" name="email" placeholder="Email" required style="width: 100%; margin-bottom: 10px;" />
            <input type="text" name="phone" placeholder="Phone Number" required style="width: 100%; margin-bottom: 10px;" />
            <select name="gender" required style="width: 100%; margin-bottom: 10px;">
                <option value="male" selected>Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
            <input type="password" name="password" placeholder="Password" required style="width: 100%; margin-bottom: 10px;" />
            <label>
                Available:
                <select name="is_available" style="margin-left: 10px;">
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                </select>
            </label><br /><br />
            <button type="submit" style="padding: 10px 20px;">Add Delivery Person</button>
        </form>

        <h2>Existing Delivery Persons</h2>
        <ul>
            <?php
            $stmt = $pdo->query("SELECT delivery_id, name, email, phone, gender, is_available FROM delivery_persons ORDER BY delivery_id DESC");
            while ($person = $stmt->fetch()) {
                $status = $person['is_available'] ? 'Available' : 'Not Available';
                echo "<li><strong>" . htmlspecialchars($person['name']) . "</strong> (" . htmlspecialchars($person['gender']) . ") - Email: " . htmlspecialchars($person['email']) . " - Phone: " . htmlspecialchars($person['phone']) . " - Status: $status</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>

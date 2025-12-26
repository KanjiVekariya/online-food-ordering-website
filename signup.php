<?php
// signup.php

$host = 'localhost';
$dbname = 'food_app';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];
        $phone    = trim($_POST['phone']);
        

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            header("Location: signup.php?error=empty_fields");
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: signup.php?error=email_exists");
            exit;
        }

        // Hash the password securely
      
        // Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password,  :phone)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            
            ':phone' => $phone ?: null
        ]);

        header("Location: signup.php?signup=success");
        exit;
    }
} catch (Exception $e) {
    header("Location: signup.php?error=server_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign Up</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .signup-container {
            padding: 30px 40px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            
        }

        h2 {
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            width: 100%;
            text-align: left;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 20px;
            border: 1px solid lightgrey;
            font-size: 1rem;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #AF3E3E;
            border: none;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            color: white;
        }

        input[type="submit"]:hover {
            background-color: #911d1d;
        }

        .error-message,
        .success-message {
            width: 100%;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .error-message {
            background-color: #ff4c4c;
            color: #fff;
        }

        .success-message {
            background-color: #28a745;
            color: #fff;
        }

        .login-link {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: grey;
            text-decoration: none;
            font-size: 0.9rem;
            width: 100%;
        }

        .login-link:hover {
            color: black;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Create Your Account</h2>

    <?php
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'empty_fields') {
            echo '<div class="error-message">Please fill in all required fields.</div>';
        } elseif ($_GET['error'] === 'email_exists') {
            echo '<div class="error-message">This email is already registered.</div>';
        } elseif ($_GET['error'] === 'server_error') {
            echo '<div class="error-message">An error occurred. Please try again later.</div>';
        }
    } elseif (isset($_GET['signup']) && $_GET['signup'] === 'success') {
        echo '<div class="success-message">Signup successful! You can now log in.</div>';
    }
    ?>

    <form method="POST" action="signup.php" novalidate>
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" required  />

        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" required  />

        <label for="password">Password *</label>
        <input type="password" id="password" name="password" required  minlength="6" />

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone"  pattern="^\+?[0-9\s\-]{7,15}$" />

        

        <input type="submit" value="Sign Up" />
    </form>

    <a href="login.php" class="login-link">Already have an acccount ? Login here</a>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
      setTimeout(() => {
        successMessage.style.transition = "opacity 0.5s ease";
        successMessage.style.opacity = '0';
        setTimeout(() => {
          successMessage.style.display = 'none';
        }, 500);
      }, 2000);
    }
  });
</script>

</body>
</html>

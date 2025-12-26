<?php
session_start();

$host = 'localhost';
$dbname = 'food_app';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty_fields");
        exit;
    }

    // Admin shortcut check
    error_log("Received login for: $email");

if ($email === 'admin@gmail.com' && $password === '123') {
    error_log("Admin login successful");
    $_SESSION['user_id'] = 0;
    $_SESSION['name'] = 'Admin User';
    $_SESSION['email'] = 'admin@gmail.com';

    header("Location: dashboard.php");
    exit;
}


    // Normal user login
    $stmt = $pdo->prepare("SELECT user_id, name, email, password FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($password === $user['password']) {  // Plain text password check (not recommended)
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=invalid_credentials");
            exit;
        }
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}

} catch (Exception $e) {
    header("Location: login.php?error=server_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
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

        input[type="email"],
        input[type="password"] {
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

        .signup-link {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: grey;
            text-decoration: none;
            font-size: 0.9rem;
            width: 100%;
        }

        .signup-link:hover {
            color: black;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login to Your Account</h2>

    <?php
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'empty_fields') {
            echo '<div class="error-message">Please enter both email and password.</div>';
        } elseif ($_GET['error'] === 'invalid_credentials') {
            echo '<div class="error-message">Invalid email or password.</div>';
        } elseif ($_GET['error'] === 'server_error') {
            echo '<div class="error-message">An error occurred. Please try again later.</div>';
        }
    } elseif (isset($_GET['signup']) && $_GET['signup'] === 'success') {
        echo '<div class="success-message">Signup successful! Please login below.</div>';
    }
    ?>

    <form method="POST" action="login.php" novalidate>
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" required />

        <label for="password">Password *</label>
        <input type="password" id="password" name="password" required  />

        <input type="submit" value="Login" />
    </form>

    <a href="signup.php" class="signup-link">Don't have an account? Sign up here</a>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const successMessage = document.querySelector('.success-message');
    const errorMessage = document.querySelector('.error-message');
    if (successMessage) {
      setTimeout(() => {
        successMessage.style.transition = "opacity 0.5s ease";
        successMessage.style.opacity = '0';
        setTimeout(() => {
          successMessage.style.display = 'none';
        }, 500);
      }, 2000);
    }
	if (errorMessage) {
      setTimeout(() => {
        errorMessage.style.transition = "opacity 0.5s ease";
        errorMessage.style.opacity = '0';
        setTimeout(() => {
          errorMessage.style.display = 'none';
        }, 500);
      }, 2000);
    }
  });
</script>

</body>
</html>

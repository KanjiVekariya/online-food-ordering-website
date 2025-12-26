<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Thank You</title>
<style>
    body { font-family: Arial, sans-serif; background:#f3f4f6; text-align: center; padding: 50px; }
    h1 { color: #4CAF50; }
    a { color: #f97316; text-decoration: none; font-weight: bold; }
</style>
</head>
<body>
    <h1>Thank you for your order!</h1>
    <p>Your order has been placed successfully.</p>
	<a href="receipt.php" class="btn-confirm" style="text-decoration:none;">
    Download Invoice (PDF)
</a>
    <p><a href="index.php">Return to homepage</a></p>
</body>
</html>

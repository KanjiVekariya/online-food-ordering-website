<?php
// Connect to DB (update credentials)
$conn = new mysqli('localhost', 'root', '', 'food_app');
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Get the searched dish from URL param
$searchDish = isset($_GET['dish']) ? $_GET['dish'] : '';

if ($searchDish) {
    $searchDish = $conn->real_escape_string($searchDish);

    $sql = "
        SELECT r.restaurant_id, r.name AS restaurant_name, r.address, r.phone,
               m.item_id, m.name AS menu_name, m.price, m.description, m.image_url
        FROM restaurants r
        JOIN menu_items m ON r.restaurant_id = m.restaurant_id
        WHERE m.name LIKE '%$searchDish%'
        AND m.is_available = 1
        AND r.is_open = 1
        ORDER BY r.restaurant_id
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $currentRestaurant = null;
        echo "<h1>Restaurants serving '$searchDish'</h1>";

        while ($row = $result->fetch_assoc()) {
            // When restaurant changes, print header
            if ($currentRestaurant !== $row['restaurant_id']) {
                if ($currentRestaurant !== null) {
                    echo "</ul>"; // close previous restaurant menu list
                }
                $currentRestaurant = $row['restaurant_id'];
                echo "<h2>" . htmlspecialchars($row['restaurant_name']) . "</h2>";
                echo "<p>Address: " . htmlspecialchars($row['address']) . "</p>";
                echo "<p>Phone: " . htmlspecialchars($row['phone']) . "</p>";
                echo "<ul>";
            }

            // Print menu item info for momos
            echo "<li>";
            echo "<strong>" . htmlspecialchars($row['menu_name']) . "</strong> - $" . number_format($row['price'], 2);
            if (!empty($row['description'])) {
                echo "<br>" . htmlspecialchars($row['description']);
            }
            if (!empty($row['image_url'])) {
                echo "<br><img src='" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['menu_name']) . "' width='100'>";
            }
            echo "</li>";
        }
        echo "</ul>"; // close last restaurant menu list
    } else {
        echo "No restaurants found serving '$searchDish'.";
    }
} else {
    echo "Please select a dish to search.";
}

$conn->close();
?>

<?php
include 'config.php';
include 'utils/navbar.php';


// Handle AJAX autocomplete request
if (isset($_GET['ajax_search'])) {
    $query = trim($_GET['ajax_search']);

    // Prepare case-insensitive query to find menu items starting with $query
    $stmt = $pdo->prepare("SELECT name, price, image_url FROM menu_items WHERE is_available = 1 AND name LIKE :search ORDER BY name ASC LIMIT 8");
    $stmt->execute(['search' => $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}






// Check if this is an AJAX request for search suggestions
if (isset($_GET['ajax_search'])) {
    $search = strtolower(trim($_GET['ajax_search']));
    
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM menu_items WHERE is_available = 1 AND LOWER(name) LIKE :search ORDER BY name LIMIT 10");
        $stmt->execute(['search' => "%$search%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $results = [];
    }

    // Return JSON response for AJAX call
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// For normal page load (full menu display or search via GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE is_available = 1 AND LOWER(name) LIKE :search ORDER BY name");
    $stmt->execute(['search' => '%' . strtolower($search) . '%']);
} else {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY name");
    $stmt->execute();
}

$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Menu - DailyBites</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background: #fafafa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #222;
    }
    main h2 {
      text-align: center;
      margin: 3rem 0 1.5rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      color: #2c3e50;
    }

    .search-wrapper {
      max-width: 600px;
      margin: 0 auto 3rem;
      position: relative;
    }

    .search-form {
      display: flex;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      overflow: hidden;
      background: #fff;
      border: 1px solid #ddd;
      transition: box-shadow 0.3s ease;
    }

    .search-form:focus-within {
      box-shadow: 0 4px 14px rgba(0,0,0,0.15);
      border-color: #198754;
    }

    .search-form input[type="text"] {
      flex-grow: 1;
      border: none;
      padding: 12px 20px;
      font-size: 1rem;
      outline: none;
      color: #333;
    }

    .search-form button {
      background-color: #198754;
      border: none;
      color: white;
      padding: 0 25px;
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
      transition: background-color 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .search-form button:hover {
      background-color: #146c43;
    }

    /* Suggestions dropdown */
    #suggestions {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #ddd;
      border-top: none;
      max-height: 300px;
      overflow-y: auto;
      z-index: 1000;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      display: none; /* Hidden by default */
    }

    #suggestions .suggestion-item {
      display: flex;
      align-items: center;
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 1px solid #eee;
      transition: background-color 0.2s ease;
    }

    #suggestions .suggestion-item:last-child {
      border-bottom: none;
    }

    #suggestions .suggestion-item:hover {
      background-color: #f1f1f1;
    }

    #suggestions img {
      width: 50px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
      margin-right: 15px;
      flex-shrink: 0;
    }

    #suggestions .suggestion-info {
      flex-grow: 1;
    }

    #suggestions .suggestion-name {
      font-weight: 600;
      margin-bottom: 4px;
      color: #2c3e50;
    }

    #suggestions .suggestion-price {
      color: #198754;
      font-weight: 700;
    }

    /* Cards grid layout */
    .menu-container {
      max-width: 1100px;
      margin: 0 auto 5rem;
      padding: 0 15px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }

    .card-img-top {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<main id="menu">
  <h2>Explore Best Menu</h2>

  <div class="search-wrapper">
    <form action="" method="GET" class="search-form" role="search" aria-label="Search menu items" autocomplete="off">
      <input
        type="text"
        name="search"
        id="search-input"
        placeholder="Search menu items..."
        value="<?php echo htmlspecialchars($search); ?>"
        aria-label="Search menu items"
      />
      <button type="submit" aria-label="Submit search">🔍</button>
    </form>
    <div id="suggestions" role="listbox" aria-label="Search suggestions"></div>
  </div>

  <div class="menu-container" id="menu-container">
    <?php if (count($menuItems) > 0): ?>
      <?php foreach ($menuItems as $row): ?>
        <div class="card" style="width: 18rem;">
          <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
            <strong>&#8377;<?php echo number_format($row['price'], 2); ?></strong><br><br>
            <a href="search_dishes.php?query=<?php echo urlencode($row['name']); ?>" class="btn btn-success">Order Now</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; font-size:1.2rem; color:#777; width: 100%;">
        No menu items found<?php echo $search ? " for '<strong>" . htmlspecialchars($search) . "</strong>'." : "."; ?>
      </p>
    <?php endif; ?>
  </div>
</main>

<?php include 'utils/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const searchInput = document.getElementById('search-input');
  const suggestions = document.getElementById('suggestions');
  const menuContainer = document.getElementById('menu-container');

  let debounceTimer;

  // Fetch suggestions from server via AJAX
  function fetchSuggestions(query) {
    if (query.length < 1) {
      suggestions.style.display = 'none';
      return;
    }

    fetch(`menu.php?ajax_search=${encodeURIComponent(query)}`)
      .then(res => res.json())
      .then(data => {
        if (data.length === 0) {
          suggestions.style.display = 'none';
          return;
        }

        // Build suggestions HTML
        suggestions.innerHTML = '';
        data.forEach(item => {
          const itemDiv = document.createElement('div');
          itemDiv.classList.add('suggestion-item');
          itemDiv.setAttribute('role', 'option');
          itemDiv.setAttribute('tabindex', '-1');

          itemDiv.innerHTML = `
            <img src="${item.image_url}" alt="${item.name}">
            <div class="suggestion-info">
              <div class="suggestion-name">${item.name}</div>
              <div class="suggestion-price">&#8377;${parseFloat(item.price).toFixed(2)}</div>
            </div>
          `;

          // Clicking suggestion navigates to order page for that item
          itemDiv.addEventListener('click', () => {
            window.location.href = `search_dishes.php?query=${encodeURIComponent(item.name)}`;
          });

          suggestions.appendChild(itemDiv);
        });

        suggestions.style.display = 'block';
      })
      .catch(err => {
        console.error('Error fetching suggestions:', err);
        suggestions.style.display = 'none';
      });
  }

  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      fetchSuggestions(searchInput.value.trim());
    }, 250); // debounce delay to avoid flooding server with requests
  });

  // Close suggestions if clicked outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrapper')) {
      suggestions.style.display = 'none';
    }
  });

  // Optional: Keyboard navigation support for suggestions
  let selectedIndex = -1;
  searchInput.addEventListener('keydown', (e) => {
    const items = suggestions.querySelectorAll('.suggestion-item');
    if (suggestions.style.display === 'block' && items.length > 0) {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        items.forEach((item, i) => {
          item.classList.toggle('bg-success', i === selectedIndex);
          item.classList.toggle('text-white', i === selectedIndex);
        });
        items[selectedIndex].focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        items.forEach((item, i) => {
          item.classList.toggle('bg-success', i === selectedIndex);
          item.classList.toggle('text-white', i === selectedIndex);
        });
        items[selectedIndex].focus();
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (selectedIndex >= 0 && selectedIndex < items.length) {
          items[selectedIndex].click();
        }
      } else if (e.key === 'Escape') {
        suggestions.style.display = 'none';
      }
    }
  });

  // Focus on the search input when Alt + L is pressed
  document.addEventListener('keydown', function(e) {
    // Check if 'Alt' + 'L' is pressed
    if (e.altKey && e.key === 'x') {
      // Prevent the default action (if any) for Alt + L (like opening a browser menu)
      e.preventDefault();
      // Focus the search input field
      searchInput.focus();
	  const length = searchInput.value.length;
    searchInput.setSelectionRange(length, length);
    }
  });
</script>

</body>
</html>

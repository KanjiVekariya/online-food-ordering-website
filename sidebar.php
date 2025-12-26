

<!-- sidebar.php -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
  /* Base sidebar */
  .sidebar {
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    width: 240px;
    background-color: #333;
    color: white;
    padding: 20px;
    font-family: Arial, sans-serif;
    overflow-y: auto;
    transition: all 0.3s ease;
    z-index: 1050;
  }

  /* Sidebar links and styles */
  .sidebar a, .menu-title {
    color: white;
    text-decoration: none;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: flex-start; /* Align left */
  }

  .sidebar a:hover, .menu-title:hover {
    background-color: #555;
  }

  .menu-item {
    margin-bottom: 10px;
    position: relative;
  }

  .menu-title {
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: font-size 0.3s ease;
    gap: 10px; /* space between icon and text */
  }

  .menu-title.active {
    font-weight: bold;
    background-color: #555;
  }

  .menu-links {
    display: none;
    flex-direction: column;
    padding-left: 38px; /* indent submenu */
    margin-top: 6px;
    gap: 6px;
  }

  .menu-links a {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    display: block;
    text-align: left;
  }

  .menu-links a.active {
    background-color: #555;
    font-weight: bold;
  }

  .arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
    flex-shrink: 0;
    margin-left: auto; /* Push arrow to far right */
  }

  .arrow.down {
    transform: rotate(90deg);
  }

  /* Scrollbar */
  .sidebar::-webkit-scrollbar {
    width: 6px;
  }
  .sidebar::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 3px;
  }

  /* Toggle button inside sidebar */
  #sidebarToggle {
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    user-select: none;
    z-index: 2001;
  }

  /* Collapsed sidebar style */
  .sidebar.collapsed {
    width: 80px !important;
    padding: 20px 10px !important;
    overflow-x: hidden;
  }

  /* Hide menu text when collapsed */
  .sidebar.collapsed a,
  .sidebar.collapsed .menu-title > span.text-label,
  .sidebar.collapsed .menu-title > .arrow {
    font-size: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    overflow: hidden !important;
    width: 0 !important;
  }

  /* Show only icons when collapsed */
  .menu-title i {
    font-size: 20px;
    width: 28px;
    text-align: center;
    flex-shrink: 0;
  }

  /* Hide submenu links completely when collapsed */
  .sidebar.collapsed .menu-links {
    display: none !important;
  }

  /* Tooltip container for hover preview in collapsed mode */
  .menu-item-tooltip {
    position: fixed;
    background-color: #444;
    padding: 10px 15px;
    border-radius: 6px;
    color: white;
    font-size: 14px;
    font-weight: normal;
    white-space: nowrap;
    z-index: 3000;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    display: none;
  }

  /* Show full menu title on hover when collapsed (tooltip) */
  .sidebar.collapsed .menu-item:hover > .menu-item-tooltip {
    display: block;
  }

  /* Position the tooltip vertically centered next to sidebar */
  .menu-item-tooltip.left {
    left: 90px;
  }

  /* Adjust "View Site" button */
  .sidebar.collapsed > div:last-child a {
    font-size: 0 !important;
    padding: 10px 6px !important;
  }
  .sidebar.collapsed > div:last-child a:hover {
    font-size: 14px !important;
  }

  /* Mobile styles */
  @media (max-width: 767px) {
    .sidebar {
      left: -240px;
      width: 240px;
      padding: 20px;
      transition: left 0.3s ease;
    }
    .sidebar.active {
      left: 0;
      box-shadow: 2px 0 12px rgba(0,0,0,0.5);
    }

    #sidebarToggle {
      position: fixed;
      top: 15px;
      left: 15px;
      background: #333;
      border-radius: 4px;
      padding: 10px 12px;
      z-index: 1100;
      font-size: 18px;
    }

    body.sidebar-open {
      overflow: hidden;
    }

    .sidebar.collapsed a,
    .sidebar.collapsed .menu-title > span.text-label,
    .sidebar.collapsed .menu-title > .arrow {
      font-size: 14px !important;
      padding: initial !important;
      margin: initial !important;
      width: auto !important;
      overflow: visible !important;
    }
  }

  /* User info container */
  .sidebar .user-info {
    margin-bottom: 20px;
    padding-left: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #555;
    color: #ccc;
    font-size: 14px;
    user-select: none;
  }
</style>

<?php
  $current = basename($_SERVER['PHP_SELF']);

  $menus = [
    "Food" => [
      "icon" => "bi-basket",
      "view" => "menu_items_view.php",
      "insert" => "admin_add_food.php"
    ],
    "Categories" => [
      "icon" => "bi-tags",
      "view" => "view_category.php",
      "insert" => "admin_insert_categories.php"
    ],
    "Restaurants" => [
      "icon" => "bi-shop",
      "view" => "view_restaurants.php",
      "insert" => "admin_restaurants.php"
    ],
/*"Delivery Staff" => [
      "icon" => "bi-person-check",
      "view" => "admin_delivery_staff.php",
      "insert" => "admin_add_delivery_staff.php"
    ],*/
    "Manage Customers" => [
      "icon" => "bi-person",
      "view" => "manage_customer.php",
      "insert" => "signup.php"
    ],

  ];

  $openMenu = null;
  foreach ($menus as $menuName => $pages) {
    if ($current === $pages['view'] || $current === $pages['insert']) {
      $openMenu = $menuName;
      break;
    }
  }
?>

<!-- Sidebar toggle button -->
<button id="sidebarToggle" aria-label="Toggle sidebar"><i class="fa-solid fa-sliders"></i></button>

<div class="sidebar bg-dark">
  <h1 style="margin:1rem 0rem; text-align:center;">Admin</h1>
  <div class="user-info">
    <i class="bi bi-person-circle" style="font-size: 28px; color: white;"></i>
    <div>admin@gmail.com</div>
  </div>

  <div style="margin-top: 10px;">
    <a href="dashboard.php" style="display: flex; align-items: center; padding: 10px 12px; text-decoration: none; color: white; gap: 10px;">
      <i class="bi bi-speedometer2" style="width:28px; text-align:center; font-size:20px; flex-shrink: 0;"></i>
      <span class="text-label">Dashboard</span>
    </a>
  </div>

  <?php foreach ($menus as $menuName => $pages): ?>
    <div class="menu-item" tabindex="0">
      <div class="menu-title <?= ($openMenu === $menuName) ? 'active' : '' ?>" data-menu="<?= htmlspecialchars($menuName) ?>">
        <i class="bi <?= $pages['icon'] ?>" aria-hidden="true" style="flex-shrink: 0; width: 28px; text-align: center; font-size: 20px;"></i>
        <span class="text-label"><?= htmlspecialchars($menuName) ?></span>
        <span class="arrow <?= ($openMenu === $menuName) ? 'down' : '' ?>">&rsaquo;</span>
      </div>
      <div class="menu-links" style="<?= ($openMenu === $menuName) ? 'display:flex;' : '' ?>">
        <a href="<?= $pages['view'] ?>" class="view <?= ($current === $pages['view']) ? 'active' : '' ?>">View</a>
        <a href="<?= $pages['insert'] ?>" class="insert <?= ($current === $pages['insert']) ? 'active' : '' ?>">Insert</a>
      </div>

      <!-- Tooltip for collapsed mode -->
      <div class="menu-item-tooltip left" aria-hidden="true">
        <div><strong><?= htmlspecialchars($menuName) ?></strong></div>
        <div style="margin-top: 6px;">
          <a href="<?= $pages['view'] ?>" style="color:#ddd; text-decoration: underline; display: block; margin-bottom: 4px;">View</a>
          <a href="<?= $pages['insert'] ?>" style="color:#ddd; text-decoration: underline; display: block;">Insert</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <div style="margin-top: 30px;">
    <a href="index.php" target="_blank" style="display: flex; align-items: center; padding: 10px 12px; background-color: #444; border-radius: 4px; text-decoration: none; color: white; gap: 10px;">
      <i class="bi bi-globe" style="width:28px; text-align:center; font-size:20px; flex-shrink: 0;"></i>
      <span class="text-label">View Site</span>
    </a>
  </div>
  <div style="margin-top: 30px;">
    <a href="logout_admin.php" target="_blank" style="display: flex; align-items: center; padding: 10px 12px; border-radius: 4px; text-decoration: none; color: white; gap: 10px;">
      <i class="bi bi-box-arrow-right" style="flex-shrink: 0;"></i>
      <span class="text-label">logout</span>
    </a>
  </div>
</div>

<script>
  // Toggle submenu open/close
  document.querySelectorAll('.menu-title').forEach(title => {
    title.addEventListener('click', () => {
      const activeTitle = document.querySelector('.menu-title.active');
      if (activeTitle === title) {
        title.classList.remove('active');
        title.nextElementSibling.style.display = 'none';
        title.querySelector('.arrow').classList.remove('down');
        return;
      }
      if (activeTitle) {
        activeTitle.classList.remove('active');
        activeTitle.nextElementSibling.style.display = 'none';
        activeTitle.querySelector('.arrow').classList.remove('down');
      }
      title.classList.add('active');
      title.nextElementSibling.style.display = 'flex';
      title.querySelector('.arrow').classList.add('down');
    });
  });
</script>

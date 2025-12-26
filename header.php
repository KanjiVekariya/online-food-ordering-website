<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Your Site Title</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
</head>
<style>
	#adminnav
	{
		position:sticky;
		top:0px;
		left:0px;
	}
</style>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="adminnav">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">FoodApp Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo ' active'; ?>" href="index.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF']) == 'menu_items_view.php') echo ' active'; ?>" href="menu_items_view.php">Menu Items</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF']) == 'view_category.php') echo ' active'; ?>" href="view_category.php">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF']) == 'admin_restaurants.php') echo ' active'; ?>" href="admin_restaurants.php">Restaurants</a>
        </li>
        <!-- Add more nav links as needed -->
      </ul>
      <form class="d-flex" role="search" action="search.php" method="GET">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="q" />
        <button class="btn btn-outline-light" type="submit">Search</button>
      </form>
      <ul class="navbar-nav ms-3">
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Optional container to start your page content -->
<div class="container mt-4">

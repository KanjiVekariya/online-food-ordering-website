<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - FoodZone</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    .main {
      margin-left: 500px;
      padding: 40px;
    }

    h1 {
      margin-bottom: 30px;
    }

    form {
      max-width: 700px;
     background: #f9f9f9;
     
      padding:20px;
	  border:1px solid lightgrey;
      border-radius: 8px;
      //box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea,
    select {
      width: 100%;
      margin-bottom: 20px;
      padding: 8px;
      border-radius: 2px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    button {
      background-color: #2980b9;
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #1f6391;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <h1>Add New Food Item</h1>
	

    <?php
    // Show success popup if ?success=1 in URL
    if (isset($_GET['success']) && $_GET['success'] == 1) {
      echo "<script>alert('✅ Food item inserted successfully!');</script>";
    }

    // Fetch categories and restaurants
    $categories = $pdo->query("SELECT category_id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $restaurants = $pdo->query("SELECT restaurant_id, name FROM restaurants")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <form action="insert_food.php" method="POST" enctype="multipart/form-data">
	
      <label for="name">Food Name</label>
      <input type="text" name="name" required>

      <label for="description">Description</label>
      <textarea name="description" rows="4" required></textarea>

      <label for="price">Price (₹)</label>
      <input type="number" step="0.01" name="price" required>

		 
	  <div class="mb-3">
		  <label for="formFile image" class="form-label">Upload Image</label>
		  <input class="form-control" type="file" id="formFile" name="image" accept="image/*" required>
	  </div>



      <!--
	  
	  <label for="image">Upload Image</label>
      <input type="file" name="image" accept="image/*" required>-->

      <label for="restaurant_id">Select Restaurant</label>
	  <select class="form-select" aria-label="Default select example" name="restaurant_id" required>
        <option value="">Select a Restaurant --</option>
        <?php foreach ($restaurants as $res): ?>
          <option value="<?= $res['restaurant_id'] ?>"><?= htmlspecialchars($res['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="category_id">Select Category</label>
      
	  <select class="form-select" aria-label="Default select example" name="category_id" required>
		  <option selected>Select a Category</option>
		  <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
	    </select>
	  
	  
	  
	  
	  <!--<select name="category_id" required>
        <option value="">Select a Category</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>-->

    <br>  <button type="submit">➕ Add Food</button>
    </form>
  </div>
</body>
</html>

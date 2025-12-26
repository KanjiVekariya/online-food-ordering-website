<?php include 'config.php'; 

$stmt = $pdo->prepare("SELECT * FROM restaurants ORDER BY created_at DESC");
    $stmt->execute();
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
session_start(); // MUST be first
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>DailyBites - Menu</title>
  <link rel="stylesheet" href="assets/style.css" />
  
 
 
 <style>
    /* Navbar styles */
    

    /* Hero Section */
    .hero {
      background: url('assets/front.jpg') no-repeat center center/cover;
      color: white;
      text-align: center;
      padding: 200px 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      box-shadow: inset 0 0 0 1000px rgba(0,0,0,0.4);
      margin-bottom: 40px;
    }
    .hero h1 {
      font-size: 3rem;
      margin-bottom: 15px;
      font-weight: 700;
    }
    .hero p {
      font-size: 1.3rem;
      margin-bottom: 30px;
      font-weight: 500;
    }
    .hero .btn {
      background: #AF3E3E;
      padding: 15px 35px;
      font-size: 1.1rem;
      border: none;
      border-radius: 35px;
      color: white;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .hero .btn:hover {
      background: #ab2f2f;
    }

    /* Existing styles for menu */
    header h1 {
      text-align: center;
      margin: 40px 0 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    main h2 {
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin-bottom: 25px;
    }
    .menu-container {
      display: grid;
      grid-template-columns: repeat(auto-fill,minmax(280px,1fr));
      gap: 25px;
      padding: 0 30px 40px;
      max-width: 1100px;
      margin: 0 auto;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .menu-card {
      box-shadow: 0 2px 10px rgb(0 0 0 / 0.1);
      border-radius: 12px;
      overflow: hidden;
      background: white;
      transition: box-shadow 0.3s ease;
    }
    .menu-card:hover {
      box-shadow: 0 4px 18px rgb(0 0 0 / 0.15);
    }
    .menu-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    .menu-card h3 {
      margin: 15px;
      font-weight: 600;
      color: #222;
    }
    .menu-card p {
      margin: 0 15px 10px;
      color: #555;
      font-size: 0.95rem;
      min-height: 50px;
    }
    .menu-card strong {
      display: block;
      margin: 0 15px 15px;
      font-size: 1rem;
      color: #ff6f61;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 20px 0;
      background: #f5f5f5;
      color: #555;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin-top: 40px;
    }
	  .card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
  }
 .category-carousel .swiper-slide {
      text-align: center;
      width: auto;
    }

    .category-carousel .swiper-slide1 img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      margin: auto;
    }



    .swiper-buttons .btn-yellow {
      background-color: #ffc107;
      border: none;
      padding: 6px 12px;
      font-size: 20px;
      border-radius: 50%;
    }

    .swiper-buttons .btn-yellow:hover {
      background-color: #e0a800;
    }

    .category-title {
      font-size: 14px;
    }
	.swiper-slide1 {
    width: auto;
  }
a.card-link {
  display: block;
  color: inherit;
  text-decoration: none;
  transition: box-shadow 0.3s ease, transform 0.2s ease;
  outline: none;   
border-radius:2rem;  /* Removes blue outline */
}
a.card-link:hover {
	//box-shadow: rgba(0, 0, 0, 0.2) 0px 18px 50px -10px;
	 transition: transform 0.3s ease, box-shadow 0.3s ease;
	   transform: translateY(-8px);
	   transform: scale(0.95);
}
 footer a:hover {
  color: black !important;
  text-decoration: underline;
}
  </style>
  <!-- Swiper CSS -->

<!-- Swiper JS -->


</head>

<body>
  <?php include 'utils/navbar.php';?>


  <section class="hero">
    <h1>Delicious Food Delivered To You</h1>
    <p>Explore our diverse menu and order your favorite meals with ease!</p>
    <a href="#menu" class="btn">Order Now</a>
  </section>


<!--category-->

<section class="py-5 overflow-hidden">
  <div class="container-lg">
    <div class="row">
      <div class="col-md-12">

        <div class="section-header d-flex flex-wrap justify-content-between mb-4">
          <h2 class="section-title">Category</h2>

          <div class="d-flex align-items-center">
            <!--<a href="#" class="btn btn-primary me-2">View All</a>-->
            <div class="swiper-buttons">
              <button class="category-prev swiper-prev btn btn-yellow">❮</button>
<button class="category-next swiper-next btn btn-yellow">❯</button>
            </div>
          </div>
        </div>

      </div>
    </div>
    <div class="row">
      <div class="col-md-12">

        <div class="category-carousel swiper">
          <div class="swiper-wrapper">
            <!-- 8 Categories -->
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s1.avif" alt="Fruits">
              
            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s2.avif" alt="Breads">

            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s3.avif" alt="Fruits">
     
            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s4.avif" alt="Beverages">
              
            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s5.avif" alt="Meat">
   
            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s6.avif" alt="Breads">
 
            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s7.avif" alt="Fruits">

            </a>
            <a href="#" class="nav-link swiper-slide1 text-center">
              <img src="assets/s8.avif" alt="Sweets">
          
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section


<!--restaurant all cards-->


<div class="container my-5">
  <h3 class="mb-4 fw-bold">Top Restaurants</h3>

  <div class="swiper restaurant-slider">
    <div class="swiper-wrapper">

      <?php foreach ($restaurants as $r): ?>
	  
        <div class="swiper-slide">
		<a href="restaurant.php?id=<?= $r['restaurant_id'] ?>" class="text-decoration-none btn btn-sm card-link">

          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="position-relative">
			
			
              <img src="<?= htmlspecialchars($r['photo']) ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="<?= htmlspecialchars($r['name']) ?>">
              <div class="position-absolute bottom-0 start-0 bg-dark text-white px-2 py-1 small" style="opacity: 0.85;">
                <?= rand(0, 1) ? "ITEMS AT ₹" . rand(29, 59) : "₹100 OFF ABOVE ₹399" ?>
              </div>
            </div>
            <div class="card-body px-3 py-2">
              <h6 class="mb-1 fw-bold"><?= htmlspecialchars($r['name']) ?></h6>
              <p class="mb-1 text-muted small">
                <span class="text-success fw-semibold">★ <?= number_format(rand(4.1, 4.5), 1) ?></span> • <?= rand(25, 45) ?>-<?= rand(30, 50) ?> mins
              </p>
              <p class="mb-1 small text-muted text-truncate"><?= htmlspecialchars($r['description'] ?? 'Multicuisine') ?></p>
              <p class="mb-0 text-muted small"><?= htmlspecialchars($r['address']) ?></p>
            </div>
          </div>
		  </a>
        </div>
      <?php endforeach; ?>

    </div>

    <div class="d-flex justify-content-end mt-3">
      <button class="restaurant-prev swiper-prev btn btn-yellow">❮</button>
<button class="restaurant-next swiper-next btn btn-yellow">❯</button>
    </div>
  </div>
</div>






  <main id="menu">
    <h2>Explore Best Menu</h2>
    <div class="menu-container">
      <?php
	  $i=0;
        $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1");
        while ($row = $stmt->fetch()) {
			$i=$i+1;
			if($i>6)
			{
				break;
			}
          echo '
            <div class="card" style="width: 18rem;">
  <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">
  <div class="card-body">
    <h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>
    <p class="card-text">' . htmlspecialchars($row['description']) . '</p>
	<strong>&#8377;' . number_format($row['price'], 2) . '</strong><br><br>
    <a href="search_dishes.php?query=' . urlencode($row['name']) . '" class="btn btn-success">Order Now</a>


  </div>
</div>';
        }
      ?>
    </div>
  </main>

<section class="blog-section" style="max-width: 1100px; margin: 50px auto; padding: 0 20px;">
  <h2 style="color: #AF3E3E; font-family: 'Poppins', sans-serif; text-align: center; margin-bottom: 40px;">
    From Our Blog
  </h2>
  <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: center;">
    <div style="flex: 1 1 45%; min-width: 280px;">
      <img src="assets/blog.avif" alt="Delicious Food Blog" style="width: 100%; border-radius: 15px; object-fit: cover; height: 300px;" />
    </div>
    <div style="flex: 1 1 50%; min-width: 280px; font-family: 'Poppins', sans-serif; color: #333;">
      <h3 style="margin-bottom: 15px;">5 Tips for Ordering Fresh & Healthy Meals Online</h3>
      <p style="line-height: 1.7; font-size: 1rem;">
        Discover the secrets to choosing the best fresh meals delivered right to your doorstep. From selecting quality ingredients to timing your orders for maximum freshness, our expert tips will help you enjoy every bite.
      </p>
      <a href="#" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #AF3E3E; color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
        Read More
      </a>
    </div>
  </div>
</section>


<!--category over -->


<!--footer start-->
  <?php include 'utils/footer.php';?>



  
<script>
 const swiper = new Swiper('.category-carousel', {
    slidesPerView: 8, // Shows 8 at once on desktop
    spaceBetween: 20,
    loop: false,
    navigation: {
      nextEl: '.category-next',
      prevEl: '.category-prev',
    },
    breakpoints: {
      0: {
        slidesPerView: 2
      },
      576: {
        slidesPerView: 3
      },
      768: {
        slidesPerView: 4
      },
      992: {
        slidesPerView: 6
      },
      1200: {
        slidesPerView: 8
      }
    }
  });
new Swiper('.restaurant-slider', {
  slidesPerView: 4,
  spaceBetween: 20,
  loop: false,
  navigation: {
    nextEl: '.restaurant-next',
    prevEl: '.restaurant-prev',
  },
  breakpoints: {
    0: { slidesPerView: 1 },
    576: { slidesPerView: 2 },
    768: { slidesPerView: 3 },
    992: { slidesPerView: 4 }
  }
});

</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('signupToast');
    toastEl.querySelector('.toast-body').textContent = <?php echo json_encode($toastMessage); ?>;
    toastEl.querySelector('.toast-header').className = "toast-header <?php echo $toastClass; ?>";
    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    // Remove query params from URL without reload
    if (window.history.replaceState) {
      const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
      window.history.replaceState({path: url}, '', url);
    }
  });
</script>

</body>
</html>

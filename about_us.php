<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - DailyBites</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * {
   
      box-sizing: border-box;
      
    }

    body {
      background-color: #fff;
      color: #333;
	  font-family: 'Poppins', sans-serif;
    }

    /* Navbar */
    

    /* Container */
    .container {
      padding: 40px 20px;
      max-width: 1200px;
      margin: auto;
    }

    .section {
      margin-bottom: 60px;
    }

    .section h2 {
      font-size: 32px;
      margin-bottom: 20px;
      color: #AF3E3E;
    }

    .section p {
      font-size: 16px;
      line-height: 1.8;
      margin-bottom: 20px;
    }

    /* Two-column layout */
    .row-two-columns {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      align-items: center;
    }

    .column {
      flex: 1 1 50%;
    }

    .image-column img {
      width: 100%;
      border-radius: 20px;
      object-fit: cover;
    }

    @media (max-width: 768px) {
      .row-two-columns {
        flex-direction: column;
      }

      .column {
        flex: 1 1 100%;
      }
    }

    /* Chef Section */
    .chefs {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
    }

    .chef-card {
      flex: 1 1 250px;
      background: #f8f8f8;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
    }

    .chef-card img {
      width: 100%;
      max-width: 200px;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    /* Reviews Section */
    .reviews {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
    }

    .review-card {
      flex: 1 1 300px;
      background: #f1f1f1;
      padding: 20px;
      border-radius: 10px;
    }

    .review-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .review-card .name {
      font-weight: bold;
      margin-bottom: 8px;
    }

    /* Footer */
    footer {
      background-color: #AF3E3E;
      color: white;
      text-align: center;
      padding: 20px;
      margin-top: 40px;
    }
	/* Two-column layout (larger and more spacious) */
.row-two-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  padding: 60px 0; /* More vertical space */
}

.image-column img {
  width: 100%;
  height: 40rem;
 // max-height: 500px;
  object-fit: contain;
  border-radius: 16px;
  //box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* subtle depth */
}

/* Text styling for more impact */
.text-column p {
  font-size: 18px;
  line-height: 1.9;
  margin-bottom: 20px;
}

.section.about-food h2 {
  font-size: 38px;
  margin-bottom: 30px;
  color: #AF3E3E;
}

/* Responsive fallback */
@media (max-width: 768px) {
  .row-two-columns {
    grid-template-columns: 1fr;
    gap: 30px;
    padding: 40px 0;
  }

  .section.about-food h2 {
    font-size: 28px;
  }

  .text-column p {
    font-size: 16px;
  }
}
.mission-image-container {
  margin: 40px 0 60px;
  text-align: center;
}

.mission-image-container img {
  width: 100%;
  max-height: 520px;
  object-fit: cover;
  border-radius: 12px;
  //box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}



  </style>
</head>
<body>

  <!-- Navbar -->
 <?php include 'utils/navbar.php';?>
  <!-- Main Content -->
  <div class="container">

    <!-- Mission -->
    <!-- Mission -->
<section class="section">
  <h2>Our Mission</h2>
  <p>
    At DailyBites, we’re passionate about delivering delicious, fresh meals right to your doorstep.
    We aim to make your daily meals convenient, affordable, and delightful—without compromising on quality.
  </p>
</section>

<!-- Wide Rectangle Image -->
<div class="mission-image-container">
  <img src="assets/our_mission.png" alt="Our Mission Image">
</div>


    <!-- What We Serve (Two Columns) -->

<section class="section about-food">
  <h2>What We Serve</h2>
  <div class="row-two-columns">
    <div class="column image-column">
      <img src="assets/delicious_food.avif" alt="Delicious Food">
    </div>
    <div class="column text-column">
      <div>
        <p>
          At DailyBites, we offer a diverse menu crafted with love by our expert chefs. Whether you’re craving traditional Indian meals, sizzling Asian dishes, or modern Western fusion, we’ve got something to satisfy every palate.
        </p>
        <p>
          All our meals are made with locally sourced, organic ingredients. We believe in fresh, healthy, and flavor-packed food that not only tastes great but nourishes your body too.
        </p>
        <p>
          Whether you're ordering lunch at the office or dinner with family, DailyBites is here to serve joy on your plate.
        </p>
      </div>
    </div>
  </div>
</section>



    <!-- Chef Details -->
    <section class="section">
      <h2>Meet Our Chefs</h2>
      <div class="chefs">
        <div class="chef-card">
          <img src="assets/chef1.avif" alt="Chef Raj">
          <h4>Chef Raj Malhotra</h4>
          <p>Head Chef with 15+ years of experience in Indian and Fusion cuisine.</p>
        </div>
        <div class="chef-card">
          <img src="assets/chef2.jpg" alt="Chef Anjali">
          <h4>Chef Anjali Verma</h4>
          <p>Pastry Chef specializing in desserts and sweet creations you'll never forget.</p>
        </div>
        <div class="chef-card">
          <img src="assets/chef3.jpg" alt="Chef Lee">
          <h4>Chef Daniel Lee</h4>
          <p>Asian cuisine expert bringing spicy, savory flavors from across the continent.</p>
        </div>
      </div>
    </section>

    <!-- Customer Reviews -->
    <section class="section">
      <h2>What Our Customers Say</h2>
      <div class="reviews">
        <div class="review-card">
          <img src="https://randomuser.me/api/portraits/men/41.jpg" alt="User 1">
          <div class="name">Rohit Sharma</div>
          <p>“Food is always fresh, hot, and delivered on time. 5 stars for the biryani!”</p>
        </div>
        <div class="review-card">
          <img src="https://randomuser.me/api/portraits/women/37.jpg" alt="User 2">
          <div class="name">Priya Kapoor</div>
          <p>“I love how easy it is to order. The website is clean and user-friendly.”</p>
        </div>
        <div class="review-card">
          <img src="https://randomuser.me/api/portraits/men/78.jpg" alt="User 3">
          <div class="name">Ankit Mehra</div>
          <p>“The desserts are heavenly. Chef Anjali is a genius!”</p>
        </div>
      </div>
    </section>

  </div>

  <!-- Footer -->
  <?php include 'utils/footer.php';?>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BizShowcase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="landingpage.css">
  
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-primary" href="#"><h1><img src="images/Logo.jpg" alt="Logo" class="mb-2" style="height: 40px;"> BizShowcase</h1></a>
      <div class="ms-auto">
        <button class="btn btn-outline-primary me-2" onclick="window.location.href='signup.php';">Sign Up</button>
        <button class="btn btn-primary" onclick="window.location.href='login.php';">Log In</button>
      </div>
    </div>
  </nav>

  <!-- Hero Background Section -->
  <div class="background-hero">
    <?php
    $layers = [
      ['camera.jpeg', 'Mouse.webp', 'flower.jpg','yellowcar.jpg', 'white shoe.jpg', 'smart watch.jpg','simple bag.jpg', 'apple-iphone-14-pro.jpg', 'blackboots.jpg','AppleiPhone14Pro__1__01.jpg', 'gold watch.jpg', 'Keyboard.jpg'],
      ['Ps5.jpg', 'Headphone.jpg', 'cat eye.jpg','Auto54.png', 'black channel.jpg', 'Monitor.webp','apple watch.jpg', 'Headphone.jpg', 'bluepolo.jpg','yellowflow.jpg', 'wireless.jpg', 'trendy dress.jpg'],
      ['Motherboard.webp', 'nikon cam.jpg', 'yellow clip.jpg','tecno-camon.jpg', 'sling bag.jpg', 'OIP.jpg','red car.jpg', 'SHOEROOM-06.jpg', 'techo camon.jpg','purple headphone.jpg', 'pinkflower.jpg', 'khaki dress.jpg'],
      ['GPU.jpg', 'yellowglasses.jpeg', 'Keyboard.jpg','cyber shot.jpg', 'dior glass.jpg', 'bouquet.webp','cooljacket.webp', 'brownshoe.jpg', 'bluepolo copy.jpg','bloom slip.jpg', 'black headphone.jpg', 'brown dress.jpg']
    ];

    $positions = [0, 250, 500, 750];
    $directions = ['move-left', 'move-right'];

    foreach ($layers as $i => $images) {
      $direction = $directions[$i % 2];
      $top = $positions[$i];
      echo "<div class='floating-layer $direction' style='top: {$top}px'>";
      for ($j = 0; $j < 2; $j++) {
        foreach ($images as $img) {
          echo "<div class='zoom-wrapper'><a href='product.php?image=$img'><img src='images/$img' alt='bg image'></a></div>";
        }
      }
      echo "</div>";
    }
    ?>

    <!-- Hero Content (Centered over background) -->
    <div class="hero">
      <h1>shop</h1>
      <div class="search-box">
        <form method="GET" action="search.php">
          <div class="input-group">
            <input type="text" class="form-control form-control-lg" placeholder="Search Product" name="query">
            <button class="btn btn-primary" type="submit">Search</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scrollable Marketing Content Section -->
<section class="py-5 bg-light">
  <div class="container">
    <!-- Section 1 -->
    <div class="row mb-5 align-items-center">
      <div class="col-md-6">
        <h1>Discover the Power of BizShowcase</h1>
        
      </div>
      <div class="col-md-6 d-flex justify-content-center">
      <h5 class="text-muted">BizShowcase empowers shop owners to present their products in an engaging preview format. Customers can interact by leaving feedback and rating, enchancing the shopping experience. With detailed shop information, users can easily find and connect with local business.</h5>
      </div>
    </div>

    <!-- Cards Row -->
    <div class="row text-center mb-5">
      <div class="col-md-4 mb-4">
        <img src="images/1.png" alt="Icon" class="mb-3" style="height: 250px;">
        <h5>Engaging Customers with Interactive Feedback</h5>
        <p class="text-muted">Receive valuable insights through customer comments and ratings.</p>
      </div>
      <div class="col-md-4 mb-4">
        <img src="images/2.png" alt="Icon" class="mb-3" style="height: 250px;">
        <h5>Showcase Your Products with Ease</h5>
        <p class="text-muted">Upload product previews and descriptions effortlessly.</p>
      </div>
      <div class="col-md-4 mb-4">
        <img src="images/3.png" alt="Icon" class="mb-3" style="height: 250px;">
        <h5>Find Local Shops and Products Quickly</h5>
        <p class="text-muted">Utilize our search feature to filter by category.</p>
      </div>
    </div>

    <!-- Section 2 -->
    <div class="row mb-5 align-items-center">
      <div class="col-md-6 order-md-2">
        <img src="images/4.png" alt="Collaboration" class="section-image">
      </div>
      <div class="col-md-6">
        <h2>Unlock Your Business Potential with Us</h2>
        <p class="text-muted">The BizShowcase elevates your business visibility and fosters customer engagement.
                                <br>Showcase your products and connect with your audience like never before.</p>
        <div class="row">
          <div class="col-sm-6"><br>
            <h4>📈 Increased Visibility</h4> <br>
            <p class="text-muted small">Reach a broader audience and enhance your shop’s presence in the digital marketplace.</p>
          </div>
          <div class="col-sm-6"><br>
            <h4>💬 Customer Engagement</h4><br>
            <p class="text-muted small">Encourage customer interaction through comments and ratings to build loyalty and trust.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3 -->
    <div class="mb-5 text-center">
      <h2>Discover How BizShowcase Enhances Your Business</h2>
      <p class="text-muted">BizShowcase empowers shop owners to effortlessly display their products while engaging with
                        customers. Customers can explore offerings, provide feedback, and connect with businesses in a
                         vibrant online community. 

 </p> <br>
      <div class="row mt-4">
        <div class="col-md-4 mb-4">
          <h6>🛍️ For Shop Owners: Simple Setup Process</h6>
          <p class="text-muted small">Easily register your shop and upload product previews for approval.</p>
        </div>
        <div class="col-md-4 mb-4">
          <h6>👥 For Customers: Engaging Shopping Experience</h6>
          <p class="text-muted small">Browse product, leave comments, and rate your favorites.</p>
        </div>
        <div class="col-md-4 mb-4">
          <h6>🔒 Admin Oversight: Ensuring Quality and Security</h6>
          <p class="text-muted small">Admins monitor activities to maintain a safe platform, and approve registrations.</p>
        </div>
      </div>
      <button class="btn btn-outline-primary">See More</button>
    </div>

    <!-- CTA -->
    <div class="text-center my-5">
      <h2>Showcase Your Business Today</h2>
      <p class="text-muted">Join our platform to enhance your visibility and connect with customers like never before. </p>
      <a href="#" class="btn btn-primary me-2">Register</a>
      <a href="#" class="btn btn-outline-secondary">Explore</a>
    </div>

    <!-- Quote Section -->
    <div class="text-center my-5">
      <img src="images/Logo.jpg" alt="BizShowcase" class="mb-3" style="height: 40px;">
      <blockquote class="blockquote">
        <p class="mb-0 fst-italic">"“The BizShowcase has transformed how we engage with our customers. it’s game-changer for visibility and interaction"</p>
        <footer class="blockquote-footer mt-2">Happy Local Business Owner</footer>
      </blockquote>
    </div>

    <div class="row mb-5 align-items-center">
      <div class="col-md-6">
        <h1>Stay Updated with BizShowcase</h1>
        <p>Subscribe for the latest news and updates!</p>
        
      </div>
      <div class="col-md-6 d-flex justify-content-center">
      <form>
            <div class="input-group">
              <input type="email" class="form-control" placeholder="Enter your Email;">
              <button class="btn btn-primary" type="submit">Sign Up</button>
            </div>
          </form>
      </div>
    </div>

    <!-- Footer -->
    <footer class="pt-5 border-top">
      <div class="row">
        <div class="col-md-3 mb-3">
          <img src="images/Logo.jpg" alt="Logo" class="mb-2" style="height: 40px;">
        </div>
        <div class="col-md-3 mb-3">
          <h6>Quick Links</h6>
          <ul class="list-unstyled text-muted small">
            <li><a href="user/aboutUs.php">About Us</a></li>
            <li><a href="user/contactUs.php">Contact Us</a></li>
            <li><a href="#">FAQs</a></li>
            <li><a href="#">Support</a></li>
            <li><a href="#">Blog</a></li>
          </ul>
        </div>
        <div class="col-md-3 mb-3">
          <h6>Resources</h6>
          <ul class="list-unstyled text-muted small">
            <li><a href="#">Guides</a></li>
            <li><a href="#">Webinars</a></li>
            <li><a href="#">Case Studies</a></li>
            <li><a href="#">Testimonials</a></li>
            <li><a href="#">Events  </a></li>
          </ul>
        </div>
        <div class="col-md-3 mb-3">
          <h6>Stay Updated</h6>
          <ul class="list-unstyled text-muted small">
            <li><a href="#">Newsletter</a></li>
            <li><a href="#">Special Offers</a></li>
            <li><a href="#">New Arrivals</a></li>
            <li><a href="#">Feedback</a></li>
            <li><a href="#">Joins Us</a></li>
          </ul>
        </div>
      </div>
    </footer>
  </div>
</section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Right Ad -->
<div id="adRight" class="floating-ad" style="right: 20px;">
  <div class="close-btn"><button onclick="document.getElementById('adRight').style.display='none'">&times;</button></div>
  <a href="https://your-ad-link.com" target="_blank">
    <img src="images/red car.jpg" alt="Right Ad">
    <img src="images/yellowcar.jpg" alt="Right Ad">
    <img src="images/Auto54.png" alt="Right Ad">
  </a>
</div>

<!-- Left Ad -->
<div id="adLeft" class="floating-ad" style="left: 20px;">
  <div class="close-btn"><button onclick="document.getElementById('adLeft').style.display='none'">&times;</button></div>
  <a href="https://your-ad-link.com" target="_blank">
    <img src="images/apple-iphone-14-pro.jpg" alt="Left Ad">
    <img src="images/phone.png" alt="Left Ad">
    <img src="images/AppleiPhone14Pro__1__01.jpg" alt="Left Ad">
  </a>
</div>

<script>
  window.addEventListener('load', () => {
    // Left ad pops up after 2 seconds
    setTimeout(() => {
      const adLeft = document.getElementById('adLeft');
      adLeft.style.display = 'block';
    }, 2000);

    // Right ad pops up after 3 seconds
    setTimeout(() => {
      const adRight = document.getElementById('adRight');
      adRight.style.display = 'block';
    }, 3000);
  });
</script>




</body>
</html>

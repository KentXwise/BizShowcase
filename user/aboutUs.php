<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="BizShowcase connects you to quality products, services, and innovation. Discover our journey and values." />
  <title>BizShowcase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .about-us {
      padding: 4rem 0;
      background-color: #e6f0fa; /* Light blue background for consistency */
    }
    .about-us h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #343a40;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    .about-us h2 {
      font-size: 1.75rem;
      font-weight: 600;
      color: #343a40;
      margin-top: 2rem;
      margin-bottom: 1rem;
    }
    .about-us p {
      font-size: 1.1rem;
      color: #495057;
      line-height: 1.6;
    }
    .about-card {
      background-color: #ffffff;
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .about-card p, .about-card ul {
      margin-bottom: 0.75rem;
    }
    .about-card a {
      color: #0d6efd;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .about-card a:hover {
      color: #0056b3;
      text-decoration: underline;
    }
    .values-list li {
      font-size: 1.1rem;
      color: #495057;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .values-list i {
      font-size: 1.25rem;
      color: #0d6efd;
    }
  </style>
</head>
<body>
  <header>
    <?php include("header.php"); ?>
  </header>

  <section class="about-us">
    <div class="container">
      <h1>About Us</h1>
      <p class="text-center mb-4">Welcome to our company, where innovation and excellence drive everything we do. Established in 2015, we have grown into a trusted name in the industry, dedicated to providing top-quality products and services to our valued customers worldwide.</p>

      <div class="about-card">
        <h2>Our Story</h2>
        <p>Our journey began with a simple idea: to create a platform that connects people with the resources they need to succeed. From humble beginnings, we have expanded our reach, thanks to the unwavering support of our community and the hard work of our dedicated team. Over the years, we have embraced cutting-edge technology and innovative practices to stay ahead in a competitive market.</p>
      </div>

      <div class="about-card">
        <h2>Our Values</h2>
        <ul class="values-list list-unstyled">
          <li><i class="bi bi-shield-check"></i><strong>Integrity:</strong> We uphold the highest standards of honesty and transparency in all our dealings.</li>
          <li><i class="bi bi-lightbulb"></i><strong>Innovation:</strong> We continuously seek new ways to improve and adapt to the evolving needs of our customers.</li>
          <li><i class="bi bi-heart"></i><strong>Customer Focus:</strong> Our customers are at the heart of everything we do, and we strive to exceed their expectations.</li>
          <li><i class="bi bi-people"></i><strong>Teamwork:</strong> We believe in the power of collaboration and the strength of a united team.</li>
        </ul>
      </div>

      <div class="about-card">
        <h2>Our Team</h2>
        <p>Our diverse and talented team comprises experts from various fields, including technology, customer service, and product development. Each member brings unique skills and perspectives, contributing to our collective success. We foster a culture of learning and growth, ensuring that our team remains at the forefront of industry trends.</p>
      </div>

      <div class="about-card">
        <h2>Our Commitment</h2>
        <p>We are committed to sustainability and social responsibility. We actively participate in community initiatives and strive to minimize our environmental impact through eco-friendly practices. Our goal is to build a better future for the next generation while continuing to serve our customers with dedication and passion.</p>
      </div>

      <p class="text-center mt-4">Thank you for choosing us as your partner in progress. We look forward to continuing this journey with you!</p>
    </div>
  </section>

  <footer>
    <?php include("footer.php"); ?>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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
    .contact-us {
      padding: 4rem 0;
      background-color: #e6f0fa; /* Changed to light blue */
    }
    .contact-us h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #343a40;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    .contact-us h2 {
      font-size: 1.75rem;
      font-weight: 600;
      color: #343a40;
      margin-top: 2rem;
      margin-bottom: 1rem;
    }
    .contact-us p {
      font-size: 1.1rem;
      color: #495057;
      line-height: 1.6;
    }
    .contact-card {
      background-color: #ffffff;
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .contact-card p {
      margin-bottom: 0.75rem;
    }
    .contact-card a {
      color: #0d6efd;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .contact-card a:hover {
      color: #0056b3;
      text-decoration: underline;
    }
    .social-links li {
      margin-bottom: 0.5rem;
    }
    .social-links a {
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .social-links i {
      font-size: 1.25rem;
    }
  </style>
</head>
<body>
  <header>
    <?php include("header.php"); ?>
  </header>

  <section class="contact-us">
    <div class="container">
      <h1>Contact Us</h1>
      <p class="text-center mb-4">We’re here to help you every step of the way. Whether you have questions about our services, need technical support, or want to explore partnership opportunities, our team is ready to assist you.</p>

      <div class="contact-card">
        <h2>Get in Touch</h2>
        <p><i class="bi bi-envelope me-2"></i><strong>Email:</strong> <a href="mailto:bizshowcase@gmail.com">bizshowcase@gmail.com</a></p>
        <p><i class="bi bi-telephone me-2"></i><strong>Phone:</strong> +1 (415) 555-0123 (Monday to Friday, 9:00 AM - 6:00 PM PST)</p>
        <p><i class="bi bi-geo-alt me-2"></i><strong>Address:</strong> 5678 Tech Lane, Suite 200, San Francisco, CA 94107, USA</p>
      </div>

      <div class="contact-card">
        <h2>Support Options</h2>
        <p>For immediate assistance, check our <a href="/faqs">FAQs</a> or reach out to our support team. For business inquiries, please use the email above or fill out the form on our <a href="/support">Support</a> page.</p>
      </div>

      <div class="contact-card">
        <h2>Follow Us</h2>
        <p>Stay connected with the latest updates and news by following us on:</p>
        <ul class="social-links list-unstyled">
          <li><a href="https://twitter.com/example" target="_blank"><i class="bi bi-twitter"></i> Twitter</a></li>
          <li><a href="https://linkedin.com/company/example" target="_blank"><i class="bi bi-linkedin"></i> LinkedIn</a></li>
        </ul>
      </div>

      <p class="text-center mt-4">We look forward to hearing from you and supporting your success!</p>
    </div>
  </section>

  <footer>
    <?php include("footer.php"); ?>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
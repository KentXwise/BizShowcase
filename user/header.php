<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$user = get_user_info($conn, $user_id);

// Set default values if user data is not found
$username = ($user && isset($user['first_name'], $user['last_name'])) 
    ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') 
    : 'Guest';
$profile_picture = ($user && !empty($user['profile_picture'])) 
    ? htmlspecialchars($user['profile_picture'], ENT_QUOTES, 'UTF-8') 
    : 'img/default_profile.jpg'; // Fallback image if no profile picture
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <title>Header</title>
  <link rel="stylesheet" href="css/header.css">
</head>
<body>
  <header class="header container-fluid">
    <div class="row align-items-center w-100">
      <div class="col d-flex align-items-center">
        <div class="logo-container">
          <img src="img/Logo.png" alt="Logo" class="logo">
        </div>
      </div>
      <div class="col d-flex justify-content-end align-items-center gap-2">
        <button class="notification-btn" onclick="window.location.href='notification.php'">
          <i class="fas fa-bell icon"></i>
        </button>
        <a href="myprofile.php" style="text-decoration: none; color: inherit;">
          <span><?php echo $username; ?></span>
        </a>
        <a href="myprofile.php">
          <img src="<?php echo $profile_picture; ?>" class="profile-imgg" alt="Profile Picture">
        </a>
      </div>
    </div>
  </header>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
</html>
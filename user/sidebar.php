<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <nav class="menu-links">
        <button class="toggle-btn d-none d-md-flex">
            <i class="fas fa-bars icon"></i>
            <span class="link-text">Menu</span>
        </button>
        <a href="home.php"><i class="fas fa-home icon"></i><span class="link-text">Home</span></a>
        <a href="profile.php"><i class="fas fa-user icon"></i><span class="link-text">My Profile</span></a>
        <a href="settings.php"><i class="fas fa-cog icon"></i><span class="link-text">Settings</span></a>
        <a href="add-post.php"><i class="fas fa-plus-circle icon"></i><span class="link-text">Add Post</span></a>
        <a href="subscription.php"><i class="fas fa-credit-card icon"></i><span class="link-text">Subscription</span></a>
        <a href="payment.php"><i class="fas fa-wallet icon"></i><span class="link-text">Payment</span></a>
        <a href="index.php"><i class="fas fa-sign-out-alt icon"></i><span class="link-text">Log Out</span></a>
    </nav>
</aside>

<!-- Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const isCollapsed = sidebar.classList.contains('collapsed');

        if (window.innerWidth > 991) {
            // Desktop: Toggle between collapsed and expanded
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', !isCollapsed);
            overlay.style.display = 'none'; // Overlay not needed on desktop
        } else {
            // Mobile: Toggle visibility
            if (sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebar.classList.add('collapsed');
                overlay.style.display = 'none';
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                sidebar.classList.add('show');
                sidebar.classList.remove('collapsed');
                overlay.style.display = 'block';
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }
    }

    function initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        if (window.innerWidth > 991) {
            // Desktop: Respect stored state
            sidebar.classList.add('show');
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
            overlay.style.display = 'none';
        } else {
            // Mobile: Hidden by default unless explicitly shown
            if (isCollapsed || !sidebar.classList.contains('show')) {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            } else {
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('show');
                overlay.style.display = 'block';
            }
        }
    }

    // Add event listener to toggle button
    document.querySelector('.toggle-btn').addEventListener('click', (e) => {
        e.preventDefault();
        toggleSidebar();
    });

    // Handle sidebar links (do not collapse on click, just navigate)
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.stopPropagation();
            // On mobile, close the sidebar after clicking a link
            if (window.innerWidth <= 991) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('show');
                sidebar.classList.add('collapsed');
                overlay.style.display = 'none';
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        });
    });

    // Handle overlay click to close sidebar on mobile
    document.querySelector('.sidebar-overlay').addEventListener('click', () => {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('show');
        sidebar.classList.add('collapsed');
        localStorage.setItem('sidebarCollapsed', 'true');
        document.querySelector('.sidebar-overlay').style.display = 'none';
    });

    // Handle resize events
    window.addEventListener('resize', initializeSidebar);

    // Initialize sidebar on load
    window.addEventListener('load', initializeSidebar);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>SolMusic | HTML Template</title>
    <meta charset="UTF-8">
    <meta name="description" content="SolMusic HTML Template">
    <meta name="keywords" content="music, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="shortcut icon"/>
    <!-- Font Awesome CDN for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i&display=swap" rel="stylesheet">
 
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/index.css"/>
    <link rel="stylesheet" href="css/genre.css"/>
    <link rel="stylesheet" href="css/artist.css"/>
    <link rel="stylesheet" href="css/album.css"/>
    <link rel="stylesheet" href="css/about.css"/>
    <link rel="stylesheet" href="css/contact.css"/>
    <link rel="stylesheet" href="css/signup and sigin.css"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
    
    <!-- Header section -->
    <header class="header-section clearfix">
        <a href="index.html" class="site-logo">
            <img src="img/logo.png" alt="SolMusic Logo">
        </a>

        <!-- Mobile menu toggle button -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="header-right" id="headerRight">
            <span>|</span>

            <ul class="main-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="video.php">Video</a></li>
                <li><a href="music.php">Music</a></li>
                <li class="has-submenu">
                    <a href="#">Browse</a>
                    <ul class="sub-menu">
                        <li><a href="genere.php">Genere</a></li>
                        <li><a href="artist.php">Artist</a></li>
                        <li><a href="album.php">Album</a></li>
                    </ul>
                </li>
                <li><a href="contact.php">Contact</a></li>
            </ul>

            <div class="user-panel">
               <?php
                if (isset($_SESSION['user_id'])) {
                    echo '<a href="logout.php" class="login">Logout</a>';
                } else {
                    echo '<a href="account.php" class="register">Create an account</a>';
                }
               ?>
            </div> 
        </div>
    </header>
    <!-- header end -->

    <!-- JavaScript for mobile menu toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const headerRight = document.getElementById('headerRight');
            
            mobileMenuToggle.addEventListener('click', function() {
                headerRight.classList.toggle('active');
                
                // Change icon based on menu state
                const icon = this.querySelector('i');
                if (headerRight.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close mobile menu when clicking on a link
            const menuLinks = document.querySelectorAll('.main-menu a, .user-panel a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    headerRight.classList.remove('active');
                    mobileMenuToggle.querySelector('i').classList.remove('fa-times');
                    mobileMenuToggle.querySelector('i').classList.add('fa-bars');
                });
            });
        });
    </script>

</body>
</html>
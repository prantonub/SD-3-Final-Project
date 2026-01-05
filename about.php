<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Electronic Shop | About Us</title>

    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; }
        :root { 
            --bs-primary: #007bff; 
            --bs-primary-dark: #0056b3;
            --bs-secondary-bg: #e9ecef;
            --bs-text-color: #212529;
            --bs-taka-color: #dc3545;
        }

        .top-navbar { background-color: var(--bs-secondary-bg) !important; font-size: 0.85rem; color: var(--bs-text-color); padding: 8px 3rem; display:flex; justify-content:space-between; align-items:center; }
        .top-navbar .icons a { color: var(--bs-text-color); text-decoration:none; margin-left:15px; transition:color 0.2s; }
        .top-navbar .icons a:hover { color: var(--bs-primary); }

        .main-navbar { background-color: var(--bs-primary) !important; }
        .navbar-brand span { font-family:'Merriweather', serif; font-weight:700; }

        #about h3, #why-us h3 { color: var(--bs-primary-dark); font-weight:700; }
        #about p, #why-us p { font-size: 0.95rem; color: #555; }
        #why-us .col-md-4 i { margin-bottom: 10px; }
        #offer i { font-size: 2rem; color: var(--bs-primary); margin-bottom:10px; }

        #about img { max-width:100%; border-radius:8px; }
        #subscribe { background-color: var(--bs-primary); color:white; border:none; padding:8px 20px; margin-left:10px; border-radius:4px; }

        footer { background-color: var(--bs-primary-dark); color:#adb5bd; }
        footer a { color:#adb5bd; text-decoration:none; }
        footer a:hover { color:white; }
    </style>
</head>
<body>

<!-- Top Navbar -->
<div class="top-navbar">
    <div>
        <p class="mb-0 fw-bold">Welcome to Electronic Shop</p>
    </div>
    <div class="icons d-flex">
        <?php if(isset($_SESSION['user_name'])): ?>
            <span style="margin-right:10px;">🥰 Welcome, <?=htmlspecialchars($_SESSION['user_name'])?></span>
            <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm main-navbar">
    <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">
            <img src="./images/logo.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
            <span>Electronic Shop BD</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a class="nav-link" href="index.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="shop.php">Shop</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="product-reviews.php">Reviews</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="about.php">About</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="contact.php">Contact</a>
    </li>
            </ul>
            <form class="d-flex" id="search" action="search_results.php" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Search products..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- About Section -->
<div class="container py-5" id="about">
    <h3 class="text-center">ABOUT US</h3>
    <hr style="width:50px; border:2px solid var(--bs-primary); margin:auto" />
    <div class="row mt-5 align-items-center">
        <div class="col-md-7">
            <h4>Welcome to <span style="color: var(--bs-primary-dark); font-weight:bold;">Electronic Shop</span></h4>
            <p class="mt-3">Our story began with a simple passion: to bring the best and latest electronics to your doorstep. We believe in providing top-quality gadgets, exceptional service, and a shopping experience that's both seamless and secure.</p>
            <p>From the newest smartphones and cutting-edge laptops to high-performance cameras and essential home appliances, we curate a selection that meets all your electronic needs. Every product in our store is chosen for its quality, durability, and innovation.</p>
            <a href="#" class="btn btn-dark mt-3" style="width:150px;">Learn More</a>
        </div>
        <div class="col-md-5 text-center">
            <img src="./images/background.png" alt="Our Team">
        </div>
    </div>
</div>

<!-- Why Choose Us Section -->
<div class="container mt-5 text-center" id="why-us">
    <h3 class="mb-4">Why Choose Us?</h3>
    <div class="row">
        <div class="col-md-4">
            <i class="fa-solid fa-cart-shopping fa-3x text-primary"></i>
            <h5 class="mt-3">Wide Selection</h5>
            <p>We offer a vast range of products from leading brands to suit every budget and need.</p>
        </div>
        <div class="col-md-4">
            <i class="fa-solid fa-truck fa-3x text-success"></i>
            <h5 class="mt-3">Fast & Reliable Delivery</h5>
            <p>Get your products delivered quickly and safely, right to your home.</p>
        </div>
        <div class="col-md-4">
            <i class="fa-solid fa-shield-alt fa-3x text-danger"></i>
            <h5 class="mt-3">Secure Payments</h5>
            <p>Shop with confidence, knowing your transactions are protected.</p>
        </div>
    </div>
</div>

<!-- Footer -->
<!-- Footer -->
    <footer class="mt-5" style="background-color: var(--bs-primary-dark); color: #adb5bd;">
        <div class="container py-4 py-md-5">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="text-white mb-3">Electronic Shop BD</h5>
                    <p style="font-size: 0.9rem;">Your source for the latest electronics in Bangladesh. Quality products,
                        competitive prices, and reliable service.</p>
                    <p style="font-size: 0.9rem;"><i class="fas fa-map-marker-alt me-2"></i> 12/A, Dakshinkhan, Dhaka-1230</p>
                    <p style="font-size: 0.9rem;"><i class="fas fa-envelope me-2"></i> <a href="mailto:support@electronicshopbd.com" class="text-decoration-none" style="color: #adb5bd;">support@electronicshopbd.com</a></p>
                </div>

                <div class="col-md-2 mb-3 mb-md-0">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none" style="color: #adb5bd;">Home</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-decoration-none" style="color: #adb5bd;">Shop All</a>
                        </li>
                        <li class="mb-2"><a href="about.php" class="text-decoration-none" style="color: #adb5bd;">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-decoration-none" style="color: #adb5bd;">Contact</a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h5 class="text-white mb-3">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="my_account.php" class="text-decoration-none" style="color: #adb5bd;">My Account</a>
                        </li>
                        <li class="mb-2"><a href="track_order.php" class="text-decoration-none" style="color: #adb5bd;">Track
                                Order</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none" style="color: #adb5bd;">Delivery
                                Info</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none" style="color: #adb5bd;">Refund
                                Policy</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5 class="text-white mb-3">Follow Us</h5>
                    <div class="social-icons fs-4">
                <a href="https://www.facebook.com/profile.php?id=61554376059009" target="_blank" class="text-light me-3">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://x.com/electrostore14" target="_blank" class="text-light me-3">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/electronicstorshop/" target="_blank" class="text-light me-3">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.linkedin.com/company/electronicshop/" target="_blank" class="text-light">
                    <i class="fab fa-linkedin"></i>
                </a>
                    </div>
                </div>
            </div>

            <hr class="mt-4 mb-3" style="border-top: 1px solid #495057;">

            <div class="text-center">
                <p class="mb-0" style="font-size: 0.85rem;">&copy; <?= date('Y') ?> Electronic Shop. All rights reserved.</p>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

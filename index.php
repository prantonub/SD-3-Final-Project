<?php
session_start();
include 'includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronic Shop BD | Home</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        /* Define a consistent primary color for branding (Strong Blue) */
        :root {
            --bs-primary: #007bff; 
            --bs-primary-dark: #0056b3;
            --bs-secondary-bg: #e9ecef;
            --bs-text-color: #212529;
            --bs-taka-color: #dc3545; /* Red for price, similar to original, but consistent */
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        /* Top Navigation Bar Styling */
        .top-navbar {
            background-color: var(--bs-secondary-bg) !important;
            font-size: 0.85rem;
            color: var(--bs-text-color);
            padding: 8px 3rem; /* Added padding for better look */
        }

        .top-navbar .icons a {
            color: var(--bs-text-color);
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.2s;
        }

        .top-navbar .icons a:hover {
            color: var(--bs-primary);
        }

        /* Main Navigation Bar Styling */
        .navbar-brand span {
            font-family: 'Merriweather', serif;
            font-weight: 700;
        }

        .main-navbar {
            background-color: var(--bs-primary) !important;
        }

        /* Hero Section Styling */
        .hero-section {
            min-height: 450px;
            display: flex;
            align-items: center;
            /* Placeholder for background image */
            background-image: url('./images/background.png'); 
            background-size: cover;
            background-position: center;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        }

        /* Product Card Styling (Retaining hover effect) */
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 8px; /* Added slight rounding */
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
        }

        .product-card .btn-dark {
            background-color: var(--bs-primary-dark);
            border-color: var(--bs-primary-dark);
            transition: background-color 0.2s, border-color 0.2s;
        }

        .product-card .btn-dark:hover {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .product-card .card-img-top {
            max-height: 200px;
            object-fit: contain;
            padding: 15px;
        }
        
        .product-price {
            color: var(--bs-taka-color); /* Applied consistent color for price */
            font-weight: 700;
        }
        /* bg style */


        /*  */
    </style>
</head>

<body>

    <div class="top-navbar d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="d-flex mb-1 mb-md-0">
            <p class="mb-0 text-dark fw-bold me-3">Welcome to Electronic Shop</p>
            <p class="mb-0 text-dark">| Hotline: <a href="tel:+8801787771585" class="text-decoration-none text-dark fw-bold">+880 1787771585</a></p>
        </div>
        <div class="icons d-flex">
            <?php if (isset($_SESSION['user_name'])): ?>
                <span style="margin-right: 10px;">🥰 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="cart.php" style="margin-right: 10px;"><i class="fas fa-shopping-cart"></i> My Cart</a> 
                <a href="login.php" style="margin-right: 10px;"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>

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
        <a class="nav-link active" href="index.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="shop.php">Shop</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="product-reviews.php">Reviews</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="about.php">About</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="contact.php">Contact</a>
    </li>
</ul>

                <form class="d-flex" id="search" action="search_results.php" method="GET">
                    <input class="form-control me-2" type="search" name="query" placeholder="Search products..."
                        aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    
    <?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
        <div class="alert alert-success text-center alert-dismissible fade show mb-0" role="alert">
            Login successful! Welcome to the store.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <!-- added bg -->

    <div class="hero-section text-center py-5 ">
        <div class="container">
            <h1 class="display-3" style="font-weight: 700;">
                Power Up Your Life
            </h1>
            <p class="lead">
                Discover the latest gadgets and appliances at unbeatable prices.
            </p>
            <a href="#products-section" class="btn btn-warning btn-lg mt-3" style="font-weight: bold;">Shop Now <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    

    <div class="container my-5">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <i class="fas fa-shipping-fast fa-3x mb-2 text-info"></i>
                <h5 class="fw-bold">Fast Delivery</h5>
                <p class="text-muted">Delivery all over Bangladesh.</p>
            </div>
            <div class="col-md-4 mb-3">
                <i class="fas fa-money-check-alt fa-3x mb-2 text-success"></i>
                <h5 class="fw-bold">Cash on Delivery</h5>
                <p class="text-muted">Available in major cities.</p>
            </div>
            <div class="col-md-4 mb-3">
                <i class="fas fa-shield-alt fa-3x mb-2 text-warning"></i>
                <h5 class="fw-bold">1 Year Warranty</h5>
                <p class="text-muted">Manufacturer's warranty on most products.</p>
            </div>
        </div>
    </div>
    <hr class="my-5">

    <div id="products-section" class="container my-5">
        <div class="container bg-white shadow-lg p-4 rounded">
            <h2 class="text-center mb-5" style="color: var(--bs-primary-dark); font-weight: 700;">Featured Products
            </h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                
                <?php
                // Check if the database connection object exists
                if (isset($conn)) {
                    // SQL query to fetch all products
                    $sql = "SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 12"; // Limit to 12 for featured
                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        // Display error if query fails
                        echo "<p class='text-center w-100 text-danger'>Error fetching products: " . mysqli_error($conn) . "</p>";
                    } else if (mysqli_num_rows($result) == 0) {
                        // Display message if no products are found
                        echo "<p class='text-center w-100 text-muted'>No featured products are available at the moment.</p>";
                    } else {
                        // Loop through products and display each one
                        while ($row = mysqli_fetch_assoc($result)):
                ?>
                <div class="col">
                    <div class="card h-100 product-card shadow-sm border">
                        <img src="./images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title text-dark"><?= htmlspecialchars($row['name']) ?></h5>
                            <div class="star mb-2 text-warning">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <h4 class="product-price">৳ <?= number_format($row['price'], 2) ?></h4> 

                            <form method="post" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']) ?>">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['name']) ?>">
                                <input type="hidden" name="product_price" value="<?= htmlspecialchars($row['price']) ?>">
                                <input type="hidden" name="product_image" value="<?= htmlspecialchars($row['image']) ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-dark mt-2">
                                    <i class="fa fa-cart-shopping"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php 
                        endwhile; 
                    }
                    mysqli_close($conn); // Close the connection after fetching
                } else {
                    // Display message if connection object is missing
                    echo "<p class='text-center w-100 text-danger'>Database connection failed. Please check 'includes/db.php'.</p>";
                } 
                ?>

            </div>

            <div class="text-center mt-5">
                <a href="shop.php" class="btn btn-lg" style="background-color: var(--bs-primary); color: white;">View All Products <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <hr>

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
<?php
session_start();
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop All Products | Electronic Shop BD</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        :root {
            --bs-primary: #007bff;
            --bs-primary-dark: #0056b3;
            --bs-secondary-bg: #e9ecef;
            --bs-text-color: #212529;
            --bs-taka-color: #dc3545;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        .top-navbar {
            background-color: var(--bs-secondary-bg);
            font-size: 0.85rem;
            padding: 8px 3rem;
        }

        .top-navbar .icons a {
            color: var(--bs-text-color);
            text-decoration: none;
            margin-left: 12px;
        }

        .top-navbar .icons a:hover {
            color: var(--bs-primary);
        }

        .main-navbar {
            background-color: var(--bs-primary) !important;
        }

        .navbar-brand span {
            font-family: 'Merriweather', serif;
            font-weight: 700;
        }

        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 8px;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .product-card img {
            max-height: 200px;
            object-fit: contain;
            padding: 15px;
        }

        .product-price {
            color: var(--bs-taka-color);
            font-weight: 700;
        }

        .btn-dark {
            background-color: var(--bs-primary-dark);
            border: none;
        }

        .btn-dark:hover {
            background-color: var(--bs-primary);
        }

        footer {
            background-color: var(--bs-primary-dark);
            color: #adb5bd;
        }

        footer a {
            color: #adb5bd;
            text-decoration: none;
        }

        footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>

<!-- Top Navbar -->
<div class="top-navbar d-flex justify-content-between align-items-center">
    <p class="mb-0 fw-bold">Welcome To Electronic Shop</p>
    <div class="icons">
        <?php if (isset($_SESSION['user_name'])): ?>
            <span class="me-2">🥰 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
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
<nav class="navbar navbar-expand-lg navbar-dark main-navbar shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="./images/logo.png" width="30" height="24">
            <span>Electronic Shop BD</span>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
    <li class="nav-item">
        <a class="nav-link" href="index.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="shop.php">Shop</a>
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
            <form class="d-flex" action="search_results.php" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Search products...">
                <button class="btn btn-outline-light">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- Shop Section -->
<div class="container my-5">
    <h2 class="text-center mb-5 fw-bold" style="color: var(--bs-primary-dark);">
        Shop All Products
    </h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        if (isset($conn)) {
            $sql = "SELECT id, name, price, image FROM products ORDER BY id DESC";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col">
            <div class="card h-100 product-card shadow-sm">
                <img src="./images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="card-img-top">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <h4 class="product-price">৳ <?= number_format($row['price'], 2) ?></h4>

                    <form method="post" action="cart.php">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['name']) ?>">
                        <input type="hidden" name="product_price" value="<?= $row['price'] ?>">
                        <input type="hidden" name="product_image" value="<?= htmlspecialchars($row['image']) ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-dark mt-2">
                            <i class="fas fa-cart-shopping"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
                endwhile;
            else:
                echo "<p class='text-center text-muted'>No products available.</p>";
            endif;

            mysqli_close($conn);
        }
        ?>
    </div>
</div>

<!-- Footer -->
<footer class="mt-5">
    <div class="container py-4">
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Electronic Shop. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

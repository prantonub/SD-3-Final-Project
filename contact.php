<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Electronic Shop BD</title>

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

        .contact-card {
            border: 1px solid #dee2e6;
        }

        .contact-card h2 {
            color: var(--bs-primary-dark);
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
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm main-navbar">
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
        <a class="nav-link" href="shop.php">Shop</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="product-reviews.php">Reviews</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="about.php">About</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="contact.php">Contact</a>
    </li>
            </ul>
            <form class="d-flex" action="search_results.php" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Search products...">
                <button class="btn btn-outline-light">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- Contact Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card contact-card shadow-sm p-4">
                <h2 class="text-center mb-3">Contact Us</h2>
                <p class="text-center text-muted mb-4">
                    Have any questions or need help? Send us a message and we’ll respond shortly.
                </p>

                <form action="submit_contact.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="5" class="form-control" required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

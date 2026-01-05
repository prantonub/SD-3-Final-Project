<?php
session_start();
include 'includes/db.php';

/* =======================
   SELECT PRODUCT
======================= */
$product_id = intval($_POST['product_id'] ?? 0);
$product_name = "";
$avg_rating = 0;
$total_reviews = 0;

/* =======================
   FETCH SELECTED PRODUCT
======================= */
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM products WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $product = $res->fetch_assoc();
        $product_name = $product['name'];

        // Average rating
        $avg_stmt = $conn->prepare("
            SELECT AVG(rating) avg_rating, COUNT(*) total_reviews 
            FROM reviews 
            WHERE product_id=?
        ");
        $avg_stmt->bind_param("i", $product_id);
        $avg_stmt->execute();
        $avg = $avg_stmt->get_result()->fetch_assoc();

        $avg_rating = round($avg['avg_rating'] ?? 0, 1);
        $total_reviews = $avg['total_reviews'] ?? 0;
    }
}

/* =======================
   FETCH ALL PRODUCTS
======================= */
$all_products = $conn->query("SELECT id, name FROM products");

/* =======================
   FETCH ALL CUSTOMER REVIEWS
======================= */
$reviews_stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, r.user_name, p.name AS product_name, p.image
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Product Review System | Electronic Shop BD</title>

<!-- Bootstrap & Font Awesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body {background:#f8f9fa;font-family:system-ui;}
.star-active{color:#ffc107;}
.star-inactive{color:#e4e5e9;}
.review-form{background:#fff;border-radius:10px;border:1px solid #e9ecef;padding:25px;margin-top:20px;}
.review-card{background:#fff;border-radius:12px;padding:20px;border:1px solid #e9ecef;margin-top:15px;}
.review-card:hover{box-shadow:0 6px 18px rgba(0,0,0,.06);}
.avatar{width:42px;height:42px;border-radius:50%;background:#0d6efd;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;}
.product-img{width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #ddd;}
.top-navbar {background:#e9ecef;font-size:0.85rem;padding:8px 2rem;}
.top-navbar .icons a {margin-left:12px;text-decoration:none;color:#212529;}
.top-navbar .icons a:hover {color:#007bff;}
.main-navbar {background:#007bff !important;}
.main-navbar .navbar-brand span {font-weight:700;font-family:'Merriweather', serif;}
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
<nav class="navbar navbar-expand-lg navbar-dark main-navbar shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="./images/logo.png" width="30" height="24">
            <span>Electronic Shop BD</span>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
    <li class="nav-item"><a class="nav-link active" href="product-reviews.php">Reviews</a></li>
    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
</ul>
            <form class="d-flex" action="search_results.php" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Search products...">
                <button class="btn btn-outline-light">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- Container -->
<div class="container my-5">
    <h3 class="fw-bold mb-4">Product Review System</h3>

    <!-- SELECT PRODUCT -->
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Select Product</label>
            <select class="form-select" name="product_id" onchange="this.form.submit()">
                <option value="">-- Select Product --</option>
                <?php while($p = $all_products->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= ($product_id == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>

    <?php if ($product_id > 0 && $product_name): ?>
    <!-- SELECTED PRODUCT -->
    <h4 class="fw-bold mt-4"><?= htmlspecialchars($product_name) ?></h4>

    <!-- AVERAGE RATING -->
    <div class="mb-3">
        <strong>Average Rating:</strong>
        <?php for($i=1;$i<=5;$i++): ?>
            <i class="fa-star <?= $i <= round($avg_rating) ? 'fa-solid star-active' : 'fa-regular star-inactive' ?>"></i>
        <?php endfor; ?>
        (<?= $avg_rating ?> / 5 from <?= $total_reviews ?> reviews)
    </div>

    <!-- WRITE REVIEW FORM -->
    <div class="review-form">
        <h5 class="fw-bold mb-3">Write a Review</h5>
        <form method="post" action="submit-review.php">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" class="form-control" name="user_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Rating</label>
                <select class="form-select" name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="5">★★★★★ Excellent</option>
                    <option value="4">★★★★☆ Good</option>
                    <option value="3">★★★☆☆ Average</option>
                    <option value="2">★★☆☆☆ Poor</option>
                    <option value="1">★☆☆☆☆ Terrible</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Review</label>
                <textarea class="form-control" name="comment" rows="4" required></textarea>
            </div>
            <button class="btn btn-primary">Submit Review</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ALL CUSTOMER REVIEWS -->
    <h4 class="fw-bold mt-5">All Customer Reviews</h4>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if($reviews->num_rows > 0): ?>
        <?php while($r = $reviews->fetch_assoc()): ?>
        <div class="col">
            <div class="review-card h-100">
                <div class="d-flex align-items-center mb-3">
<img src="images/<?= htmlspecialchars($r['image']) ?>" class="product-img me-3" alt="<?= htmlspecialchars($r['product_name']) ?>">
<strong><?= htmlspecialchars($r['product_name']) ?></strong>

                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-2"><?= strtoupper($r['user_name'][0]) ?></div>
                    <div>
                        <strong><?= htmlspecialchars($r['user_name']) ?></strong><br>
                        <small class="text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></small>
                    </div>
                </div>
                <div class="mb-2">
                    <?php for($i=1;$i<=5;$i++): ?>
                        <i class="fa-star <?= $i <= $r['rating'] ? 'fa-solid star-active' : 'fa-regular star-inactive' ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-muted small mb-0">“<?= htmlspecialchars($r['comment']) ?>”</p>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
    <div class="col-12">
        <div class="alert alert-info text-center">No customer reviews yet.</div>
    </div>
    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

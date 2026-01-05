<?php
session_start();
include 'includes/db.php'; // ডাটাবেজ কানেকশন

// ব্যবহারকারী লগইন করা আছে কিনা তা নিশ্চিত করুন
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_details = null;
$order_items = [];
$error_message = '';

if ($order_id > 0) {
    // 1. অর্ডারের মূল বিস্তারিত তথ্য ফেচ করুন
    $stmt_order = $conn->prepare("SELECT id, user_id, total, status, order_date FROM orders WHERE id = ? AND user_id = ?");
    $stmt_order->bind_param("ii", $order_id, $user_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();

    if ($result_order->num_rows > 0) {
        $order_details = $result_order->fetch_assoc();

        // 2. এই অর্ডারের অন্তর্ভুক্ত পণ্যগুলো ফেচ করুন
        $stmt_items = $conn->prepare("
            SELECT oi.quantity, oi.price, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        while ($row_item = $result_items->fetch_assoc()) {
            $order_items[] = $row_item;
        }
        $stmt_items->close();
    } else {
        $error_message = "<div class='alert alert-danger'>Order not found or you do not have permission to view this order.</div>";
    }
    $stmt_order->close();
} else {
    $error_message = "<div class='alert alert-danger'>Invalid Order ID.</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet" />
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
    <div class="icons d-flex align-items-center">
        <?php if(isset($_SESSION['user_name'])): ?>
            <span class="me-3">🥰 Welcome, <?=htmlspecialchars($_SESSION['user_name'])?></span>
            <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="cart.php" class="ms-2"><i class="fas fa-shopping-cart"></i> My Cart</a>
            <a href="logout.php" class="ms-2"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a>
            <a href="login.php" class="ms-2"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="register.php" class="ms-2"><i class="fas fa-user-plus"></i> Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm main-navbar bg-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="./images/logo.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top me-2">
            <span>Electronic Shop BD</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="product-reviews.php">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            </ul>
            <form class="d-flex" id="search" action="search_results.php" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Search products..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- Order Confirmation Content -->
<div class="container mt-5">
    <h2 class="mb-4 text-center">Order Confirmation</h2>

    <?php if (!empty($error_message)): ?>
        <?= $error_message ?>
    <?php elseif ($order_details): ?>
        <div class="card p-4 shadow-sm">
            <div class="text-center mb-4">
                <i class="fas fa-check-circle fa-5x text-success"></i>
                <h3 class="mt-3 text-success">Your Order Has Been Placed Successfully!</h3>
                <p class="lead">Thank you for your purchase.</p>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Order Details</h5>
                    <p><strong>Order ID:</strong> #<?= htmlspecialchars($order_details['id']) ?></p>
                    <p><strong>Order Date:</strong> <?= htmlspecialchars(date('F j, Y, g:i a', strtotime($order_details['order_date']))) ?></p>
                    <p><strong>Order Status:</strong> <span class="badge bg-info"><?= htmlspecialchars($order_details['status']) ?></span></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Total Amount</h5>
                    <p class="fs-4 text-primary">৳<?= number_format($order_details['total'], 2) ?> TK</p>
                </div>
            </div>

            <?php if (!empty($order_items)): ?>
                <h5>Items in Your Order:</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img me-2">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>৳<?= number_format($item['price'], 2) ?> TK</td>
                                    <td>৳<?= number_format($item['quantity'] * $item['price'], 2) ?> TK</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-warning">No items found for this order. Please contact support if this is an issue.</p>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="index.php" class="btn btn-primary me-3">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            We could not find your order details. Please ensure you have placed an order.
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-primary">Go to Homepage</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

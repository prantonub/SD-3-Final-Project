<?php
session_start();
include 'includes/db.php'; // Make sure this path is correct for your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Add to Cart Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $update_stmt->bind_param("ii", $user_id, $product_id);
        $update_stmt->execute();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert_stmt->bind_param("ii", $user_id, $product_id);
        $insert_stmt->execute();
    }
    
    header("Location: cart.php");
    exit();
}

// --- Handle Quantity Update or Removal ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    $product_id_to_update = $_POST['product_id'];
    $action = $_POST['action']; // 'increase', 'decrease', 'remove'

    if ($action == 'increase') {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id_to_update);
        $stmt->execute();
    } elseif ($action == 'decrease') {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id_to_update);
        $stmt->execute();

        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ? AND quantity <= 0");
        $delete_stmt->bind_param("ii", $user_id, $product_id_to_update);
        $delete_stmt->execute();
    } elseif ($action == 'remove') {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id_to_update);
        $stmt->execute();
    }

    header("Location: cart.php");
    exit();
}

// --- Fetch Cart Items ---
$cart_items_db = [];
$query = "
    SELECT ci.product_id, ci.quantity, p.name, p.price, p.image
    FROM cart ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items_db[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet" />
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
    
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            line-height: 1;
            padding: 0;
            font-size: 1.2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: .25rem;
        }
        .quantity-control input[type="text"] {
            width: 50px;
            text-align: center;
            pointer-events: none;
        }
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
        <a class="nav-link" href="about.php">About</a>
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
<div class="container my-5">
    <h2 class="mb-4 text-center">🛒 Your Shopping Cart</h2>

    <?php if (!empty($cart_items_db)): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price (per item)</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                foreach ($cart_items_db as $item):
                    $total_price = $item['price'] * $item['quantity'];
                    $grand_total += $total_price;
                ?>
                <tr>
                    <td><img src="./images/<?= htmlspecialchars($item['image']) ?>" width="60" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>৳<?= number_format($item['price'], 2) ?> TK</td>
                    <td>
                        <div class="quantity-control">
                            <form action="cart.php" method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" name="update_cart" class="btn btn-secondary btn-sm">-</button>
                            </form>
                            <input type="text" class="form-control form-control-sm" value="<?= $item['quantity'] ?>" readonly>
                            <form action="cart.php" method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" name="update_cart" class="btn btn-primary btn-sm">+</button>
                            </form>
                        </div>
                    </td>
                    <td>৳<?= number_format($total_price, 2) ?> TK</td>
                    <td>
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" name="update_cart" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="text-end">Grand Total: ৳<?= number_format($grand_total, 2) ?> TK</h4>
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <p class="text-center">🛒 Your cart is currently empty.</p>
        <div class="d-flex justify-content-center mt-4">
            <a href="index.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

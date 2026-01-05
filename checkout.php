<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$cart_items_for_checkout = [];
$grand_total = 0;

// Fetch cart items
$query = "
    SELECT ci.product_id, ci.quantity, p.name, p.price, p.image, p.stock
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
        $quantity_to_order = min($row['quantity'], $row['stock']);
        if ($quantity_to_order == 0 && $row['stock'] == 0) continue;

        if ($quantity_to_order < $row['quantity']) {
            $message = "<div class='alert alert-warning'>Adjusted quantity for " . htmlspecialchars($row['name']) . " due to low stock. Please review your cart.</div>";
        }

        $cart_items_for_checkout[] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'quantity' => $quantity_to_order,
            'subtotal' => $row['price'] * $quantity_to_order
        ];
        $grand_total += $row['price'] * $quantity_to_order;
    }
} else {
    header("Location: index.php?message=" . urlencode("Your cart is empty."));
    exit();
}
$stmt->close();

// Handle order placement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $full_name = trim($_POST['full_name']);
    $shipping_address = trim($_POST['shipping_address']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $payment_method = $_POST['payment_method'];

    $bkash_account = null;
    $bkash_transaction_id = null;
    $bkash_screenshot = null;

    if ($payment_method === 'Bkash') {
    $bkash_account = trim($_POST['bkash_account'] ?? '');
    $bkash_transaction_id = trim($_POST['bkash_transaction_id'] ?? '');

    // Validate required fields
    if (empty($bkash_account) || empty($bkash_transaction_id)) {
        $message .= "<div class='alert alert-danger'>Please fill all Bkash payment details.</div>";
    }

    // Handle screenshot upload
    if (isset($_FILES['bkash_screenshot']) && $_FILES['bkash_screenshot']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['bkash_screenshot']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed_ext)) {
            $bkash_screenshot = $upload_dir . 'bkash_' . time() . '.' . $ext;

            if (!move_uploaded_file($_FILES['bkash_screenshot']['tmp_name'], $bkash_screenshot)) {
                $message .= "<div class='alert alert-danger'>Failed to upload Bkash screenshot.</div>";
            }
        } else {
            $message .= "<div class='alert alert-danger'>Invalid screenshot format. Use JPG, PNG, or WEBP.</div>";
        }
    } else {
        $message .= "<div class='alert alert-danger'>Please upload a payment screenshot for Bkash.</div>";
    }
}


    if (empty($cart_items_for_checkout) || !empty($message)) {
        if (empty($message)) $message = "<div class='alert alert-danger'>Cannot place an empty order.</div>";
    } else {
        $conn->begin_transaction();
        try {
            $order_status = ($payment_method === 'COD') ? 'Pending' : 'Payment Received';
            $order_date = date('Y-m-d H:i:s');

            $stmt_order = $conn->prepare("INSERT INTO orders 
                (user_id, total, status, order_date, full_name, shipping_address, phone_number, email, payment_method, bkash_account, bkash_transaction_id, bkash_screenshot) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt_order->bind_param(
                "idssssssssss",
                $user_id,
                $grand_total,
                $order_status,
                $order_date,
                $full_name,
                $shipping_address,
                $phone_number,
                $email,
                $payment_method,
                $bkash_account,
                $bkash_transaction_id,
                $bkash_screenshot
            );
            $stmt_order->execute();
            $order_id = $conn->insert_id;

            $stmt_order_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($cart_items_for_checkout as $item) {
                $stmt_order_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt_order_item->execute();

                $stmt_update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt_update_stock->execute();
            }

            $stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt_clear_cart->bind_param("i", $user_id);
            $stmt_clear_cart->execute();

            $conn->commit();
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>Error placing order: " . $e->getMessage() . "</div>";
        } finally {
            if (isset($stmt_order)) $stmt_order->close();
            if (isset($stmt_order_item)) $stmt_order_item->close();
            if (isset($stmt_update_stock)) $stmt_update_stock->close();
            if (isset($stmt_clear_cart)) $stmt_clear_cart->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<link rel="stylesheet" href="style.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet">
<style>
/* ===== Global ===== */
/* ===== Global ===== */
body {
    font-family: 'Roboto', sans-serif;
    background: #f4f6f9;
    color: #333;
}

h2, h4 {
    font-weight: 700;
    color: #222;
}

/* ===== Navbar ===== */
.navbar {
    border-radius: 0 0 12px 12px;
}

.navbar-brand span {
    font-weight: 600;
    margin-left: 6px;
}

.nav-link {
    font-weight: 500;
}

.nav-link:hover {
    text-decoration: underline;
}

/* ===== Container Card ===== */
.container.mt-5 {
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

/* ===== Alerts ===== */
.alert {
    border-radius: 10px;
    font-size: 14px;
}

/* ===== Order Table ===== */
.table {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}

.table thead {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: #fff;
}

.table th,
.table td {
    vertical-align: middle;
    padding: 14px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

/* ===== Product Image ===== */
.product-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}

/* ===== Form ===== */
.form-control {
    border-radius: 10px;
    padding: 10px 14px;
    border: 1px solid #ccc;
    transition: all 0.3s ease;
    font-family: 'Roboto', sans-serif;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
}

textarea.form-control {
    resize: none;
}

/* ===== Payment Method ===== */
.form-check {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid #ddd;
}

.form-check-input {
    cursor: pointer;
}

.form-check-label {
    font-weight: 500;
    cursor: pointer;
}

/* ===== Bkash Box ===== */
#bkash-info {
    border-radius: 12px;
    padding: 15px;
    background: #e7f1ff;
    border: 1px dashed #007bff;
}

#bkash-info img {
    max-width: 220px;
    border-radius: 10px;
}

/* ===== Buttons ===== */
.btn {
    border-radius: 12px;
    font-weight: 600;
    padding: 12px;
    font-family: 'Roboto', sans-serif;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #1e7e34, #155d27);
}

.btn-secondary {
    background: #6c757d;
    border: none;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .container.mt-5 {
        padding: 20px;
    }

    .table th,
    .table td {
        font-size: 14px;
        padding: 10px;
    }

    .product-img {
        width: 50px;
        height: 50px;
    }
}


</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #007bff;">
    <div class="container">
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
    <li class="nav-item"><a class="nav-link" href="index.php" style="color:white;">Home</a></li>
    <li class="nav-item"><a class="nav-link" href="shop.php" style="color:white;">Shop</a></li>
    <li class="nav-item"><a class="nav-link" href="about.php" style="color:white;">About</a></li>
    <li class="nav-item"><a class="nav-link" href="contact.php" style="color:white;">Contact</a></li>
</ul>

        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Checkout</h2>
    <?= $message ?>

    <?php if (!empty($cart_items_for_checkout)): ?>
    <div class="row">
        <div class="col-md-7">
            <h4>Order Summary</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items_for_checkout as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                    <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img me-2">
                                <?php endif; ?>
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td>৳<?= number_format($item['price'], 2) ?> TK</td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>৳<?= number_format($item['subtotal'], 2) ?> TK</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Grand Total</th>
                            <th>৳<?= number_format($grand_total, 2) ?> TK</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-md-5">
            <h4>Shipping Information</h4>
            <form action="checkout.php" method="POST" enctype="multipart/form-data" class="mt-4">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="shipping_address" class="form-label">Shipping Address</label>
                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <h4>Payment Method</h4>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                        <label class="form-check-label" for="cod">💰 Cash on Delivery</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="bkash" value="Bkash">
                        <label class="form-check-label" for="bkash">📱 Pay with Bkash (Send Money: 01787771585) </label>
                    </div>
                </div>

                <div id="bkash-info" class="alert alert-info d-none">
                    <img src="images/Bkash.jpg" alt="Bkash">
                </div>

                <div id="bkash-fields" class="d-none">
                    <div class="mb-3">
                        <label for="bkash_account" class="form-label">Customer Bkash Account Number</label>
                        <input type="text" class="form-control" id="bkash_account" name="bkash_account" placeholder="e.g. 01XXXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label for="bkash_transaction_id" class="form-label">Customer Bkash Transaction ID</label>
                        <input type="text" class="form-control" id="bkash_transaction_id" name="bkash_transaction_id" placeholder="Enter Transaction ID">
                    </div>
                    <div class="mb-3">
                        <label for="bkash_screenshot" class="form-label">Payment Screenshot</label>
                        <input type="file" class="form-control" id="bkash_screenshot" name="bkash_screenshot" accept="image/*">
                    </div>
                </div>

                <button type="submit" name="place_order" class="btn btn-success btn-lg w-100 mt-3">Place Order</button>
            </form>
            <a href="cart.php" class="btn btn-secondary btn-lg w-100 mt-2">Back to Cart</a>
        </div>
    </div>
    <?php else: ?>
        <p class="text-center">Your cart is empty. Please <a href="index.php">continue shopping</a>.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const codRadio = document.getElementById('cod');
    const bkashRadio = document.getElementById('bkash');
    const bkashInfo = document.getElementById('bkash-info');
    const bkashFields = document.getElementById('bkash-fields');

    codRadio.addEventListener('change', () => {
        bkashInfo.classList.add('d-none');
        bkashFields.classList.add('d-none');
        document.getElementById('bkash_account').required = false;
        document.getElementById('bkash_transaction_id').required = false;
        document.getElementById('bkash_screenshot').required = false;
    });

    bkashRadio.addEventListener('change', () => {
        bkashInfo.classList.remove('d-none');
        bkashFields.classList.remove('d-none');
        document.getElementById('bkash_account').required = true;
        document.getElementById('bkash_transaction_id').required = true;
        document.getElementById('bkash_screenshot').required = true;
    });
</script>
</body>
</html>

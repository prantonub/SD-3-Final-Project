<?php
session_start();

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

include '../includes/db.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: manage_orders.php?message=" . urlencode("Invalid Order ID."));
    exit();
}

$stmt_order = $conn->prepare("
    SELECT o.*, u.name AS customer_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows === 0) {
    header("Location: manage_orders.php?message=" . urlencode("Order not found."));
    exit();
}

$order = $result_order->fetch_assoc();
$stmt_order->close();

$stmt_items = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

$order_items = [];
while ($row = $result_items->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt_items->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Details - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    .product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; }
    .bkash-screenshot { max-width: 300px; height: auto; border: 1px solid #ccc; border-radius: 5px; }
    .order-header { background-color: #343a40; color: white; padding: 15px; border-radius: 8px 8px 0 0; }
    .order-section { margin-top: 20px; }
</style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-3">Order Details (#<?= htmlspecialchars($order['id']) ?>)</h2>
    <div class="order-header d-flex justify-content-between align-items-center mb-3">
        <span><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></span>
        <span><strong>Order Date:</strong> <?= date("M d, Y H:i", strtotime($order['order_date'])) ?></span>
    </div>

    <div class="order-section">
        <h4>Customer Information</h4>
        <table class="table table-bordered">
            <tr><th>Full Name</th><td><?= htmlspecialchars($order['full_name']) ?></td></tr>
            <tr><th>Shipping Address</th><td><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></td></tr>
            <tr><th>Phone Number</th><td><?= htmlspecialchars($order['phone_number']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($order['email']) ?></td></tr>
        </table>
    </div>

    <div class="order-section">
        <h4>Payment Information</h4>
        <table class="table table-bordered">
            <tr><th>Payment Method</th><td><?= isset($order['payment_method']) ? htmlspecialchars($order['payment_method']) : 'COD' ?></td></tr>
            <?php if (isset($order['payment_method']) && $order['payment_method'] === 'Bkash'): ?>
                <tr><th>Bkash Account</th><td><?= htmlspecialchars($order['bkash_account']) ?></td></tr>
                <tr><th>Transaction ID</th><td><?= htmlspecialchars($order['bkash_transaction_id']) ?></td></tr>
                <tr>
                    <th>Payment Screenshot</th>
                    <td>
                        <?php if (!empty($order['bkash_screenshot']) && file_exists('../' . $order['bkash_screenshot'])): ?>
                            <img src="../<?= htmlspecialchars($order['bkash_screenshot']) ?>" class="bkash-screenshot" alt="Bkash Screenshot">
                        <?php else: ?>
                            Not uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="order-section">
        <h4>Products in Order</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="../images/<?= htmlspecialchars($item['image']) ?>" class="product-img" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($item['price'],2) ?> TK</td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= number_format($item['price'] * $item['quantity'],2) ?> TK</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th><?= number_format($order['total'],2) ?> TK</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <a href="manage_orders.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

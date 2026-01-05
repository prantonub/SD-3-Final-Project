<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

$stmt_orders = $conn->prepare("SELECT id, total, status, order_date, full_name, shipping_address, phone_number, email FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

if ($result_orders->num_rows > 0) {
    while ($order_row = $result_orders->fetch_assoc()) {
        $order_id = $order_row['id'];

        $stmt_items = $conn->prepare("SELECT oi.quantity, oi.price, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        $items = [];
        while ($item_row = $result_items->fetch_assoc()) {
            $items[] = $item_row;
        }
        $stmt_items->close();

        $orders[] = [
            'id' => $order_id,
            'total' => $order_row['total'],
            'status' => $order_row['status'],
            'order_date' => $order_row['order_date'],
            'full_name' => $order_row['full_name'],
            'shipping_address' => $order_row['shipping_address'],
            'phone_number' => $order_row['phone_number'],
            'email' => $order_row['email'],
            'items' => $items
        ];
    }
}
$stmt_orders->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="style.css" />
<style>
body { background-color: #f8f9fa; }
.order-card { border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.06); }
.product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
.order-header { background: #212529; color: white; padding: 15px 20px; border-top-left-radius: 10px; border-top-right-radius: 10px; }
.order-footer { background-color: #f1f1f1; padding: 12px 20px; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">📦 My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">
            You haven't placed any orders yet.
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
                $status = $order['status'];
                $badge = 'secondary';
                if ($status == 'Pending') $badge = 'warning';
                elseif ($status == 'Processing') $badge = 'primary';
                elseif ($status == 'Shipped') $badge = 'info';
                elseif ($status == 'Delivered') $badge = 'success';
                elseif ($status == 'Cancelled') $badge = 'danger';
            ?>
            <div class="card order-card mb-4">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= htmlspecialchars($order['id']) ?></strong>
                        <span class="text-light small ms-2"><?= date("M d, Y", strtotime($order['order_date'])) ?></span>
                    </div>
                    <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span>
                </div>
                <div class="px-4 pt-2 order-details">
                    <p class="m-0"><strong>Recipient:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                    <p class="m-0"><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                    <p class="m-0"><strong>Phone:</strong> <?= htmlspecialchars($order['phone_number']) ?></p>
                    <p class="m-0"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                </div>
                <div class="card-body">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="d-flex align-items-center border-bottom py-2">
                            <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img me-3">
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                <small class="text-muted">Qty: <?= htmlspecialchars($item['quantity']) ?> × <?= number_format($item['price'], 2) ?> TK</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold"><?= number_format($item['quantity'] * $item['price'], 2) ?> TK</span>
                            </div>
                        </div>
                    <?php endforeach; ?>=
                </div>
                <div class="order-footer text-end">
                    <?php if ($order['status'] == 'Pending' || $order['status'] == 'Processing'): ?>
                        <a href="cancel_order.php?order_id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-sm btn-danger me-2" onclick="return confirm('Are you sure you want to cancel this order?');">
                            Cancel Order
                        </a>
                    <?php endif; ?>
                    <strong>Total: <?= number_format($order['total'], 2) ?> TK</strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

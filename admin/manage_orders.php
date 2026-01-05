<?php
session_start();

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

include '../includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Order status updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating order status: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>Invalid order status selected.</div>";
    }

    header("Location: manage_orders.php?message=" . urlencode(strip_tags($message)));
    exit();
}

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

$orders = [];
$sql = "SELECT o.id, u.name AS customer_name, o.total, o.status, o.order_date 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 10px 15px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-center mb-4">Admin Panel</h4>
<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" href="index.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="manage_products.php">
            <i class="fas fa-box"></i> Manage Products
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="manage_users.php">
            <i class="fas fa-users"></i> Manage Users
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="manage_orders.php">
            <i class="fas fa-clipboard-list"></i> Manage Orders
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="manage_contacts.php">
            <i class="fas fa-envelope"></i> Contact Messages
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </li>
</ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Orders</h1>
            </div>

            <?= $message ?>
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">No orders found in the system.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= number_format($order['total'], 2) ?> TK</td>
                                <td>
                                    <form action="manage_orders.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                        <select name="new_status" class="form-select form-select-sm d-inline-block w-auto">
                                            <?php
                                            $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                            foreach ($statuses as $status) {
                                                $selected = ($order['status'] === $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>$status</option>";
                                            }
                                            ?>
                                        </select>
                                        <button type="submit" name="update_order_status" class="btn btn-primary btn-sm ms-2">Update</button>
                                    </form>
                                </td>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td>
                                    <a href="view_order_details.php?id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

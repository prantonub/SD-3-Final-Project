<?php
session_start();

// ✅ admin session check
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// ✅ DB connection
include '../includes/db.php';

// ✅ Total Products
$result1 = $conn->query("SELECT COUNT(*) AS total_products FROM products");
$row1 = $result1->fetch_assoc();
$total_products = $row1['total_products'] ?? 0;

// ✅ Total Users
$result2 = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'");
$row2 = $result2->fetch_assoc();
$total_users = $row2['total_users'] ?? 0;

// ✅ Pending Orders
$result3 = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'pending'");
$row3 = $result3->fetch_assoc();
$pending_orders = $row3['pending_orders'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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

        <main class="col-md-10 ms-sm-auto px-md-4 content">
            <h1 class="mt-4">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h1>
            <p>This is your admin dashboard. Use the sidebar to navigate through administrative tasks.</p>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Products</div>
                        <div class="card-body">
                            <h5 class="card-title">Total Products: <?= $total_products ?></h5>
                            <p class="card-text">Manage your product listings.</p>
                            <a href="manage_products.php" class="btn btn-light btn-sm">Go to Products</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Users</div>
                        <div class="card-body">
                            <h5 class="card-title">Total Users: <?= $total_users ?></h5>
                            <p class="card-text">Manage user accounts.</p>
                            <a href="manage_users.php" class="btn btn-light btn-sm">Go to Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Orders</div>
                        <div class="card-body">
                            <h5 class="card-title">Pending Orders: <?= $pending_orders ?></h5>
                            <p class="card-text">Review and process customer orders.</p>
                            <a href="manage_orders.php" class="btn btn-light btn-sm">Go to Orders</a>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

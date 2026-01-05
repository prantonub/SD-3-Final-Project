<?php
session_start();

// অ্যাডমিন অ্যাক্সেস চেক
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

include '../includes/db.php'; // ডাটাবেজ কানেকশন

$message = '';

// --- User Role Update Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    if (in_array($new_role, ['user', 'admin'])) {
        if ($user_id == $_SESSION['admin_id'] && $new_role !== 'admin') {
            $message = "<div class='alert alert-danger'>You cannot demote your own admin account.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>User role updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating user role: " . $conn->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid role selected.</div>";
    }
    header("Location: manage_users.php?message=" . urlencode(strip_tags($message)));
    exit();
}

// --- User Deletion Logic ---
// --- User Deletion Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id_to_delete'];

    if ($user_id == $_SESSION['admin_id']) {
        $message = "<div class='alert alert-danger'>You cannot delete your own account.</div>";
    } else {
        // Step 1: Delete from cart
        $stmt_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_cart->bind_param("i", $user_id);
        $stmt_cart->execute();
        $stmt_cart->close();

        // Step 2: Delete from orders
        $stmt_orders = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $stmt_orders->close();

        // Step 3: Delete user
        $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        if ($stmt_user->execute()) {
            $message = "<div class='alert alert-success'>User deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting user: " . $conn->error . "</div>";
        }
        $stmt_user->close();
    }

    header("Location: manage_users.php?message=" . urlencode(strip_tags($message)));
    exit();
}


// URL থেকে মেসেজ চেক
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// ইউজার ফেচ
$users = [];
$sql = "SELECT id, name, email, role FROM users ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
        th, td {
            vertical-align: middle;
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
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom">
                <h2 class="h4">Manage Users</h2>
            </div>

            <?= $message ?>

            <?php if (empty($users)): ?>
                <div class="alert alert-info">No users found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                                <td>
                                    <form action="manage_users.php" method="POST" class="d-inline-block">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <select name="new_role" class="form-select form-select-sm d-inline-block w-auto">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_user_role" class="btn btn-sm btn-primary ms-2">Update Role</button>
                                    </form>

                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                        <form action="manage_users.php" method="POST" class="d-inline-block ms-2">
                                            <input type="hidden" name="user_id_to_delete" value="<?= htmlspecialchars($user['id']) ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($user['name']) ?>?');">Delete</button>
                                        </form>
                                    <?php endif; ?>
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

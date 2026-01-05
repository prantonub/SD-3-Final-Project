<?php
session_start();

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

include '../includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id_to_delete'];

    $stmt_select_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt_select_image->bind_param("i", $product_id);
    $stmt_select_image->execute();
    $stmt_select_image->bind_result($image_to_delete);
    $stmt_select_image->fetch();
    $stmt_select_image->close();

    $stmt_delete_product = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete_product->bind_param("i", $product_id);

    if ($stmt_delete_product->execute()) {
        if (!empty($image_to_delete) && file_exists("../images/" . $image_to_delete)) {
            unlink("../images/" . $image_to_delete);
        }
        $message = "<div class='alert alert-success'>Product deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting product: " . $conn->error . "</div>";
    }
    $stmt_delete_product->close();
    header("Location: manage_products.php?message=" . urlencode(strip_tags($message)));
    exit();
}

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

$products = [];
$sql = "SELECT id, name, price, image, stock, description FROM products ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
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
                    <h1 class="h2">Manage Products</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_product.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>

                <?= $message ?> <?php if (empty($products)): ?>
                    <div class="alert alert-info" role="alert">
                        No products found. Please add new products.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['id']) ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= number_format($product['price'], 2) ?> TK</td>
                                    <td><?= htmlspecialchars($product['stock']) ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="manage_products.php" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="product_id_to_delete" value="<?= htmlspecialchars($product['id']) ?>">
                                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
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

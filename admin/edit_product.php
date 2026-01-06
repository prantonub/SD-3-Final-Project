<?php
session_start();


if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

include '../includes/db.php'; // connect db

$message = '';
$product_data = null;

//
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT id, name, description, price, stock, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $product_data = $result->fetch_assoc();
    } else {
        $message = "<div class='alert alert-danger'>Product not found.</div>";
    }
    $stmt->close();
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $message = "<div class='alert alert-warning'>No product ID provided for editing.</div>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $existing_image = $_POST['existing_image'];
    $image_name = $existing_image;

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../images/";
        $uploaded_image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $uploaded_image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = array('jpg', 'png', 'jpeg', 'gif');
        if (!in_array($imageFileType, $allowed_types)) {
            $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
        } else {
            $i = 0;
            $original_image_name = pathinfo($uploaded_image_name, PATHINFO_FILENAME);
            $temp_image_name = $uploaded_image_name;
            while (file_exists($target_dir . $temp_image_name)) {
                $i++;
                $temp_image_name = $original_image_name . "_" . $i . "." . $imageFileType;
            }
            $image_name = $temp_image_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
                if (!empty($existing_image) && file_exists($target_dir . $existing_image)) {
                    unlink($target_dir . $existing_image);
                }
            } else {
                $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your new file.</div>";
            }
        }
    }

    // --- Update Database ---
    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image_name, $product_id);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Product updated successfully!</div>";
            header("Location: manage_products.php?message=" . urlencode(strip_tags($message)));
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Error updating product: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Admin</title>
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
        .product-img-preview {
            max-width: 150px;
            height: auto;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage_products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_orders.php"><i class="fas fa-clipboard-list"></i> Manage Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Product</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage_products.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>

            <?= $message ?>
            <?php if ($product_data): ?>
            <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_data['id']) ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product_data['image']) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product_data['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product_data['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product_data['price']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($product_data['stock']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Leave blank to keep current image.</small>
                    <?php if (!empty($product_data['image'])): ?>
                        <p class="mt-2">Current Image:</p>
                        <img src="../images/<?= htmlspecialchars($product_data['image']) ?>" class="product-img-preview">
                    <?php endif; ?>
                </div>
                <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
            </form>
            <?php else: ?>
                <p>No product found to edit.</p>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

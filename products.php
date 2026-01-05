<?php
session_start();
include 'includes/db.php';

// Check if a product ID is passed in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare a safe SQL query to get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id); // "i" stands for integer type
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $product = null; // No product found with this ID
    }

    $stmt->close();
} else {
    // If no ID is provided, show an error message
    $product = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <?php if ($product): ?>
        <div class="card p-4">
            <div class="row">
                <div class="col-md-6">
                    <img src="images/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <div class="col-md-6">
                    <h1 class="display-4"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="lead text-muted"><?= htmlspecialchars($product['description']) ?></p>
                    <h3 class="mt-4">Price: $<?= htmlspecialchars($product['price']) ?></h3>
                    <h5 class="mt-2">In Stock: <?= htmlspecialchars($product['stock']) ?> units</h5>
                  <form action="add_to_cart.php" method="POST">
    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
    <button type="submit" class="btn btn-primary btn-lg mt-4">Add to Cart</button>
</form>

                    <a href="javascript:history.back()" class="btn btn-secondary mt-4">Go Back</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading">Product Not Found!</h4>
            <p>The requested product could not be found. Please go back and try again.</p>
            <a href="javascript:history.back()" class="btn btn-danger mt-3">Go Back</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
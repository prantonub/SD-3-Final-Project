<?php
session_start();
include 'includes/db.php'; 

// Check if a search query is submitted
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    // Convert the user's search query to lowercase for case-insensitive search
    $search_term = '%' . strtolower(trim($_GET['query'])) . '%';

    // Prepare a safe SQL query using prepared statements
    // We also use LOWER() on the database columns to ensure case-insensitivity
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE LOWER(name) LIKE ? OR LOWER(description) LIKE ?");
    
    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $products = [];
    }

    $stmt->close();
} else {
    // If no query, show a message or display all products
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Search Results</h2>
        <?php if (isset($_GET['query']) && !empty(trim($_GET['query']))): ?>
            <p>Showing results for: **<?= htmlspecialchars($_GET['query']) ?>**</p>
        <?php endif; ?>
        
        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text">Price: $<?= htmlspecialchars($product['price']) ?></p>
                                <a href="products.php?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Sorry, no products were found matching your search.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$product_id = intval($_POST['product_id'] ?? 0);
$user_name  = trim($_POST['user_name'] ?? '');
$rating     = intval($_POST['rating'] ?? 0);
$comment    = trim($_POST['comment'] ?? '');

if ($product_id <= 0 || $rating < 1 || $rating > 5 || $user_name === '' || $comment === '') {
    die("Invalid input");
}

/* Insert review */
$stmt = $conn->prepare("
    INSERT INTO reviews (product_id, user_name, rating, comment, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("isis", $product_id, $user_name, $rating, $comment);

if ($stmt->execute()) {
    // ✅ Redirect back to the same product page
    header("Location: product-reviews.php?submitted=1&product_id=" . $product_id);
    exit;
} else {
    die("Review submission failed");
}

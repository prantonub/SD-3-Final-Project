<?php
session_start();
include 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND (status = 'Pending' OR status = 'Processing')");
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {

    header("Location: my_orders.php?status=success");
} else {
    
    header("Location: my_orders.php?status=error");
}

$stmt->close();
$conn->close();
?>
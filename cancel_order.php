<?php
session_start();
include 'includes/db.php';

// নিশ্চিত করুন যে ব্যবহারকারী লগইন করা আছে
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// URL থেকে অর্ডার আইডি নিন
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// ডাটাবেসে অর্ডারের স্ট্যাটাস 'Cancelled' হিসেবে আপডেট করুন
// এখানে নিরাপত্তা নিশ্চিত করতে ব্যবহারকারীর ID যাচাই করা হচ্ছে
$stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND (status = 'Pending' OR status = 'Processing')");
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {
    // সফল হলে My Orders পেজে ফিরে যান
    header("Location: my_orders.php?status=success");
} else {
    // কোনো সমস্যা হলে My Orders পেজে ফিরে যান
    header("Location: my_orders.php?status=error");
}

$stmt->close();
$conn->close();
?>
<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    die("Invalid access.");
}

$user_id = $_SESSION['user_id'];
$otp_entered = trim($_POST['otp']);

// Fetch OTP from database
$stmt = $conn->prepare("SELECT email_otp, otp_expires FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($otp_saved, $otp_expires);
$stmt->fetch();
$stmt->close();

// Check OTP
$current_time = date("Y-m-d H:i:s");
if($otp_entered === $otp_saved && $current_time <= $otp_expires){
    // Mark user as verified
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, email_otp = NULL, otp_expires = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    session_destroy();
    echo "OTP verified successfully! Registration complete.";
} else {
    echo "Invalid or expired OTP. Try again.";
}
?>

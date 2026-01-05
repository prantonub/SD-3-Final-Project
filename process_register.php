<?php
session_start();

// Database connection
include __DIR__ . '/includes/db.php';

// PHPMailer includes
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$name = trim($_POST['name']);
$username = trim($_POST['username']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

if (empty($name) || empty($username) || empty($phone) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required!";
    header("Location: register.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Email or Username already registered!";
    header("Location: register.php");
    exit();
}
$stmt->close();

$otp = rand(100000, 999999);
$otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $conn->prepare("INSERT INTO users (name, username, phone, email, password, email_otp, otp_expires, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssssss", $name, $username, $phone, $email, $password, $otp, $otp_expires);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $stmt->close();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';
        $mail->Password   = 'your_app_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'Electronic Shop BD');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Electronic Shop BD';
        $mail->Body    = "Hi $name,<br><br>Your OTP code: <b>$otp</b><br>Expires in 10 minutes.";

        $mail->send();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['success'] = "OTP sent to your email. Please verify.";
        header("Location: verify_email.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
        header("Location: register.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Registration failed. Please try again.";
    header("Location: register.php");
    exit();
}

$conn->close();

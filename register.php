<?php
session_start();
include __DIR__ . '/includes/db.php';

// PHPMailer
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';
require __DIR__ . '/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* =========================
   STEP 1: REGISTRATION
========================= */
if (isset($_POST['register'])) {

    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $rawPass  = $_POST['password'];

    // Empty check
    if (empty($name) || empty($username) || empty($phone) || empty($email) || empty($rawPass)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: register.php");
        exit;
    }

    /* ✅ EMAIL VALIDATION */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address!";
        header("Location: register.php");
        exit;
    }

    $domain = substr(strrchr($email, "@"), 1);
    if (!$domain || !checkdnsrr($domain, "MX")) {
        $_SESSION['error'] = "Email domain does not exist or cannot receive emails!";
        header("Location: register.php");
        exit;
    }

    /* ✅ BANGLADESH PHONE VALIDATION */
    if (!preg_match('/^\+8801[3-9][0-9]{8}$/', $phone)) {
        $_SESSION['error'] = "Phone number must be a valid Bangladesh number (+8801XXXXXXXXX)";
        header("Location: register.php");
        exit;
    }

    // Password hash
    $password = password_hash($rawPass, PASSWORD_BCRYPT);
// ✅ Check existing phone number
$stmt = $conn->prepare("SELECT id FROM users WHERE phone=?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "This phone number is already registered!";
    $stmt->close();
    header("Location: register.php");
    exit;
}
$stmt->close();

    // Check existing email or username
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email or Username already registered!";
        $stmt->close();
        header("Location: register.php");
        exit;
    }
    $stmt->close();

    // Generate OTP
    $otp = rand(100000, 999999);
    $otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users 
        (name, username, phone, email, password, email_otp, otp_expires, is_verified)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $stmt->bind_param("sssssss", $name, $username, $phone, $email, $password, $otp, $otp_expires);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Send OTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'prantonub.cse23@gmail.com';
            $mail->Password   = 'subm fndv otae mdig';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('prantonub.cse23@gmail.com', 'Electronic Shop BD');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Electronic Shop BD';
            $mail->Body = "
                Hi $name,<br><br>
                Your OTP code is: <b>$otp</b><br>
                This code will expire in 10 minutes.
            ";

            $mail->send();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['verify_email'] = $email;
            $_SESSION['success'] = "OTP sent to your email. Please verify.";

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
        }

        header("Location: register.php");
        exit;

    } else {
        $_SESSION['error'] = "Registration failed! Try again.";
        header("Location: register.php");
        exit;
    }
}

/* =========================
   STEP 2: OTP VERIFICATION
========================= */
if (isset($_POST['verify_otp'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: register.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $entered_otp = trim($_POST['otp']);

    if (empty($entered_otp)) {
        $_SESSION['error'] = "Please enter the OTP.";
        header("Location: register.php");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT email_otp, otp_expires 
        FROM users 
        WHERE id=? AND is_verified=0
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($email_otp, $otp_expires);
    $stmt->fetch();
    $stmt->close();

    if (!$email_otp) {
        $_SESSION['error'] = "No OTP found or account already verified!";
        header("Location: register.php");
        exit;
    }

    if ($entered_otp !== $email_otp) {
        $_SESSION['error'] = "Incorrect OTP!";
        header("Location: register.php");
        exit;
    }

    if (strtotime($otp_expires) < time()) {
        $_SESSION['error'] = "OTP expired. Please register again.";
        header("Location: register.php");
        exit;
    }

    // Verify user
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_verified=1, email_otp=NULL, otp_expires=NULL 
        WHERE id=?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Email verified successfully! You can now login.";
    $_SESSION['show_login_btn'] = true;

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Electronic Shop BD</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


 <style>
body {
    background: linear-gradient(180deg, #1283ceff 0%, #e9eef5 100%);
    min-height: 100vh;
}
.auth-card {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.auth-header {
    background-color: #1f2937;
    color: #ffffff;
    padding: 18px;
    text-align: center;
}

.auth-header h3 {
    margin: 0;
    font-weight: 600;
}

.card-body {
    background-color: #ffffff;
}

.form-control {
    padding-left: 42px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
}

.form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}


.input-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #6b7280;
}

.btn-primary {
    background-color: #2563eb;
    border-color: #2563eb;
    border-radius: 6px;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #1e40af;
    border-color: #1e40af;
}

.btn-success {
    border-radius: 6px;
    font-weight: 500;
}

a {
    color: #2563eb;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}
.auth-card {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background-color: rgba(255, 255, 255, 0.95); /* Slight transparency */
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}
body {
    background-image: url('images/bg.png'); /* <-- Replace with your image path */
    background-size: cover;        /* Cover entire viewport */
    background-position: center;   /* Center the image */
    background-repeat: no-repeat;  /* Don't repeat */
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

</style>


</head>

<body>
    

<div class="container d-flex align-items-center justify-content-center" 
     style="min-height:100vh; margin-left:20px;">

    <div class="col-md-6 col-lg-5">

        <div class="card shadow auth-card">
            <div class="auth-header">
                <h3 class="mb-0"><i class="fa-solid fa-cart-shopping"></i> Electronic Shop BD</h3>
                <small>Your trusted electronics partner</small>
            </div>

            <div class="card-body p-4">

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if(!isset($_SESSION['verify_email'])): ?>
                <!-- Registration Form -->
                <h5 class="text-center mb-3">Create Your Account</h5>

                <form method="POST">
                    <div class="mb-3 position-relative">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <i class="fa fa-user-tag input-icon"></i>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>

              <div class="mb-3 position-relative">
    <i class="fa fa-phone input-icon"></i>
    <input 
        type="tel"
        name="phone"
        class="form-control"
        value="+880"
        placeholder="+8801XXXXXXXXX"
        required
    >
</div>


                    <div class="mb-3 position-relative">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <button type="submit" name="register" class="btn btn-primary w-100">
                        <i class="fa fa-user-plus"></i> Register
                    </button>

                    <p class="text-center mt-3 mb-0">
                        Already have an account?
                        <a href="login.php">Login</a>
                    </p>
                </form>

                <?php else: ?>
                <!-- OTP Verification -->
                <h5 class="text-center mb-3">Verify Your Email</h5>
                <p class="text-center small text-muted">
                    OTP sent to <strong><?= $_SESSION['verify_email']; ?></strong>
                </p>

                <form method="POST">
                    <div class="mb-3 position-relative">
                        <i class="fa fa-key input-icon"></i>
                        <input type="text" name="otp" class="form-control text-center"
                               placeholder="Enter 6-digit OTP" required>
                    </div>

                    <button type="submit" name="verify_otp" class="btn btn-success w-100">
                        <i class="fa fa-check-circle"></i> Verify OTP
                    </button>
                </form>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

</body>
</html>

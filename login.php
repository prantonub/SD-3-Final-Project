<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Electronic Shop BD</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Merriweather', serif;
    background: linear-gradient(135deg, #247db9, #2971dc);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Login Card */
.login-card {
    width: 100%;
    max-width: 400px;
    background-color: #ffffff;
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s, box-shadow 0.3s;
}
.login-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
}

/* Logo */
.login-card .logo {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}
.login-card .logo img {
    width: 60px;
    height: 60px;
}

/* Heading */
.login-card h3 {
    text-align: center;
    color: #0b5ed7;
    font-weight: 700;
    margin-bottom: 8px;
}
.login-card p.subtitle {
    text-align: center;
    color: #6c757d;
    margin-bottom: 30px;
    font-size: 0.95rem;
}

/* Form Fields */
.form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
}
.form-control:focus {
    border-color: #0b5ed7;
    box-shadow: 0 0 0 0.2rem rgba(11, 94, 215, 0.25);
}

/* Buttons */
.btn-primary {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    background-color: #0b5ed7;
    border: none;
    transition: background-color 0.3s, transform 0.3s;
}
.btn-primary:hover {
    background-color: #0646a0;
    transform: translateY(-2px);
}

/* Forgot Password */
.forgot-link {
    text-align: center;
    margin-top: 15px;
}
.forgot-link a {
    color: #0b5ed7;
    font-weight: 600;
    text-decoration: none;
}
.forgot-link a:hover {
    color: #0646a0;
    text-decoration: underline;
}

/* Signup Link */
.signup-link {
    text-align: center;
    margin-top: 20px;
    font-size: 0.95rem;
}
.signup-link a {
    color: #0b5ed7;
    font-weight: 600;
    text-decoration: none;
}
.signup-link a:hover {
    color: #0646a0;
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

<!-- Login Card -->
<div class="login-card">
    <div class="logo">
        <img src="images/logo.png" alt="Logo">
    </div>
    <h3>Welcome Back!</h3>
    <p class="subtitle">Sign in to continue to your Electronic Shop account.</p>

    <!-- Login Form -->
    <form action="login_process.php" method="POST">
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email address" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </div>
    </form>

    <!-- Forgot Password -->
    <p class="forgot-link">
        <a href="#">Forgot Password?</a>
    </p>

    <!-- Signup Link -->
    <p class="signup-link">
        Don't have an account? <a href="register.php">Sign Up</a>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

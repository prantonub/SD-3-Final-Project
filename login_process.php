<?php
session_start();
include 'includes/db.php'; // connect to database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?"); 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            
            //
            if ($role !== 'admin') {
                session_regenerate_id(true); // 

                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role; 

               // 
header("Location: index.php?login=success");
exit();
            } else {
                // 
                header("Location: login.php?error=" . urlencode("Admins must login from admin panel."));
                exit();
            }
        } else {
            header("Location: login.php?error=" . urlencode("Invalid password."));
            exit();
        }
    } else {
        header("Location: login.php?error=" . urlencode("No user found with that email."));
        exit();
    }

    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}
?>

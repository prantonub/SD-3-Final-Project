<?php
session_start();
include 'includes/db.php'; // database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get & sanitize input
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        die("❌ All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Invalid email address.");
    }

    // Prepare SQL (prevent SQL injection)
    $stmt = $conn->prepare("
        INSERT INTO contact_messages (name, email, subject, message)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("❌ Database error.");
    }

    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        echo "
        <script>
            alert('✅ Your message has been sent successfully!');
            window.location.href = 'contact.php';
        </script>
        ";
    } else {
        echo "❌ Failed to send message. Try again.";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: contact.php");
    exit;
}
?>

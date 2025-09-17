<?php
session_start();
include("Connection/Config.php"); // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Check password (make sure you stored it using password_hash in signup)
        if (password_verify($password, $row['password'])) {
            // Store session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['full_name'] = $row['full_name'];

            header("Location: dashboard.php"); // redirect after login
            exit();
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
<?php
session_start();
include("Config.php");

try {
    // Gumamit ng `users` database config
    $conn = DatabaseConfig::getConnection('merchflow');

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $firstname  = trim($_POST['firstname']);
        $middlename = trim($_POST['middlename']); // optional
        $lastname   = trim($_POST['lastname']);
        $email      = trim($_POST['email']);
        $role       = trim($_POST['role']); // admin or user
        $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Build full_name
        $full_name = !empty($middlename) 
            ? "$firstname $middlename $lastname"
            : "$firstname $lastname";

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $_SESSION['alert'] = [
                "icon" => "error",
                "title" => "Signup Failed",
                "text" => "This email is already registered!",
            ];
            header("Location: signup.php");
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$full_name, $email, $password, $role])) {
            $_SESSION['alert'] = [
                "icon" => "success",
                "title" => "Account Created!",
                "html"  => "Redirecting to login page in <b></b> seconds...",
                "timer" => 3000,
                "timerProgressBar" => true,
                "didOpen" => "function() { const b = Swal.getHtmlContainer().querySelector('b'); setInterval(() => { b.textContent = Math.ceil(Swal.getTimerLeft()/1000); }, 100); }",
                "willClose" => "function() { window.location.href = 'login.php'; }"
            ];
        } else {
            $_SESSION['alert'] = [
                "icon" => "error",
                "title" => "Signup Failed",
                "text" => "Something went wrong. Please try again!"
            ];
        }

        header("Location: signup.php");
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MerchFlow Pro | Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="signup.css">

</head>
<body>
    <div class="signup-container">
        <div class="signup-left">
            <img src="Osave.png" alt="MerchFlow Logo" class="signup-logo">
            <div class="brand-title">MerchFlow Pro</div>
            <div class="brand-subtitle">Sign up for an account</div>
            <form class="signup-form" method="POST" action="signup.php">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="firstname" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="middlename" placeholder="Middle Name" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="lastname" placeholder="Last Name" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="role" placeholder="Type your Role (admin, User)" required>
                </div>
                <button type="submit" class="signup-btn">Sign Up</button>
            </form>
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
        <div class="signup-right">
            <img src="osave.png" alt="Signup Graphic" class="signup-graphic">
        </div>
    </div>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['alert']['icon'] ?>',
            title: '<?= $_SESSION['alert']['title'] ?>',
            text: <?= isset($_SESSION['alert']['text']) ? json_encode($_SESSION['alert']['text']) : "undefined" ?>,
            html: <?= isset($_SESSION['alert']['html']) ? json_encode($_SESSION['alert']['html']) : "undefined" ?>,
            timer: <?= isset($_SESSION['alert']['timer']) ? $_SESSION['alert']['timer'] : "undefined" ?>,
            timerProgressBar: <?= isset($_SESSION['alert']['timerProgressBar']) ? 'true' : 'false' ?>,
            didOpen: <?= $_SESSION['alert']['didOpen'] ?? "undefined" ?>,
            willClose: <?= $_SESSION['alert']['willClose'] ?? "undefined" ?>,
        });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>
</body>
</html>
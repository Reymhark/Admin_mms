<?php
session_start();
require_once(__DIR__ . "/Config.php");
// ✅ Make sure naka-login
if (isset($_SESSION['user_id'])) {
    // naka-login na, dalhin sa tamang page
    $redirectPage = ($_SESSION['role'] === 'admin') ? 'index.php' : 'user.php';
    header("Location: $redirectPage");
    exit;
}


// ✅ Connect to merchflow DB
$conn = DatabaseConfig::getConnection("merchflow");

$flash = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $flash = [
            'type'  => 'error',
            'title' => 'Missing Fields',
            'text'  => 'Please enter both email and password.'
        ];
    } else {
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);   // PDO style
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                // Redirect based on role
                $redirectPage = ($user['role'] === 'admin') ? 'index.php' : 'user.php';

                $flash = [
                    'type'     => 'success',
                    'title'    => 'Login Successful!',
                    'text'     => 'Welcome back, ' . $user['full_name'] . '! Redirecting...',
                    'timer'    => 3000,
                    'redirect' => $redirectPage
                ];
            } else {
                $flash = [
                    'type'  => 'error',
                    'title' => 'Invalid Password',
                    'text'  => 'Please try again.'
                ];
            }
        } else {
            $flash = [
                'type'  => 'error',
                'title' => 'Account Not Found',
                'text'  => 'No account found for that email. Please sign up first.'
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MerchFlow Pro | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="Osave.png" alt="NextGen-MMS Logo" class="login-logo">
            <div class="brand-title">NextGen-MMS</div>
            <div class="brand-subtitle">Login to your account</div>

            <form class="login-form" method="POST" action="">
                 <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="role" placeholder="Type  Role (admin, User)" required
                           value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : '' ?>">
                </div>
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="form-actions">
                <a href="forgot.php" class="forgot-link">Forgot Password?</a>
            </div>
            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up</a>
            </div>
        </div>
        <div class="login-right">
            <img src="osave.png" alt="Login Graphic" class="login-graphic">
        </div>
    </div>

    <script>
        // SweetAlert flash from PHP
        document.addEventListener('DOMContentLoaded', function () {
            const flash = <?php echo json_encode($flash, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
            if (!flash) return;

            const opts = {
                icon: flash.type || 'info',
                title: flash.title || '',
                text: flash.text || ''
            };

            if (flash.type === 'success' && flash.timer) {
                opts.timer = flash.timer;
                opts.showConfirmButton = false;
                opts.timerProgressBar = true;
            }

            function go() {
                if (flash.redirect) window.location.href = flash.redirect;
            }

            if (window.Swal && Swal.fire) {
                Swal.fire(opts).then(go);
                if (flash.type === 'success' && flash.redirect) {
                    setTimeout(go, flash.timer || 3000);
                }
            } else {
                alert((opts.title ? opts.title + '\n' : '') + (opts.text || ''));
                go();
            }
        });
    </script>
</body>
</html>
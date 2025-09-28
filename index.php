<?php
session_start();
include(__DIR__ . "/Config.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Always use full_name from session
$full_name = $_SESSION['full_name'] ?? 'User';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MerchFlow Pro | Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
</head>
<body>
        <div class="app-container">
            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-logo">
                        <img src="Logo.png" alt="MerchFlow Logo" />
                        <span>ADMINISTRATIVE</span>
                    </div>
                </div>
                <br>
                <br>
             <div class="sidebar-menu">
                    <a href="index.php" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                
                    <a href="Reserve_facilities.php" class="menu-item">
                        <i class="fas fa-building"></i>
                        <span>Facilities Reservations</span>
                    </a>
                    <a href="visitor_register.php" class="menu-item">
                        <i class="fas fa-user"></i>
                        <span>Visitor Registration</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="main-content" id="main-content">
                <!-- Navbar -->
                <nav class="navbar">
                    <div class="navbar-left">
                        <button class="toggle-sidebar" id="toggle-sidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="navbar-logo">
                            <img src="Osave.png" alt="MerchFlow Logo" />
                            <span>NEXTGENMMS</span>
                        </div>
                    </div>
                    <div class="navbar-right">
                        <div class="dropdown">
                            <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($full_name, 0, 2)); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
                                    <div class="user-role">Employee</div>
                                </div>
                            </div>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a href="#" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a></li>

                                <li><hr class="dropdown-divider"></li>
                                <li><a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </nav>

                 <!---content--->
                <div class="content">
                    <div class="content">
                        <div class="img-1" id="img-1">
                            <img src="pic1.jpg" class="img-fluid" alt="Sample Image">
                            <p>
                                O!Save opened its 400th store in Imus, Cavite last December. Founded in 2021, the company opened its first store in San Fernando, Pampanga in 2022.

                                Through its continuously growing network of stores, O!Save provides communities with access to high-quality, low-cost food, household, and personal care products.

                                “We are not just opening stores, we are also making a positive impact on our communities. Each of our 400 stores not only provides access to quality products at affordable prices, but also meaningful career opportunities. We thus look forward to growing together with our communities,” said O!Save Philippines CEO Ramon Greshake.

                                Robinsons Retail currently holds a stake in O!Save, reflecting a shared commitment to meeting the evolving needs of Filipino consumers at every price point.
                            </p>

                        </div>

                        <div class="img-2" id="img-2">
                            <img src="pic2.jpg" class="img-fluid" alt="Sample Image">
                            <img src="pic3.jpg" class="img-fluid" alt="Sample Image">
                        </div>

                </div>
            </div>
        </div>

    <!-- Bootstrap JS (fixes dropdown + hamburger) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
<?php
session_start();
require_once(__DIR__ . "/Config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Always use full_name from session
$full_name = $_SESSION['full_name'] ?? 'User';

// ✅ Gumawa ng PDO connection para sa bawat database
$pdo_legal      = DatabaseConfig::getConnection("legaldb");
$pdo_docs       = DatabaseConfig::getConnection("docbd");
$pdo_facilities = DatabaseConfig::getConnection("facilitiesdb");
$pdo_visitors   = DatabaseConfig::getConnection("visitordb");

// Legal Management Counts
$total_legal   = $pdo_legal->query("SELECT COUNT(*) FROM legal_management")->fetchColumn();
$active_legal  = $pdo_legal->query("SELECT COUNT(*) FROM legal_management WHERE status = 'Active'")->fetchColumn();
$expired_legal = $pdo_legal->query("SELECT COUNT(*) FROM legal_management WHERE status = 'Expired'")->fetchColumn();

// Visitors Today
$visitors_today = $pdo_visitors->query("
    SELECT COUNT(*) 
    FROM visitor_management
    WHERE DATE(time_in) = CURDATE()
")->fetchColumn();

// Facilities Reservations
$reservations = $pdo_facilities->query("SELECT COUNT(*) FROM facilities_reservations")->fetchColumn();

// Documents
$total_docs = $pdo_docs->query("SELECT COUNT(*) FROM document_management")->fetchColumn();
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
                <div class="sidebar-menu">
                    <div class="menu-label">Main</div>
                    <a href="#" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="legal_management.php" class="menu-item">
                        <i class="fas fa-scale-balanced"></i>
                        <span>Legal Management</span>
                    </a>
                    <a href="document_management.php" class="menu-item">
                        <i class="fas fa-file-alt"></i>
                        <span>Document Management</span>
                    </a>
                    <div class="menu-label">Management</div>
                    <a href="facilities_reservation.php" class="menu-item">
                        <i class="fas fa-building"></i>
                        <span>Facilities Reservation</span>
                    </a>
                    <a href="visitor_management.php" class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Visitor Management</span>
                    </a>
                    <div class="menu-label">OTHERS</div>
                    <a href="visitor_logs_gallery.php" class="menu-item">
                        <i class="fas fa-file"></i>
                        <span>Visitors Log</span>
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
                            <img src="Logo.png" alt="MerchFlow Logo" />
                            <span>NEXTGENMMS</span>
                        </div>
                       <div class="search-box">
                            
                        </div>
                    </div>
                    <div class="navbar-right">
                        <div class="nav-icon">
                            <i class="far fa-bell"></i>
                            <span class="nav-badge">3</span>
                        </div>
                        <div class="nav-icon">
                            <i class="far fa-envelope"></i>
                            <span class="nav-badge">5</span>
                        </div>
                        <div class="dropdown">
                            <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($full_name, 0, 2)); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
                                    <div class="user-role">Administrator</div>
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
<br>
                 <!---content--->
                   <div class="container mt-4">
                    <div class="row g-4 mb-4">
                        <!-- Total Documents Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-primary">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-file-alt"></i> Total Documents</h5>
                                    <h2 class="fw-bold"><?= $total_legal ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Archived Documents Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-danger">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-file-alt"></i> Archived Documents</h5>
                                    <h2 class="fw-bold"><?= $archived_docs ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Active Contracts Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-success">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> Active</h5>
                                    <h2 class="fw-bold"><?= $active_legal ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Expired Contracts Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-danger">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-archive"></i> Expired</h5>
                                    <h2 class="fw-bold"><?= $expired_legal ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Visitors Today Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-info">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-users"></i> Visitors Today</h5>
                                    <h2 class="fw-bold"><?= $visitors_today ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Reservations Card -->
                        <div class="col-md-4">
                            <div class="card shadow border-0 text-white bg-warning">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-bullhorn"></i> Reservations</h5>
                                    <h2 class="fw-bold"><?= $reservations ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


<br>
<br>
                        <div class="container-fluid p-4">
                            <div class="card p-3 shadow-sm">
                                <!-- Quick Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h3>
                                </div>
                                <div class="mb-3">
                                    <a href="legal_management.php" class="btn btn-primary me-2">Manage Legal</a>
                                    <a href="document_management.php" class="btn btn-success me-2">Manage Documents</a>
                                    <a href="facilities_reservation.php" class="btn btn-warning me-2">Facilities</a>
                                    <a href="visitor_management.php" class="btn btn-info">Visitors</a>
                                </div>
                                <hr class="my-4">

                                <!-- Recent Activities Card -->
                                <div class="card mb-4 shadow border-0">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activities</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- your PHP loop here -->
                                         <?php if (!empty($recent_activities)): ?>
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($recent_activities as $row): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <!-- Dynamic Icon per type -->
                                                            <?php if ($row['type'] === 'visitor'): ?>
                                                                <i class="fas fa-user text-info"></i>
                                                            <?php elseif ($row['type'] === 'legal'): ?>
                                                                <i class="fas fa-file-alt text-primary"></i>
                                                            <?php elseif ($row['type'] === 'reservation'): ?>
                                                                <i class="fas fa-calendar-check text-warning"></i>
                                                            <?php else: ?>
                                                                <i class="fas fa-file-contract text-success"></i>
                                                            <?php endif; ?>

                                                            <strong><?= htmlspecialchars($row['activity']) ?></strong>
                                                            <div class="small text-muted">
                                                                <?= htmlspecialchars($row['name']) ?> • <?= htmlspecialchars($row['date']) ?>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-muted text-center mb-0">No recent activities found.</p>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <!-- Visitors & Reservations -->
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0"><i class="fas fa-users"></i> Recent Visitors</h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted">Quick summary of recent visitors. Link to full visitor management below.</p>
                                                <a href="visitor_management.php" class="btn btn-info btn-sm">View All Visitors</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-warning text-dark">
                                                <h5 class="mb-0"><i class="fas fa-building"></i> Facilities Reservations</h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted">Upcoming reservations and quick access to manage facilities.</p>
                                                <a href="facilities_reservation.php" class="btn btn-warning btn-sm">View All Reservations</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


            </div>
        </div>

    <!-- Bootstrap JS (fixes dropdown + hamburger) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
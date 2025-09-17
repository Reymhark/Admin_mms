<?php
session_start();
include(__DIR__ . "/Config.php");

// ✅ Only allow admin to access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "You don't have access to this page!";
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Admin';

// ✅ Connect to database
$conn = DatabaseConfig::getConnection("facilitiesdb");

// ✅ Handle admin actions first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['reservation_id'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE facilities_reservations SET status = 'Approved' WHERE reservation_id = ?");
        $stmt->execute([$reservation_id]);
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE facilities_reservations SET status = 'Rejected' WHERE reservation_id = ?");
        $stmt->execute([$reservation_id]);
    } elseif ($action === 'cancel') {
        // ✅ Cancel → Auto Delete from DB
        $stmt = $conn->prepare("DELETE FROM facilities_reservations WHERE reservation_id = ?");
        $stmt->execute([$reservation_id]);
    }
}

// ✅ Now fetch updated reservations
$sql = "
    SELECT r.reservation_id, f.facility_name, r.reserved_by, 
           r.start_datetime, r.end_datetime, r.purpose, r.status
    FROM facilities_reservations r
    JOIN facilities f ON r.facility_id = f.facility_id
    ORDER BY r.start_datetime DESC
";
$reservations = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MerchFlow Pro | Facilities Reservation</title>
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
                <a href="index.php" class="menu-item ">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="legal_management.php" class="menu-item">
                    <i class="fas fa-scale-balanced"></i>
                    <span>Legal Management</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Document Management</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-box-open"></i>
                    <span>Products</span>
                </a>
                <div class="menu-label">Management</div>
                <a href="facilities_reservation.php" class="menu-item active">
                    <i class="fas fa-building"></i>
                    <span>Facilities Reservation</span>
                </a>
                <a href="visitor_management.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Visitor Management</span>
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
                        <span>MerchFlow</span>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search reservations...">
                    </div>
                </div>
                <div class="navbar-right">
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
                                <?= strtoupper(substr($full_name, 0, 2)) ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($full_name) ?></div>
                                <div class="user-role">Administrator</div>
                            </div>
                        </div>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a href="#" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
<br>
            <!-- Content -->
            <div class="container mt-4">
                <h2>Facility Reservations Management</h2>
                <hr>
<br>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Facility</th>
                            <th>Reserved By</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($reservations) > 0): ?>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['facility_name']) ?></td>
                                <td><?= htmlspecialchars($r['reserved_by']) ?></td>
                                <td><?= $r['start_datetime'] ?></td>
                                <td><?= $r['end_datetime'] ?></td>
                                <td><?= htmlspecialchars($r['purpose']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $r['status']=='Approved'?'success':
                                        ($r['status']=='Rejected'?'danger':
                                        ($r['status']=='Cancelled'?'secondary':'warning'))
                                    ?>"><?= $r['status'] ?></span>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <a href="update_reservation.php?id=<?= $r['reservation_id'] ?>&status=Approved" 
                                           class="btn btn-success btn-sm">Approve</a>
                                        <a href="update_reservation.php?id=<?= $r['reservation_id'] ?>&status=Rejected" 
                                           class="btn btn-danger btn-sm">Reject</a>
                                    <?php elseif ($r['status'] === 'Approved'): ?>
                                        <a href="update_reservation.php?id=<?= $r['reservation_id'] ?>&status=Cancelled" 
                                           class="btn btn-secondary btn-sm">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No reservations found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>

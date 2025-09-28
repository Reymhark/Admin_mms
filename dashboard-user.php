<?php
session_start();
require_once(__DIR__ . "/Config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'User';

// ✅ Connect to DB
try {
    $conn = DatabaseConfig::getConnection("facilitiesdb");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// ✅ Handle reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_facility'])) {
    $facility_id  = $_POST['facility_id'] ?? null;
    $start_date   = $_POST['start_date'] ?? null;
    $start_time   = $_POST['start_time'] ?? null;
    $end_date     = $_POST['end_date'] ?? null;
    $end_time     = $_POST['end_time'] ?? null;
    $purpose      = $_POST['purpose'] ?? null;

    if ($facility_id && $start_date && $start_time && $end_date && $end_time && $purpose) {
        $start_datetime = $start_date . " " . $start_time;
        $end_datetime   = $end_date . " " . $end_time;

        try {
            $sql = "INSERT INTO facilities_reservations 
                    (facility_id, reserved_by, start_datetime, end_datetime, purpose, status) 
                    VALUES (?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$facility_id, $full_name, $start_datetime, $end_datetime, $purpose]);

            $message = "✅ Reservation submitted successfully!";
        } catch (Exception $e) {
            $message = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Please fill in all required fields.";
    }
    header("Location: Reserve_facilities.php"); // replace with your page URL
    exit();
}

// ✅ Fetch facilities for dropdown
$facilities = [];
try {
    $stmt = $conn->query("SELECT * FROM facilities ORDER BY facility_name ASC");
    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "⚠️ Could not fetch facilities: " . $e->getMessage();
}

// ✅ Fetch user's reservations only
$my_reservations = [];
try {
    $stmt = $conn->prepare("
        SELECT r.reservation_id, f.facility_name, 
               r.start_datetime, r.end_datetime, r.purpose, r.status
        FROM facilities_reservations r
        JOIN facilities f ON r.facility_id = f.facility_id
        WHERE r.reserved_by = ?
        ORDER BY r.start_datetime DESC
    ");
    $stmt->execute([$full_name]);
    $my_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "⚠️ Could not fetch reservations: " . $e->getMessage();
}
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
                    <a href="dashboard-user.php" class="menu-item active">
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
                            <img src="Logo.png" alt="MerchFlow Logo" />
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
<br>

             <div class="container py-5">

                            <h2 class="mb-4"><i class="fas fa-building"></i> Facilities Reservation</h2>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                            <?php endif; ?>

                            <!-- Reservation Button -->
                            <div class="d-flex justify-content-end mb-4">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reservationModal">
                                    <i class="fas fa-calendar-plus"></i> Request Reservation
                                </button>
                            </div>

                            <!-- My Reservations Table -->
                            <h3 class="mb-3 text-danger"><i class=""></i> My Reservations</h3>
                            <?php if (count($my_reservations) > 0): ?>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Facility</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Purpose</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($my_reservations as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['facility_name']) ?></td>
                                            <td><?= htmlspecialchars($r['start_datetime']) ?></td>
                                            <td><?= htmlspecialchars($r['end_datetime']) ?></td>
                                            <td><?= htmlspecialchars($r['purpose']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $r['status']=='Approved'?'success':
                                                    ($r['status']=='Rejected'?'danger':
                                                    ($r['status']=='Cancelled'?'secondary':'warning'))
                                                ?>">
                                                    <?= htmlspecialchars($r['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info">No reservations found.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Reservation Modal -->
                        <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="reservationModalLabel">
                                        <i class="fas fa-calendar-plus"></i> Request Facility Reservation
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="reserve_facility" value="1">

                                        <!-- Facility Selection -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Select Facility</label>
                                            <select name="facility_id" class="form-select" required>
                                                <option value="">-- Select Facility --</option>
                                                <?php foreach ($facilities as $f): ?>
                                                    <option value="<?= htmlspecialchars($f['facility_id']) ?>">
                                                        <?= htmlspecialchars($f['facility_name']) ?> (Capacity: <?= htmlspecialchars($f['capacity']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Start Date & Time -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Start Time</label>
                                                <input type="time" name="start_time" class="form-control" required>
                                            </div>
                                        </div>

                                        <!-- End Date & Time -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">End Date</label>
                                                <input type="date" name="end_date" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">End Time</label>
                                                <input type="time" name="end_time" class="form-control" required>
                                            </div>
                                        </div>

                                        <!-- Purpose -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Purpose</label>
                                            <textarea name="purpose" class="form-control" rows="3" required></textarea>
                                        </div>

                                        <!-- Submit -->
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="fas fa-paper-plane"></i> Submit Reservation
                                        </button>
                                    </form>
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
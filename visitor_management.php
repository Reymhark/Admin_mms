<?php 
session_start();
include 'Config.php';

$full_name = $_SESSION['full_name'] ?? 'User';

// ✅ Connect DB
$conn = DatabaseConfig::getConnection("visitordb");

// =======================
// Register Visitor (Only in visitor table)
// =======================
if (isset($_POST['register_visitor'])) {
    $full_name = $_POST['full_name'];
    $contact = $_POST['contact_number'];
    $purpose_of_visit = $_POST['purpose'];

    $photoPath = "uploads/default.png";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg'];

        if (in_array($fileExt, $allowed)) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $newFileName = time() . "_" . preg_replace('/\s+/', '_', $fileName);
            $targetFile = $targetDir . $newFileName;

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $photoPath = $targetFile;
            }
        }
    }

    // Save only to visitor table
    $stmt = $conn->prepare("INSERT INTO visitor (full_name, contact_number, photo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $contact, $photoPath);
    $stmt->execute();
    $stmt->close();

    header("Location: visitor_management.php");
    exit();
}

// =======================
// Time In (Create record in visitor_management)
if (isset($_POST['register_visitor'])) {
    $full_name = $_POST['full_name'];
    $contact = $_POST['contact_number'];
    $purpose_of_visit = $_POST['purpose'];

    // Default photo
    $photoPath = "uploads/default.png";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg'];

        if (in_array($fileExt, $allowed)) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            // Create unique file name
            $newFileName = time() . "_" . preg_replace('/\s+/', '_', $fileName);
            $targetFile = $targetDir . $newFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmp, $targetFile)) {
                $photoPath = $targetFile; // save relative to project root
            }
        }
    }

    // Save to visitor table
    $stmt = $conn->prepare("INSERT INTO visitor (full_name, contact_number, photo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $contact, $photoPath);
    $stmt->execute();
    $stmt->close();

    header("Location: visitor_management.php");
    exit();
}

// =======================
// Time Out (Update record)
// =======================
if (isset($_POST['time_out'])) {
    if (!empty($_POST['record_id'])) {
        $record_id = $_POST['record_id'];

        $stmt = $conn->prepare("UPDATE visitor_management SET time_out = NOW(), status='Complete' WHERE id=? AND status='In'");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: visitor_management.php");
    exit();
}

// =======================
// Fetch Records
// =======================
$records = $conn->query("
    SELECT vm.id, vm.visitor_id, vm.full_name, vm.contact_number, v.photo, 
           vm.visit_date, vm.purpose_of_visit, vm.time_in, vm.time_out, vm.status 
    FROM visitor_management vm
    JOIN visitor v ON vm.visitor_id = v.visitor_id
    ORDER BY vm.id DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitor Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="visitor.css">
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


                <!---content--->
                        <div class="container mt-5">

                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="fw-bold text-dark"><i class="fa fa-users me-2"></i>Visitor Management</h2>
                            </div>

                            <!-- Visitor Stats -->
                            <div class="row g-4">

                                <!-- Total Visitors -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                            <i class="bi bi-people-fill fs-2"></i>
                                        </div>
                                        <h6 class="text-muted">Total Visitors</h6>
                                        <h2 class="fw-bold text-primary">
                                            <?php
                                            $sql = "SELECT COUNT(DISTINCT visitor_id) as total FROM visitor_management";
                                            $result = $conn->query($sql);
                                            $row = $result->fetch(PDO::FETCH_ASSOC);
                                            echo $row['total'];
                                            ?>
                                        </h2>
                                    </div>
                                </div>

                                <!-- Visitors Today -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                                        <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3">
                                            <i class="bi bi-calendar-day fs-2"></i>
                                        </div>
                                        <h6 class="text-muted">Visitors Today</h6>
                                        <h2 class="fw-bold text-success">
                                            <?php
                                            $today = date("Y-m-d");
                                            $sql = "SELECT COUNT(DISTINCT visitor_id) as total FROM visitor_management WHERE visit_date = '$today'";
                                            $result = $conn->query($sql);
                                            $row = $result->fetch(PDO::FETCH_ASSOC);
                                            echo $row['total'];
                                            ?>
                                        </h2>
                                    </div>
                                </div>

                                <!-- Visitors This Week -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                                        <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                            <i class="bi bi-calendar-week fs-2"></i>
                                        </div>
                                        <h6 class="text-muted">This Week</h6>
                                        <h2 class="fw-bold text-warning">
                                            <?php
                                            $monday = date("Y-m-d", strtotime("monday this week"));
                                            $sunday = date("Y-m-d", strtotime("sunday this week"));
                                            $sql = "SELECT COUNT(DISTINCT visitor_id) as total FROM visitor_management 
                                                    WHERE visit_date BETWEEN '$monday' AND '$sunday'";
                                            $result = $conn->query($sql);
                                            $row = $result->fetch(PDO::FETCH_ASSOC);
                                            echo $row['total'];
                                            ?>
                                        </h2>
                                    </div>
                                </div>

                            </div>

                            <!-- Visitor Timeline -->
                                <div class="card mt-5 shadow-sm border-0 rounded-4">
                                    <div class="card-header bg-danger text-white rounded-top-4">
                                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Visitors</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="timeline">
                                            <?php
                                            $sql = "SELECT full_name, visit_date, purpose_of_visit, time_in 
                                                    FROM visitor_management 
                                                    ORDER BY id DESC 
                                                    LIMIT 10";
                                            $stmt = $conn->query($sql);
                                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetch all rows as associative array

                                            if (!empty($rows)) {
                                                foreach ($rows as $row) {
                                                    echo '<li class="timeline-item">';
                                                    echo '<div class="timeline-icon bg-primary text-white"><i class="bi bi-person-fill"></i></div>';
                                                    echo '<div class="timeline-content">';
                                                    echo '<p><strong>' . htmlspecialchars($row['full_name']) . '</strong> - <em>' . htmlspecialchars($row['purpose_of_visit']) . '</em></p>';
                                                    echo '<span class="text-muted small"><i class="bi bi-calendar-event"></i> ' . date("M d, Y h:i A", strtotime($row['time_in'])) . '</span>';
                                                    echo '</div>';
                                                    echo '</li>';
                                                }
                                            } else {
                                                echo '<li class="timeline-item text-muted">No visitors recorded yet</li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>


                            <!-- Visitor Records Section -->
                                    <div class="mt-5">
                                        <!-- Header with Toggle -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h2 class="fw-bold text-danger mb-0">Visitor Records</h2>
                                            <button id="toggleRecordsBtn" class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#recordsSection">
                                                <i class="fas fa-eye"></i> View Records
                                            </button>
                                        </div>

                                        <!-- Visitor Records Table (collapsible) -->
                                        <div id="recordsSection" class="collapse">
                                            <div class="card mt-4 shadow-sm border-0 rounded-4">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Full Name</th>
                                                                    <th>Contact</th>
                                                                    <th>Purpose</th>
                                                                    <th>Visit Date</th>
                                                                    <th>Time In</th>
                                                                    <th>Time Out</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($records)): ?>
                                                                    <?php foreach ($records as $v): ?>
                                                                        <tr>
                                                                            <td><?php echo (int)$v['id']; ?></td>
                                                                            <td><?php echo htmlspecialchars($v['full_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($v['contact_number']); ?></td>
                                                                            <td><?php echo htmlspecialchars($v['purpose_of_visit']); ?></td>
                                                                            <td><?php echo htmlspecialchars(date("M d, Y", strtotime($v['visit_date']))); ?></td>

                                                                            <?php
                                                                                $timeIn = !empty($v['time_in']) ? date("h:i A", strtotime($v['time_in'])) : '-';
                                                                                $timeOut = !empty($v['time_out']) ? date("h:i A", strtotime($v['time_out'])) : '-';
                                                                            ?>
                                                                            <td><?php echo $timeIn; ?></td>
                                                                            <td><?php echo $timeOut; ?></td>

                                                                            <td>
                                                                                <?php
                                                                                    $status = $v['status'] ?? 'Pending';
                                                                                    if ($status === 'In') {
                                                                                        echo "<span class='badge bg-success'>In</span>";
                                                                                    } elseif ($status === 'Complete') {
                                                                                        echo "<span class='badge bg-secondary'>Complete</span>";
                                                                                    } else {
                                                                                        echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="8" class="text-center text-muted">No visitor records found.</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
             </div>

        </div>

         <script>
                                    // Toggle button text and icon dynamically
            document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById('toggleRecordsBtn');
            const collapseEl = document.getElementById('recordsSection');

            collapseEl.addEventListener('shown.bs.collapse', function () {
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Records';
        });

            collapseEl.addEventListener('hidden.bs.collapse', function () {
            btn.innerHTML = '<i class="fas fa-eye"></i> View Records';
        });

        collapseEl.addEventListener('hidden.bs.collapse', function () {
            btn.innerHTML = '<i class="fas fa-eye"></i> View Records';
        });
            </script>
    


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="main.js"></script>
</body>
</html>

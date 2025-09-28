
<?php
session_start();
include 'Config.php';

// Always use full_name from session
$full_name = $_SESSION['full_name'] ?? 'User';

try {
    $conn = DatabaseConfig::getConnection("visitordb");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

function insertLog($conn, $vm_id, $action, $description, $performed_by) {
    $stmt = $conn->prepare("INSERT INTO visitor_logs (vl_id, action, description, performed_by) 
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([$vm_id, $action, $description, $performed_by]);
}

// Handle Time In / Time Out
if (isset($_POST['action']) && isset($_POST['visitor_mgmt_id'])) {
    $vm_id = $_POST['visitor_mgmt_id'];
    $action = $_POST['action'];

    if ($action === 'time_in') {
        $stmt = $conn->prepare("UPDATE visitor_management 
                                SET time_in = NOW(), status='In' 
                                WHERE vm_id=? AND time_in IS NULL");
        $stmt->execute([$vm_id]);

        insertLog($conn, $vm_id, "Time In", "Visitor has timed in.", $performed_by);

    } elseif ($action === 'time_out') {
        $stmt = $conn->prepare("UPDATE visitor_management 
                                SET time_out = NOW(), status='Out' 
                                WHERE vm_id=? AND time_in IS NOT NULL AND time_out IS NULL");
        $stmt->execute([$vm_id]);

        insertLog($conn, $vm_id, "Time Out", "Visitor has timed out.", $performed_by);
    }

    header("Location: visitor_register.php");
    exit();
}

// =======================
// Handle visitor registration
// =======================
if (isset($_POST['register_visitor'])) {
    $visitor_name = $_POST['full_name'];
    $contact = $_POST['contact_number'];
    $purpose = $_POST['purpose'];

    $performed_by = $_SESSION['full_name'] ?? 'Admin';
    
    // Default photo
    $photoPath = "uploads/default.png";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg'];

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

    // Insert visitor (visitor table)
    $stmt = $conn->prepare("INSERT INTO visitor (full_name, contact_number, photo) VALUES (?, ?, ?)");
    $stmt->execute([$visitor_name, $contact, $photoPath]);
    $visitor_id = $conn->lastInsertId();

    // Insert visitor record (visitor_management)
    $stmt2 = $conn->prepare("INSERT INTO visitor_management 
                            (visitor_id, full_name, purpose, time_in, status) 
                            VALUES (?, ?, ?, NOW(), 'In')");
    $stmt2->execute([$visitor_id, $visitor_name, $purpose]);
    $vm_id = $conn->lastInsertId();

    // Insert log
    insertLog($conn, $vm_id, "Registered", "Visitor $visitor_name registered with purpose: $purpose", $performed_by);

    header("Location: visitor_register.php");
    exit();
}

// =======================
// Fetch all visitor records
// =======================
$stmt = $conn->query("SELECT vm.vm_id, vm.full_name, vm.purpose, 
                             vm.time_in, vm.time_out, vm.status, v.contact_number, v.photo
                      FROM visitor_management vm
                      LEFT JOIN visitor v ON vm.visitor_id = v.visitor_id
                      ORDER BY vm.vm_id DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Visitor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="index.php" class="menu-item">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                
                    <a href="Reserve_facilities.php" class="menu-item">
                        <i class="fas fa-building"></i>
                        <span>Facilities Reservations</span>
                    </a>
                    <a href="visitor_register.php" class="menu-item active">
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
<br>
                <!-- Content -->
                    <div class="container mt-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="fw-bold text-danger mb-0"></h2>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#registerVisitorModal">
                                <i class="fas fa-user-plus me-2"></i> Register Visitor
                            </button>
                        </div>

                    <div class="modal fade" id="registerVisitorModal" tabindex="-1" aria-labelledby="registerVisitorModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="registerVisitorModalLabel">Register Visitor</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="text" name="full_name" placeholder="Full Name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" name="contact_number" placeholder="Contact Number" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" name="purpose" placeholder="Purpose of Visit" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                                    </div>
                                    <button type="submit" name="register_visitor" class="btn btn-danger w-100">Register</button>
                                </form>
                            </div>
                            </div>
                        </div>
                        </div>


                               <!-- Visitor Card Display -->
                            <div class="visitor-cards">
                                <?php foreach ($records as $row): ?>
                                    <div class="visitor-card">
                                        <img src="<?= htmlspecialchars($row['photo']) ?>" alt="Visitor Photo">
                                        <h3><?= htmlspecialchars($row['full_name']) ?></h3>
                                        <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?></p>
                                        <p><strong>Purpose:</strong> <?= htmlspecialchars($row['purpose']) ?></p>
                                        <p><strong>Time In:</strong> <?= !empty($row['time_in']) ? date("h:i A", strtotime($row['time_in'])) : 'Pending' ?></p>
                                        <p><strong>Time Out:</strong> <?= !empty($row['time_out']) ? date("h:i A", strtotime($row['time_out'])) : 'Pending' ?></p>
                                        <p class="status-badge">
                                            <?php if($row['status'] === 'In'): ?>
                                                <span class="badge bg-success">In</span>
                                            <?php elseif($row['status'] === 'Out' || $row['status'] === 'Complete'): ?>
                                                <span class="badge bg-secondary">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </p>

                                        <!-- Time In / Time Out buttons -->
                                        <form method="POST" class="d-flex justify-content-center gap-2">
                                            <input type="hidden" name="visitor_mgmt_id" value="<?= $row['vm_id'] ?>">
                                            <?php if(empty($row['time_in'])): ?>
                                                <button type="submit" name="action" value="time_in" class="btn btn-primary btn-sm">Time In</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="time_out" class="btn btn-success btn-sm"
                                                    <?= !empty($row['time_out']) ? 'disabled' : '' ?>>Time Out</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                <?php endforeach; ?>

                                <?php if(empty($records)): ?>
                                    <p class="text-center text-muted">No visitors recorded yet.</p>
                                <?php endif; ?>
                            </div>



        </div>
    </div>


     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</div>
</body>
</html>

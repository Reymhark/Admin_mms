<?php
session_start();
include(__DIR__ . "/Config.php"); // DatabaseConfig should return PDO $pdo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Always use full_name from session
$full_name = $_SESSION['full_name'] ?? 'User';

// ✅ Use $pdo consistently
$pdo = DatabaseConfig::getConnection("legaldb");

// 🔹 Auto-expire contracts
$pdo->query("UPDATE legal_management SET status='Expired' WHERE expiry_date < CURDATE() AND status='Active'");

// 🔹 Fetch employees
$employees = $pdo->query("SELECT * FROM employees ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Fetch suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY supplier_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Handle Add Contract
if (isset($_POST['add_contract'])) {
    $doc_type     = $_POST['doc_type'];
    $related_to   = $_POST['related_to'];
    $reference_id = $_POST['reference_id'];
    $issued_date  = $_POST['issued_date'];
    $expiry_date  = $_POST['expiry_date'];

    // Handle file upload
    $file_path = NULL;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/contracts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $file_path = $targetFile;
        }
    }

    // Validate reference_id
    $valid = false;
    if ($related_to === 'HR4') {
        $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = ?");
        $stmt->execute([$reference_id]);
        $valid = $stmt->rowCount() > 0;
    } elseif ($related_to === 'Core2') {
        $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
        $stmt->execute([$reference_id]);
        $valid = $stmt->rowCount() > 0;
    }

    if ($valid) {
        $stmt = $pdo->prepare("INSERT INTO legal_management (doc_type, related_to, reference_id, issued_date, expiry_date, file_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->execute([$doc_type, $related_to, $reference_id, $issued_date, $expiry_date, $file_path]);

        $_SESSION['flash'] = ['success' => true, 'msg' => 'Contract added successfully!'];
        header("Location: legal_management.php");
        exit();
    } else {
        $_SESSION['flash'] = ['success' => false, 'msg' => 'Invalid employee or supplier selected.'];
        header("Location: legal_management.php");
        exit();
    }
}

// 🔹 Fetch contracts with related names
$sql = "
    SELECT lm.*, 
           CASE 
              WHEN lm.related_to='HR4' THEN e.full_name
              WHEN lm.related_to='Core2' THEN s.supplier_name
           END as related_name
    FROM legal_management lm
    LEFT JOIN employees e ON lm.related_to='HR4' AND lm.reference_id = e.id
    LEFT JOIN suppliers s ON lm.related_to='Core2' AND lm.reference_id = s.id
";
$contracts = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Summary counts
$total_legal     = $pdo->query("SELECT COUNT(*) FROM legal_management")->fetchColumn();
$active_legal    = $pdo->query("SELECT COUNT(*) FROM legal_management WHERE status = 'Active'")->fetchColumn();
$expired_legal   = $pdo->query("SELECT COUNT(*) FROM legal_management WHERE status = 'Expired'")->fetchColumn();
$terminated_legal= $pdo->query("SELECT COUNT(*) FROM legal_management WHERE status = 'Terminated'")->fetchColumn();
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
                    <a href="index.php" class="menu-item">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="legal_management.php" class="menu-item active">
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
                            <img src="Osave.png" alt="MerchFlow Logo" />
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
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3" style="margin: 0 15px; border-radius: 8px;">
                                <h3 class="mb-0"><i class="fas fa-scale-balanced"></i> Legal Management</h3>
                                <div>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#contractModal">+ Add Contract</button>
                                </div>
                            </div> 
                            <hr>
                            <br>
                            
                            <div class="row g-4 px-3 mb-4">
                               <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-primary">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-file-alt"></i> Total Documents</h5>
                                                <h2 class="fw-bold text-center"  ><?= $total_legal ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-success">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-check-circle"></i> Active</h5>
                                                <h2 class="fw-bold text-center"><?= $active_legal ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-danger">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-archive"></i> Archived</h5>
                                                <h2 class="fw-bold text-center"><?=$expired_legal  ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                 <!---content--->
                    <div class="container mt-4">

                            <br>

                            <!-- Contracts Table -->
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Document Type</th>
                                                <th>Related Department</th>
                                                <th>Employee/Supplier</th>
                                                <th>Issued</th>
                                                <th>Expiry</th>
                                                <th>Status</th>
                                                <th>File</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($contracts)): ?>
                                            <?php foreach ($contracts as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['doc_type']) ?></td>
                                                    <td><?= htmlspecialchars($row['related_to']) ?></td>
                                                    <td><?= htmlspecialchars($row['related_name'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($row['issued_date']) ?></td>
                                                    <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                                                    <td>
                                                        <?php if ($row['status'] == 'Active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php elseif ($row['status'] == 'Expired'): ?>
                                                            <span class="badge bg-danger">Expired</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Terminated</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($row['file_path'])): ?>
                                                            <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No file</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center text-muted">No contracts found.</td></tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Add Contract -->
                        <div class="modal fade" id="contractModal" tabindex="-1">
                            <div class="modal-dialog">
                                                            <form method="POST" enctype="multipart/form-data" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Contract</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <select name="doc_type" class="form-control mb-2" required>
                                        <option value="">-- Select Document Type --</option>
                                        <option value="Employee contract">Employee contract</option>
                                        <option value="Supplier contract">Supplier contract</option>
                                    </select>

                                    <select name="related_to" id="relatedTo" class="form-control mb-2" required onchange="toggleDropdown()">
                                        <option value="">-- Related To --</option>
                                        <option value="HR4">HR4 (Employee)</option>
                                        <option value="Core2">Core2 (Supplier)</option>
                                    </select>

                                    <!-- Employee dropdown -->
                                    <select name="reference_id" id="employeeSelect" class="form-control mb-2" style="display:none;">
                                        <option value="">-- Select Employee --</option>
                                        <?php foreach ($employees as $e): ?>
                                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Supplier dropdown -->
                                    <select name="reference_id" id="supplierSelect" class="form-control mb-2" style="display:none;">
                                        <option value="">-- Select Supplier --</option>
                                        <?php foreach ($suppliers as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?> - <?= htmlspecialchars($s['contact_person']) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <input type="date" name="issued_date" class="form-control mb-2" required>
                                    <input type="date" name="expiry_date" class="form-control mb-2" required>
                                    <input type="file" name="file" class="form-control mb-2">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="add_contract" class="btn btn-success">Save</button>
                                </div>
                            </form>
                            </div>
                        </div>

                     <script>
                            function toggleDropdown() {
                            const relatedTo = document.getElementById('relatedTo').value;
                            document.getElementById('employeeSelect').style.display = relatedTo === 'HR4' ? 'block' : 'none';
                            document.getElementById('supplierSelect').style.display = relatedTo === 'Core2' ? 'block' : 'none';
                        }
                    </script>


            </div>
        </div>

    <!-- Bootstrap JS (fixes dropdown + hamburger) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
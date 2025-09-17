<?php
session_start();
include(__DIR__ . "/Config.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Always use full_name from session
$full_name = $_SESSION['full_name'] ?? 'User';


// ✅ Connect to database
$pdo = DatabaseConfig::getConnection('docbd');

// Modules for upload dropdown
$modules = [
    "HR1" => ["Offers", "IDs"],
    "Core1" => ["POS"],
    "Core2" => ["Supplier Contracts"],
    "Financials" => ["Invoices"]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
    $doc_type = $_POST['doc_type'];
    $related_module = $_POST['related_module'];
    $reference_id = $_POST['reference_id'];

    if (!empty($_FILES['document']['name'])) {
    // ✅ Go up one folder (kasi nasa "admin/" yung PHP file)
    $uploadDir = __DIR__ . "/../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique file name
    $fileName = time() . "_" . basename($_FILES['document']['name']);
    $targetPath = $uploadDir . $fileName;        // full path sa disk
    $targetForDb = "uploads/" . $fileName;       // relative path for DB

    if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("
            INSERT INTO document_management (doc_type, related_module, reference_id, file_path) 
            VALUES (?,?,?,?)
        ");
        $stmt->execute([$doc_type, $related_module, $reference_id, $targetForDb]);

        $_SESSION['flash'] = ['success' => true, 'msg' => "Document uploaded!"];
    } else {
        $_SESSION['flash'] = ['success' => false, 'msg' => "File upload failed!"];
    }
}
    header("Location: document_management.php");
    exit;
}

// Handle Archive/Unarchive/Delete
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    switch ($_GET['action']) {
        case 'archive':
            $pdo->prepare("UPDATE document_management SET is_archived=1 WHERE doc_id=?")->execute([$id]);
            $_SESSION['flash'] = ['success' => true, 'msg' => "Document archived."];
            break;
        case 'unarchive':
            $pdo->prepare("UPDATE document_management SET is_archived=0 WHERE doc_id=?")->execute([$id]);
            $_SESSION['flash'] = ['success' => true, 'msg' => "Document restored."];
            break;
        case 'delete':
            $pdo->prepare("DELETE FROM document_management WHERE doc_id=?")->execute([$id]);
            $_SESSION['flash'] = ['success' => true, 'msg' => "Document deleted permanently."];
            break;
    }
    header("Location: document_management.php");
    exit;
}

// Fetch all documents
try {
    $stmt = $pdo->query("SELECT * FROM document_management ORDER BY uploaded_at DESC");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$full_name = $_SESSION['full_name'] ?? 'User';
$pdo = DatabaseConfig::getConnection('docbd');

// 📊 Dashboard Counts
$totalDocs = $pdo->query("SELECT COUNT(*) FROM document_management")->fetchColumn();
$totalArchived = $pdo->query("SELECT COUNT(*) FROM document_management WHERE is_archived = 1")->fetchColumn();
$totalActive = $pdo->query("SELECT COUNT(*) FROM document_management WHERE is_archived = 0")->fetchColumn();

// 📑 Fetch All Documents
$stmt = $pdo->query("SELECT * FROM document_management ORDER BY uploaded_at DESC");
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <a href="legal_management.php" class="menu-item">
                        <i class="fas fa-scale-balanced"></i>
                        <span>Legal Management</span>
                    </a>
                    <a href="document_management.php" class="menu-item active">
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

                 <!---content--->
                        <div class="container py-5">
                            <h2 class="mb-4 text-start"><i class="fas fa-folder"></i> Document Management</h2>
                            <hr>
                            <br>
                            <!-- Flash Message -->
                            <?php if (isset($_SESSION['flash'])): ?>
                                <div class="alert alert-<?= $_SESSION['flash']['success'] ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
                                </div>
                                <?php unset($_SESSION['flash']); ?>
                            <?php endif; ?>

                            <!-- Upload Document Button -->
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        <i class="fas fa-upload me-1"></i> Upload Document
                                    </button>
                                </div>


                            <!-- Upload Modal -->
                            <div class="modal fade" id="uploadModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Upload Document</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label>Related Department</label>
                                                    <select name="related_module" class="form-control" required>
                                                        <?php foreach ($modules as $module => $types): ?>
                                                            <option value="<?= htmlspecialchars($module) ?>"><?= htmlspecialchars($module) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Document Type</label>
                                                    <input type="text" name="doc_type" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Reference ID</label>
                                                    <input type="number" name="reference_id" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Upload File</label>
                                                    <input type="file" name="document" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button name="upload_doc" class="btn btn-primary">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                                <!-- 📊 Dashboard Cards -->
                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-primary">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-file-alt"></i> Total Documents</h5>
                                                <h2 class="fw-bold text-center"  ><?= $totalDocs ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-success">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-check-circle"></i> Active</h5>
                                                <h2 class="fw-bold text-center"><?= $totalActive ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow border-0 text-white bg-danger">
                                            <div class="card-body">
                                                <h5 class="card-title text-center"><i class="fas fa-archive"></i> Archived</h5>
                                                <h2 class="fw-bold text-center"><?= $totalArchived ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            

                            <!-- Documents Table -->
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>ID</th>
                                            <th>Department</th>
                                            <th>Type</th>
                                            <th>Reference ID</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Uploaded At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($documents)): ?>
                                            <?php foreach ($documents as $doc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($doc['doc_id']) ?></td>
                                                    <td><?= htmlspecialchars($doc['related_module']) ?></td>
                                                    <td><?= htmlspecialchars($doc['doc_type']) ?></td>
                                                    <td><?= htmlspecialchars($doc['reference_id']) ?></td>

                                                    <!-- ✅ Fix file path -->
                                                <td>
                                                    <a href="/Admin_mms/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>


                                                    <td><?= $doc['is_archived'] ? 'Archived' : 'Active' ?></td>
                                                    <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                                                    <td>
                                                        <?php if (!$doc['is_archived']): ?>
                                                            <a href="?action=archive&id=<?= htmlspecialchars($doc['doc_id']) ?>" class="btn btn-warning btn-sm">Archive</a>
                                                        <?php else: ?>
                                                            <a href="?action=unarchive&id=<?= htmlspecialchars($doc['doc_id']) ?>" class="btn btn-success btn-sm">Unarchive</a>
                                                        <?php endif; ?>
                                                        <a href="?action=delete&id=<?= htmlspecialchars($doc['doc_id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete permanently?')">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No documents found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                        </div>

            </div>
    </div>

    <!-- Bootstrap JS (fixes dropdown + hamburger) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
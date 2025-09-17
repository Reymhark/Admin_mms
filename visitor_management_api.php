<?php
header("Content-Type: application/json");
include "Config.php";

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ✅ Get all visitors
if ($method === "GET" && $action === "visitors") {
    $result = $conn->query("SELECT * FROM visitor ORDER BY created_at DESC");
    $visitors = [];
    while ($row = $result->fetch_assoc()) {
        $visitors[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $visitors]);
    exit();
}

// ✅ Get visitor records
if ($method === "GET" && $action === "records") {
    $result = $conn->query("
        SELECT vm.id, vm.visitor_id, vm.full_name, v.contact_number, v.photo, 
               vm.visit_date, vm.purpose_of_visit, vm.time_in, vm.time_out, vm.status 
        FROM visitor_management vm
        JOIN visitor v ON vm.visitor_id = v.visitor_id
        ORDER BY vm.id DESC
    ");
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $records]);
    exit();
}

// ✅ Register new visitor
if ($method === "POST" && $action === "register") {
    $full_name = $_POST['full_name'] ?? '';
    $contact = $_POST['contact_number'] ?? '';
    $purpose = $_POST['purpose'] ?? '';

    $photoPath = "uploads/default.png";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $newFileName = time() . "_" . preg_replace('/\s+/', '_', $fileName);
            $targetFile = $targetDir . $newFileName;
            move_uploaded_file($fileTmp, $targetFile);
            $photoPath = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO visitor (full_name, contact_number, purpose_of_visit, photo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $contact, $purpose, $photoPath);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Visitor registered"]);
    exit();
}

// ✅ Time In
if ($method === "POST" && $action === "time-in") {
    $visitor_id = $_POST['visitor_id'] ?? null;
    if (!$visitor_id) {
        echo json_encode(["status" => "error", "message" => "No visitor selected"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT full_name, purpose_of_visit FROM visitor WHERE visitor_id = ?");
    $stmt->bind_param("i", $visitor_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $purpose);
    $stmt->fetch();
    $stmt->close();

    $visit_date = date('Y-m-d');
    $stmt2 = $conn->prepare("INSERT INTO visitor_management (visitor_id, full_name, visit_date, purpose_of_visit, time_in, status) VALUES (?, ?, ?, ?, NOW(), 'In')");
    $stmt2->bind_param("isss", $visitor_id, $full_name, $visit_date, $purpose);
    $stmt2->execute();

    echo json_encode(["status" => "success", "message" => "Visitor timed in"]);
    exit();
}

// ✅ Time Out
if ($method === "POST" && $action === "time-out") {
    $record_id = $_POST['record_id'] ?? null;
    if (!$record_id) {
        echo json_encode(["status" => "error", "message" => "No record selected"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE visitor_management SET time_out = NOW(), status = 'Complete' WHERE id = ? AND status = 'In'");
    $stmt->bind_param("i", $record_id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Visitor timed out"]);
    exit();
}

// ❌ Default response
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit();
?>

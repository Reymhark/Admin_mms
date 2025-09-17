<?php
// legal_management_api.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include(__DIR__ . "/Config.php"); // database connection

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

// ---------------- GET ----------------
if ($method === "GET") {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM legal_management WHERE legal_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo json_encode($result ? ["status"=>"success","message"=>"Record fetched","data"=>$result] : ["status"=>"error","message"=>"Record not found"]);
    } else {
        $res = $conn->query("SELECT * FROM legal_management ORDER BY legal_id DESC");
        $records = [];
        while ($row = $res->fetch_assoc()) $records[] = $row;
        echo json_encode(["status"=>"success","message"=>"Records fetched","data"=>$records]);
    }
    exit;
}

// ---------------- POST ----------------
elseif ($method === "POST") {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    $fullName    = "";
    $branch      = "";
    $docType     = "";
    $docTitle    = "";
    $description = "";
    $issuedDate  = "";
    $expiryDate  = "";
    $upload_document = "";

    if (strpos($contentType, 'application/json') !== false) {
        // JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $fullName    = $input['full_name'] ?? '';
        $branch      = $input['store_branch'] ?? '';
        $docType     = $input['document_type'] ?? '';
        $docTitle    = $input['document_title'] ?? '';
        $description = $input['description'] ?? '';
        $issuedDate  = $input['issued_date'] ?? '';
        $expiryDate  = $input['expiry_date'] ?? '';
    } else {
        // form-data
        $fullName    = $_POST['full_name'] ?? '';
        $branch      = $_POST['store_branch'] ?? '';
        $docType     = $_POST['document_type'] ?? '';
        $docTitle    = $_POST['document_title'] ?? '';
        $description = $_POST['description'] ?? '';
        $issuedDate  = $_POST['issued_date'] ?? '';
        $expiryDate  = $_POST['expiry_date'] ?? '';

        if (isset($_FILES['upload_document']) && $_FILES['upload_document']['error'] === 0) {
            $fileName = $_FILES['upload_document']['name'];
            $fileTmp  = $_FILES['upload_document']['tmp_name'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['png','jpg','jpeg','pdf','doc','docx'];
            if (in_array($fileExt, $allowedExtensions)) {
                $targetDir = "docs/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                $newFileName = uniqid() . "_" . $fileName;
                $targetPath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $upload_document = $targetPath;
                }
            }
        }
    }

    // Validation
    if (!$fullName || !$branch || !$docType || !$docTitle) {
        echo json_encode(["status"=>"error","message"=>"Required fields missing"]);
        exit;
    }

    // Insert DB
    $stmt = $conn->prepare("INSERT INTO legal_management (full_name, store_branch, document_type, document_title, description, issued_date, expiry_date, upload_document) VALUES (?,?,?,?,?,?,?,?)");
    if (!$stmt) {
        echo json_encode(["status"=>"error","message"=>"Prepare failed: ".$conn->error]);
        exit;
    }
    $stmt->bind_param("ssssssss", $fullName, $branch, $docType, $docTitle, $description, $issuedDate, $expiryDate, $upload_document);
    if ($stmt->execute()) {
        echo json_encode([
            "status"=>"success",
            "message"=>"Record added successfully",
            "data"=>[
                "legal_id"=>$stmt->insert_id,
                "full_name"=>$fullName,
                "store_branch"=>$branch,
                "document_type"=>$docType,
                "document_title"=>$docTitle,
                "description"=>$description,
                "issued_date"=>$issuedDate,
                "expiry_date"=>$expiryDate,
                "upload_document"=>$upload_document
            ]
        ]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Insert failed: ".$stmt->error]);
    }
    exit;
}

// ---------------- DELETE ----------------
elseif ($method === "DELETE") {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($input['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM legal_management WHERE legal_id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) echo json_encode(["status"=>"success","message"=>"Record deleted"]);
        else echo json_encode(["status"=>"error","message"=>"Delete failed"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"ID required"]);
    }
    exit;
}

// ---------------- PUT ----------------
elseif ($method === "PUT") {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) { echo json_encode(["status"=>"error","message"=>"ID required"]); exit; }

    $fields = [];
    $params = [];
    $types  = "";

    $columns = ['full_name','store_branch','document_type','document_title','description','issued_date','expiry_date','upload_document'];
    foreach ($columns as $col) {
        if (isset($input[$col])) {
            $fields[] = "$col=?";
            $params[] = $input[$col];
            $types .= "s";
        }
    }

    if (count($fields) === 0) { echo json_encode(["status"=>"error","message"=>"No fields to update"]); exit; }

    $params[] = $id;
    $types .= "i";

    $sql = "UPDATE legal_management SET ".implode(", ", $fields)." WHERE legal_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) echo json_encode(["status"=>"success","message"=>"Record updated"]);
    else echo json_encode(["status"=>"error","message"=>"Update failed"]);
    exit;
}

// ---------------- METHOD NOT ALLOWED ----------------
else {
    echo json_encode(["status"=>"error","message"=>"Method not allowed"]);
    exit;
}
?>

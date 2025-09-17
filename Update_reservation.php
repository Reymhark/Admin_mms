<?php
session_start();
include(__DIR__ . "/Config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Only admin can update reservations
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "You don't have access to this page!";
    exit;
}

// Connect to DB
$conn = DatabaseConfig::getConnection("facilitiesdb");

// Get reservation ID and new status from query string
if (isset($_GET['id']) && isset($_GET['status'])) {
    $reservation_id = intval($_GET['id']);
    $new_status = $_GET['status'];

    // Only allow valid statuses
    $allowed_statuses = ['Approved', 'Rejected', 'Cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        die("❌ Invalid status update!");
    }

    try {
        $stmt = $conn->prepare("UPDATE facilities_reservations SET status = ? WHERE reservation_id = ?");
        $stmt->execute([$new_status, $reservation_id]);

        $_SESSION['flash_message'] = "✅ Reservation #$reservation_id has been updated to $new_status.";
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "❌ Error updating reservation: " . $e->getMessage();
    }
}

// Redirect back to the reservations page
header("Location: facilities_reservation.php");
exit;

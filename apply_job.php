<?php
include 'db.php';
startSession();
header('Content-Type: application/json');
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
 
$job_id = $_POST['job_id'] ?? 0;
$candidate_id = $_SESSION['user_id'];
 
if ($job_id) {
    // Check if already applied
    $check_sql = "SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('ii', $job_id, $candidate_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Already applied']);
    } else {
        $insert_sql = "INSERT INTO applications (job_id, candidate_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('ii', $job_id, $candidate_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
} else {
    echo json_encode(['success' => false]);
}
?>

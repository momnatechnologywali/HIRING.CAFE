<?php
include 'db.php';
startSession();
header('Content-Type: application/json');
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
 
$recruiter_id = $_SESSION['user_id'];
$candidate_id = $_POST['candidate_id'] ?? 0;
$date = $_POST['date'] ?? '';
$type = $_POST['type'] ?? 'video';
 
// Assume a job_id for simplicity, or fetch from context
$job_id = 1; // Placeholder; in real, pass job_id
 
if ($candidate_id && $date) {
    $insert_sql = "INSERT INTO interviews (job_id, candidate_id, recruiter_id, schedule_date, interview_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('iiss', $job_id, $candidate_id, $recruiter_id, $date, $type);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>

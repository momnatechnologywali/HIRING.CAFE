<?php
include 'db.php';
startSession();
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$to_id = $_GET['to'] ?? 0;
 
// Fetch messages if to_id set
$messages = [];
if ($to_id) {
    $sql = "SELECT * FROM messages WHERE (from_id = ? AND to_id = ?) OR (from_id = ? AND to_id = ?) ORDER BY sent_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $user_id, $to_id, $to_id, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
 
    // Mark as read
    $update_sql = "UPDATE messages SET read_status = TRUE WHERE to_id = ? AND from_id != ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $to_id) {
    $message = sanitize($_POST['message']);
    if (!empty($message)) {
        $insert_sql = "INSERT INTO messages (from_id, to_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('iis', $user_id, $to_id, $message);
        $stmt->execute();
        echo "<script>window.location.reload();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Hiring Cafe</title>
    <style>
        /* Internal CSS - Chat-like interface */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; display: flex; height: 100vh; }
        .chat-container { flex: 1; display: flex; flex-direction: column; max-width: 800px; margin: auto; background: #fff; }
        .messages { flex: 1; padding: 1rem; overflow-y: auto; border-bottom: 1px solid #ddd; }
        .message { margin-bottom: 1rem; padding: 0.5rem; border-radius: 5px; max-width: 70%; }
        .message.sent { background: #4a90e2; color: white; margin-left: auto; }
        .message.received { background: #e9ecef; color: #333; }
        .form-group { padding: 1rem; display: flex; gap: 0.5rem; }
        textarea { flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; resize: none; }
        .btn { padding: 0.5rem 1rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; }
        header { padding: 1rem; background: #f1f3f4; text-align: center; }
        @media (max-width: 768px) { body { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="chat-container">
        <header>
            <h2>Messages <?php if ($to_id): ?>(with Candidate/Recruiter)<?php endif; ?></h2>
            <?php if (!$to_id): ?><p>Select a user to message.</p><?php endif; ?>
        </header>
        <div class="messages">
            <?php if ($to_id): foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['from_id'] == $user_id ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($msg['message']); ?><br>
                    <small><?php echo date('M j, Y g:i a', strtotime($msg['sent_at'])); ?></small>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <?php if ($to_id): ?>
            <form method="POST">
                <div class="form-group">
                    <textarea name="message" placeholder="Type a message..." required rows="2"></textarea>
                    <button type="submit" class="btn">Send</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>

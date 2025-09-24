<?php
include 'db.php';
startSession();
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
 
// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
 
$profile = null;
if ($role == 'candidate') {
    $sql_profile = "SELECT * FROM candidates_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($sql_profile);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
}
 
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle updates (simplified: update bio, skills, etc.)
    $bio = sanitize($_POST['bio']);
    $update_sql = "UPDATE users SET bio = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $bio, $user_id);
    if ($stmt->execute()) {
        $success = 'Profile updated!';
        // Refresh user data
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error = 'Update failed.';
    }
 
    if ($role == 'candidate' && isset($_POST['skills'])) {
        $skills = json_encode(explode(',', sanitize($_POST['skills'])));
        $sql_skills = "UPDATE candidates_profiles SET skills = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql_skills);
        $stmt->bind_param('si', $skills, $user_id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Hiring Cafe</title>
    <style>
        /* Internal CSS - Profile editing style */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 1rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input, textarea { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        textarea { height: 100px; }
        .btn { padding: 0.8rem 1.5rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #357abd; }
        .error { color: #e74c3c; margin-bottom: 1rem; }
        .success { color: #27ae60; margin-bottom: 1rem; }
        .file-upload { margin: 1rem 0; }
        .preview { max-width: 200px; margin-top: 1rem; }
        @media (max-width: 768px) { .container { margin: 1rem; padding: 1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <?php if ($role == 'candidate'): ?>
                <div class="form-group">
                    <label for="skills">Skills (comma-separated)</label>
                    <input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars(implode(', ', json_decode($profile['skills'] ?? '[]', true))); ?>">
                </div>
                <div class="form-group file-upload">
                    <label for="resume">Upload Resume</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc">
                </div>
                <div class="form-group file-upload">
                    <label for="video">Upload Video Intro</label>
                    <input type="file" id="video" name="video" accept="video/*">
                </div>
            <?php endif; ?>
            <button type="submit" class="btn">Update Profile</button>
        </form>
        <a href="dashboard.php" class="btn" style="background: #95a5a6; margin-left: 1rem;">Back to Dashboard</a>
    </div>
 
    <script>
        // Handle file uploads (simulate, in real: PHP upload handling)
        document.querySelector('form').addEventListener('submit', function(e) {
            // Add upload logic if needed
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>

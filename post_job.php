<?php
include 'db.php';
startSession();
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);
    $salary = sanitize($_POST['salary']);
    $location = sanitize($_POST['location']);
    $job_type = sanitize($_POST['job_type']);
    $category = sanitize($_POST['category']);
 
    if (empty($title) || empty($description)) {
        $error = 'Title and description required.';
    } else {
        $sql = "INSERT INTO jobs (recruiter_id, title, description, requirements, salary, location, job_type, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssssss', $user_id, $title, $description, $requirements, $salary, $location, $job_type, $category);
        if ($stmt->execute()) {
            $success = 'Job posted successfully!';
        } else {
            $error = 'Error posting job.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - Hiring Cafe</title>
    <style>
        /* Internal CSS - Form style similar to signup */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 1rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input, textarea, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        textarea { height: 100px; }
        .btn { width: 100%; padding: 1rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #357abd; }
        .error { color: #e74c3c; margin-bottom: 1rem; }
        .success { color: #27ae60; margin-bottom: 1rem; }
        @media (max-width: 768px) { .container { padding: 1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Post a New Job</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="requirements">Requirements</label>
                <textarea id="requirements" name="requirements" required></textarea>
            </div>
            <div class="form-group">
                <label for="salary">Salary Range</label>
                <input type="text" id="salary" name="salary">
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location">
            </div>
            <div class="form-group">
                <label for="job_type">Job Type</label>
                <select id="job_type" name="job_type">
                    <option value="fulltime">Full Time</option>
                    <option value="parttime">Part Time</option>
                    <option value="contract">Contract</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category">
            </div>
            <button type="submit" class="btn">Post Job</button>
        </form>
        <a href="dashboard.php" class="btn" style="background: #95a5a6; margin-top: 1rem; display: block;">Back to Dashboard</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>

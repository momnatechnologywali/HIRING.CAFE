<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');
 
try {
    include 'db.php';
 
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
 
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }
 
    $user_id = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'];
    $error_message = '';
    $applied_jobs = null;
    $posted_jobs = null;
    $applications = null;
    $interviews = null;
 
    // Fetch user data
    $sql_user = "SELECT username, email, company_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    if (!$stmt) {
        error_log("User query prepare failed: " . $conn->error);
        $error_message = "Failed to load user data.";
    } else {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
 
    if ($role === 'candidate') {
        // Fetch applied jobs
        $sql_applied = "SELECT j.*, u.company_name FROM job_applications ja JOIN jobs j ON ja.job_id = j.id JOIN users u ON j.recruiter_id = u.id WHERE ja.candidate_id = ? ORDER BY ja.applied_at DESC";
        $stmt = $conn->prepare($sql_applied);
        if (!$stmt) {
            error_log("Applied jobs query prepare failed: " . $conn->error);
            $error_message .= " Failed to load applied jobs.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $applied_jobs = $stmt->get_result();
            $stmt->close();
        }
 
        // Fetch candidate profile
        $sql_profile = "SELECT skills, experience_level, resume, video_intro FROM candidates_profiles WHERE user_id = ?";
        $stmt = $conn->prepare($sql_profile);
        if (!$stmt) {
            error_log("Profile query prepare failed: " . $conn->error);
            $error_message .= " Failed to load profile.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
 
        // Fetch upcoming interviews
        $sql_interviews = "SELECT i.*, j.title, u.company_name FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN users u ON j.recruiter_id = u.id WHERE i.candidate_id = ? AND i.scheduled_at > NOW() ORDER BY i.scheduled_at";
        $stmt = $conn->prepare($sql_interviews);
        if (!$stmt) {
            error_log("Candidate interviews query prepare failed: " . $conn->error);
            $error_message .= " Failed to load interviews.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $interviews = $stmt->get_result();
            $stmt->close();
        }
    } elseif ($role === 'recruiter') {
        // Fetch posted jobs
        $sql_posted = "SELECT * FROM jobs WHERE recruiter_id = ? ORDER BY posted_at DESC";
        $stmt = $conn->prepare($sql_posted);
        if (!$stmt) {
            error_log("Posted jobs query prepare failed: " . $conn->error);
            $error_message .= " Failed to load posted jobs.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $posted_jobs = $stmt->get_result();
            $stmt->close();
        }
 
        // Fetch applications for posted jobs
        $sql_applications = "SELECT ja.*, j.title, u.username FROM job_applications ja JOIN jobs j ON ja.job_id = j.id JOIN users u ON ja.candidate_id = u.id WHERE j.recruiter_id = ? ORDER BY ja.applied_at DESC";
        $stmt = $conn->prepare($sql_applications);
        if (!$stmt) {
            error_log("Applications query prepare failed: " . $conn->error);
            $error_message .= " Failed to load applications.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $applications = $stmt->get_result();
            $stmt->close();
        }
 
        // Fetch scheduled interviews
        $sql_interviews = "SELECT i.*, j.title, u.username FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN users u ON i.candidate_id = u.id WHERE j.recruiter_id = ? AND i.scheduled_at > NOW() ORDER BY i.scheduled_at";
        $stmt = $conn->prepare($sql_interviews);
        if (!$stmt) {
            error_log("Recruiter interviews query prepare failed: " . $conn->error);
            $error_message .= " Failed to load interviews.";
        } else {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $interviews = $stmt->get_result();
            $stmt->close();
        }
    }
} catch (Exception $e) {
    error_log("Dashboard.php fatal error: " . $e->getMessage());
    $error_message = "An unexpected error occurred. Please contact support.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hiring Cafe</title>
    <style>
        /* Internal CSS - Consistent with index.php, professional and responsive */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        header { background: #fff; padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-size: 1.8rem; font-weight: bold; color: #4a90e2; }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; transition: color 0.3s; }
        .nav-links a:hover { color: #4a90e2; }
        .auth-btns { display: flex; gap: 1rem; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: #4a90e2; color: white; }
        .btn-primary:hover { background: #357abd; }
        .btn-secondary { background: #fff; color: #4a90e2; border: 1px solid #4a90e2; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .section h2 { font-size: 2.5rem; margin-bottom: 2rem; color: #2c3e50; text-align: center; }
        .profile-header { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .profile-header h3 { color: #2c3e50; margin-bottom: 0.5rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .card h3 { color: #4a90e2; margin-bottom: 0.5rem; }
        .card p { color: #7f8c8d; margin-bottom: 1rem; }
        .card-tags { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
        .tag { background: #e3f2fd; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem; color: #1976d2; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 1rem; font-size: 1.1rem; background: #ffe6e6; padding: 1rem; border-radius: 5px; }
        footer { background: #2c3e50; color: white; text-align: center; padding: 2rem; margin-top: 4rem; }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .section h2 { font-size: 2rem; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Hiring Cafe</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#" onclick="redirect('search_jobs.php')">Jobs</a></li>
                <li><a href="#" onclick="redirect('showcase.php')">Candidates</a></li>
            </ul>
            <div class="auth-btns">
                <a href="#" onclick="redirect('dashboard.php')" class="btn btn-primary">Dashboard</a>
                <a href="#" onclick="logout()" class="btn btn-secondary">Logout</a>
            </div>
        </nav>
    </header>
 
    <section class="section">
        <div class="profile-header">
            <h2>Welcome, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
            <?php if ($role === 'recruiter'): ?>
                <p>Company: <?php echo htmlspecialchars($user['company_name'] ?? 'N/A'); ?></p>
            <?php endif; ?>
            <?php if ($role === 'candidate'): ?>
                <p><a href="#" onclick="redirect('profile.php')" class="btn btn-primary">Edit Profile</a></p>
            <?php endif; ?>
        </div>
 
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
 
        <?php if ($role === 'candidate'): ?>
            <!-- Candidate Dashboard -->
            <h2>Your Applied Jobs</h2>
            <?php if (isset($applied_jobs) && $applied_jobs && $applied_jobs->num_rows > 0): ?>
                <div class="grid">
                    <?php while ($job = $applied_jobs->fetch_assoc()): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($job['title'] ?? 'Untitled Job'); ?></h3>
                            <p><strong><?php echo htmlspecialchars($job['company_name'] ?? 'Unknown Company'); ?></strong> - <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></p>
                            <p>Applied on: <?php echo htmlspecialchars(date('Y-m-d', strtotime($job['applied_at'] ?? 'now'))); ?></p>
                            <div class="card-tags">
                                <span class="tag"><?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?></span>
                                <span class="tag"><?php echo htmlspecialchars($job['category'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="error">You haven't applied to any jobs yet. <a href="search_jobs.php">Find jobs now!</a></p>
            <?php endif; ?>
 
            <h2>Your Profile</h2>
            <?php if (isset($profile)): ?>
                <div class="card">
                    <p>Experience: <?php echo htmlspecialchars($profile['experience_level'] ?? 'Not set'); ?></p>
                    <?php 
                    $skills = json_decode($profile['skills'] ?? '[]', true);
                    if (is_array($skills) && !empty($skills)): ?>
                        <div class="card-tags">
                            <?php foreach ($skills as $skill): ?>
                                <span class="tag"><?php echo htmlspecialchars($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No skills listed. Update your profile!</p>
                    <?php endif; ?>
                    <p>Resume: <?php echo $profile['resume'] ? 'Uploaded' : 'Not uploaded'; ?></p>
                    <p>Video Intro: <?php echo $profile['video_intro'] ? 'Uploaded' : 'Not uploaded'; ?></p>
                    <p><a href="#" onclick="redirect('profile.php')" class="btn btn-primary">Update Profile</a></p>
                </div>
            <?php else: ?>
                <p class="error">Profile not found. <a href="profile.php">Create one now!</a></p>
            <?php endif; ?>
 
            <h2>Upcoming Interviews</h2>
            <?php if (isset($interviews) && $interviews && $interviews->num_rows > 0): ?>
                <div class="grid">
                    <?php while ($interview = $interviews->fetch_assoc()): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($interview['title'] ?? 'Untitled Job'); ?></h3>
                            <p><strong><?php echo htmlspecialchars($interview['company_name'] ?? 'Unknown Company'); ?></strong></p>
                            <p>Scheduled: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($interview['scheduled_at'] ?? 'now'))); ?></p>
                            <p>Type: <?php echo htmlspecialchars($interview['type'] ?? 'N/A'); ?></p>
                            <button class="btn btn-danger" onclick="cancelInterview(<?php echo (int)($interview['id'] ?? 0); ?>)">Cancel</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="error">No upcoming interviews. Apply to jobs to get started

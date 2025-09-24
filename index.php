<?php
include 'db.php';
 
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Initialize variables
$result_jobs = null;
$result_candidates = null;
 
// Fetch top 5 active jobs with error handling
$sql_jobs = "SELECT j.*, u.company_name FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE j.is_active = TRUE ORDER BY j.posted_at DESC LIMIT 5";
$result_jobs = $conn->query($sql_jobs);
if (!$result_jobs) {
    error_log("Job query failed: " . $conn->error);
    $error_message = "Failed to load jobs. Please try again later.";
}
 
// Fetch top 5 candidates with error handling
$sql_candidates = "SELECT u.*, cp.skills, cp.experience_level FROM users u LEFT JOIN candidates_profiles cp ON u.id = cp.user_id WHERE u.role = 'candidate' AND u.is_active = TRUE ORDER BY u.created_at DESC LIMIT 5";
$result_candidates = $conn->query($sql_candidates);
if (!$result_candidates) {
    error_log("Candidate query failed: " . $conn->error);
    $error_message = isset($error_message) ? $error_message . " Failed to load candidates." : "Failed to load candidates. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiring Cafe - Fast Hiring Platform</title>
    <style>
        /* Internal CSS - Professional, modern, responsive design */
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
        .hero { text-align: center; padding: 4rem 2rem; background: #fff; margin: 2rem 0; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; color: #2c3e50; }
        .hero p { font-size: 1.2rem; margin-bottom: 2rem; color: #7f8c8d; }
        .search-bar { max-width: 600px; margin: 0 auto; display: flex; gap: 0.5rem; }
        .search-bar input { flex: 1; padding: 1rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        .search-bar .btn { padding: 1rem 2rem; }
        .section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .section h2 { text-align: center; font-size: 2.5rem; margin-bottom: 2rem; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .card h3 { color: #4a90e2; margin-bottom: 0.5rem; }
        .card p { color: #7f8c8d; margin-bottom: 1rem; }
        .card-tags { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
        .tag { background: #e3f2fd; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem; color: #1976d2; }
        footer { background: #2c3e50; color: white; text-align: center; padding: 2rem; margin-top: 4rem; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 1rem; font-size: 1.1rem; }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h1 { font-size: 2rem; }
            .grid { grid-template-columns: 1fr; }
            .search-bar { flex-direction: column; }
            .search-bar input, .search-bar .btn { width: 100%; }
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
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role'])): ?>
                    <a href="#" onclick="redirect('dashboard.php')" class="btn btn-secondary">Dashboard</a>
                    <a href="#" onclick="logout()" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="#" onclick="redirect('login.php')" class="btn btn-secondary">Login</a>
                    <a href="#" onclick="redirect('signup.php')" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
 
    <section class="hero">
        <h1>Find Your Dream Job or Top Talent Fast</h1>
        <p>Connect recruiters and candidates in minutes with video intros and instant interviews.</p>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search jobs by title, skill, or location..." aria-label="Search jobs">
            <button class="btn btn-primary" onclick="searchJobs()">Search</button>
        </div>
    </section>
 
    <section class="section">
        <h2>Trending Jobs</h2>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($result_jobs && $result_jobs->num_rows > 0): ?>
            <div class="grid">
                <?php while ($job = $result_jobs->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($job['title'] ?? 'Untitled Job'); ?></h3>
                        <p><strong><?php echo htmlspecialchars($job['company_name'] ?? 'Unknown Company'); ?></strong> - <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></p>
                        <p><?php echo substr(htmlspecialchars($job['description'] ?? 'No description available'), 0, 100); ?>...</p>
                        <div class="card-tags">
                            <span class="tag"><?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?></span>
                            <span class="tag"><?php echo htmlspecialchars($job['category'] ?? 'N/A'); ?></span>
                            <span class="tag"><?php echo htmlspecialchars($job['salary'] ?? 'N/A'); ?></span>
                        </div>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'candidate'): ?>
                            <button class="btn btn-primary" onclick="applyJob(<?php echo (int)$job['id']; ?>)">Apply Now</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="error">No jobs available at the moment.</p>
        <?php endif; ?>
    </section>
 
    <section class="section">
        <h2>Top Candidates</h2>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($result_candidates && $result_candidates->num_rows > 0): ?>
            <div class="grid">
                <?php while ($candidate = $result_candidates->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($candidate['username'] ?? 'Anonymous'); ?></h3>
                        <p>Experience: <?php echo htmlspecialchars($candidate['experience_level'] ?? 'N/A'); ?></p>
                        <?php 
                        $skills = json_decode($candidate['skills'] ?? '[]', true);
                        if (is_array($skills) && !empty($skills)): ?>
                            <div class="card-tags">
                                <?php foreach (array_slice($skills, 0, 3) as $skill): ?>
                                    <span class="tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No skills listed.</p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'recruiter'): ?>
                            <button class="btn btn-primary" onclick="scheduleInterview(<?php echo (int)$candidate['id']; ?>)">Schedule Interview</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="error">No candidates available at the moment.</p>
        <?php endif; ?>
    </section>
 
    <footer>
        <p>&copy; 2025 Hiring Cafe. All rights reserved.</p>
    </footer>
 
    <script>
        // JavaScript for redirections and interactions
        function redirect(page) {
            try {
                window.location.href = page;
            } catch (e) {
                console.error('Redirect failed:', e);
                alert('Error navigating to the page.');
            }
        }
 
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                redirect('logout.php');
            }
        }
 
        function searchJobs() {
            const query = document.getElementById('searchInput').value.trim();
            if (!query) {
                alert('Please enter a search query.');
                return;
            }
            redirect(`search_jobs.php?q=${encodeURIComponent(query)}`);
        }
 
        function applyJob(jobId) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Please login to apply for jobs.');
                redirect('login.php');
                return;
            }
            if (!Number.isInteger(jobId)) {
                alert('Invalid job ID.');
                return;
            }
            fetch('apply_job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `job_id=${jobId}`
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                alert(data.success ? 'Applied successfully!' : `Error: ${data.message || 'Failed to apply.'}`);
            })
            .catch(error => {
                console.error('Apply job error:', error);
                alert('An error occurred while applying. Please try again.');
            });
        }
 
        function scheduleInterview(candidateId) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Please login to schedule interviews.');
                redirect('login.php');
                return;
            }
            if (!Number.isInteger(candidateId)) {
                alert('Invalid candidate ID.');
                return;
            }
            const date = prompt('Enter schedule date (YYYY-MM-DD HH:MM):');
            if (!date) {
                alert('Please provide a valid date.');
                return;
            }
            fetch('schedule_interview.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `candidate_id=${candidateId}&date=${encodeURIComponent(date)}&type=video`
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                alert(data.success ? 'Interview scheduled successfully!' : `Error: ${data.message || 'Failed to schedule.'}`);
            })
            .catch(error => {
                console.error('Schedule interview error:', error);
                alert('An error occurred while scheduling. Please try again.');
            });
        }
 
        // Enter key for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchJobs();
            }
        });
    </script>
</body>
</html>
<?php
// Free result sets and close connection
if ($result_jobs) {
    $result_jobs->free();
}
if ($result_candidates) {
    $result_candidates->free();
}
$conn->close();
?>

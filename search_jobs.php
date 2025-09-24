<?php
include 'db.php';
startSession();
 
$query = $_GET['q'] ?? '';
$filters = []; // Can extend for filters
 
$sql = "SELECT j.*, u.company_name FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE j.is_active = TRUE";
$params = [];
$types = '';
 
if ($query) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.category LIKE ?)";
    $search = "%$query%";
    $params = [$search, $search, $search];
    $types = 'sss';
}
 
$sql .= " ORDER BY j.posted_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Jobs - Hiring Cafe</title>
    <style>
        /* Internal CSS - Search results grid */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        header { background: #fff; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-bar { max-width: 600px; margin: 1rem auto; display: flex; gap: 0.5rem; }
        .search-bar input { flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 0.8rem 1.5rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .section { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .card h3 { color: #4a90e2; margin-bottom: 0.5rem; }
        .tag { background: #e3f2fd; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem; color: #1976d2; margin-right: 0.5rem; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search jobs..." value="<?php echo htmlspecialchars($query); ?>">
            <button class="btn" onclick="searchJobs()">Search</button>
            <a href="index.php" class="btn" style="background: #95a5a6;">Home</a>
        </div>
    </header>
 
    <section class="section">
        <h2>Job Results<?php if ($query): ?> for "<?php echo htmlspecialchars($query); ?>"<?php endif; ?></h2>
        <div class="grid">
            <?php while ($job = $jobs->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><strong><?php echo htmlspecialchars($job['company_name']); ?></strong></p>
                    <p><?php echo substr(htmlspecialchars($job['description']), 0, 150); ?>...</p>
                    <div>
                        <span class="tag"><?php echo htmlspecialchars($job['job_type']); ?></span>
                        <span class="tag"><?php echo htmlspecialchars($job['location']); ?></span>
                        <span class="tag"><?php echo htmlspecialchars($job['salary']); ?></span>
                    </div>
                    <button class="btn" onclick="applyJob(<?php echo $job['id']; ?>)" style="margin-top: 1rem; width: 100%;">Apply</button>
                </div>
            <?php endwhile; ?>
        </div>
        <?php if ($jobs->num_rows == 0): ?>
            <p style="text-align: center; color: #7f8c8d;">No jobs found. Try a different search.</p>
        <?php endif; ?>
    </section>
 
    <script>
        function searchJobs() {
            const q = document.getElementById('searchInput').value;
            window.location.href = `search_jobs.php?q=${encodeURIComponent(q)}`;
        }
 
        function applyJob(jobId) {
            // Similar to index.php
            fetch('apply_job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `job_id=${jobId}`
            }).then(res => res.json()).then(data => {
                alert(data.success ? 'Applied!' : 'Error');
            });
        }
 
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchJobs();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>

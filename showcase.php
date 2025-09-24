<?php
include 'db.php';
startSession();
 
// Fetch all candidates
$sql = "SELECT u.*, cp.* FROM users u LEFT JOIN candidates_profiles cp ON u.id = cp.user_id WHERE u.role = 'candidate' AND u.is_active = TRUE ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates Showcase - Hiring Cafe</title>
    <style>
        /* Internal CSS - Similar to index grid */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        header { background: #fff; padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        nav { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; }
        .logo { font-size: 1.5rem; color: #4a90e2; }
        .btn { padding: 0.5rem 1rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
        .section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        h2 { text-align: center; margin-bottom: 2rem; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card h3 { color: #4a90e2; margin-bottom: 0.5rem; }
        .skills { display: flex; gap: 0.5rem; margin: 1rem 0; flex-wrap: wrap; }
        .tag { background: #e8f5e8; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem; color: #27ae60; }
        .video-preview { width: 100%; max-width: 300px; margin: 1rem 0; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Candidates Showcase</div>
            <a href="index.php" class="btn">Home</a>
        </nav>
    </header>
 
    <section class="section">
        <h2>Top Candidates Ready to Hire</h2>
        <div class="grid">
            <?php while ($candidate = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($candidate['username']); ?></h3>
                    <p><?php echo htmlspecialchars($candidate['bio'] ?? 'No bio available.'); ?></p>
                    <?php if ($candidate['skills']): 
                        $skills = json_decode($candidate['skills'], true);
                        echo '<div class="skills">';
                        foreach ($skills as $skill): ?>
                            <span class="tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; 
                        echo '</div>'; 
                    endif; ?>
                    <?php if ($candidate['video_intro']): ?>
                        <video class="video-preview" controls>
                            <source src="<?php echo htmlspecialchars($candidate['video_intro']); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'recruiter'): ?>
                        <button class="btn" onclick="messageCandidate(<?php echo $candidate['id']; ?>)" style="width: 100%; margin-top: 1rem;">Message</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
 
    <script>
        function messageCandidate(id) {
            window.location.href = `messages.php?to=${id}`;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>

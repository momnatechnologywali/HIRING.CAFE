<?php
include 'db.php';
startSession();
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
 
    if (empty($email) || empty($password)) {
        $error = 'Email and password required.';
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE email = ? AND is_active = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                echo "<script>window.location.href = 'dashboard.php';</script>";
                exit;
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hiring Cafe</title>
    <style>
        /* Internal CSS - Matching signup style */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        input:focus { outline: none; border-color: #4a90e2; }
        .btn { width: 100%; padding: 1rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background: #357abd; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 1rem; }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #4a90e2; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="link">
            <a href="#" onclick="redirect('signup.php')">No account? Sign Up</a>
        </div>
    </div>
 
    <script>
        function redirect(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>

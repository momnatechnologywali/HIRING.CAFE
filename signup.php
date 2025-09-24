<?php
include 'db.php';
startSession();
 
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    $company_name = ($_POST['role'] == 'recruiter') ? sanitize($_POST['company_name']) : '';
 
    // Validate
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if user exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Insert user
            $hashed_pass = hashPassword($password);
            $insert_sql = "INSERT INTO users (username, email, password, role, company_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param('sssss', $username, $email, $hashed_pass, $role, $company_name);
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                if ($role == 'candidate') {
                    // Insert empty profile
                    $insert_profile = "INSERT INTO candidates_profiles (user_id) VALUES (?)";
                    $stmt = $conn->prepare($insert_profile);
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                }
                $success = 'Account created successfully!';
                // JS redirect after
                echo "<script>alert('Welcome!'); window.location.href = 'dashboard.php';</script>";
                exit;
            } else {
                $error = 'Error creating account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hiring Cafe</title>
    <style>
        /* Internal CSS - Clean, professional form design */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: #4a90e2; }
        .btn { width: 100%; padding: 1rem; background: #4a90e2; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background: #357abd; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 1rem; }
        .success { color: #27ae60; text-align: center; margin-bottom: 1rem; }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #4a90e2; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Account</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required onchange="toggleCompany()">
                    <option value="">Select Role</option>
                    <option value="candidate">Candidate</option>
                    <option value="recruiter">Recruiter</option>
                </select>
            </div>
            <div class="form-group" id="company-group" style="display:none;">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name">
            </div>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <div class="link">
            <a href="#" onclick="redirect('login.php')">Already have an account? Login</a>
        </div>
    </div>
 
    <script>
        function redirect(page) {
            window.location.href = page;
        }
 
        function toggleCompany() {
            const role = document.getElementById('role').value;
            document.getElementById('company-group').style.display = role === 'recruiter' ? 'block' : 'none';
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>

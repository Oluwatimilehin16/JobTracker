<?php
session_start();
include 'config.php';

$message = '';
$messageType = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    // If no errors, proceed with authentication
    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                // Show success message before redirect
                $message = 'Login successful! Redirecting to dashboard...';
                $messageType = 'success';
                // Clear password field for security
                $password = '';
                
                // Add JavaScript redirect after showing success message
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                </script>";
            } else {
                // Check specifically what went wrong
                if (!$user) {
                    $errors['email'] = 'No account found with this email address';
                } else {
                    $errors['password'] = 'Incorrect password';
                }
                $message = 'Login failed. Please check your credentials.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database connection error. Please try again later.';
            $messageType = 'error';
            // Log the actual error for debugging (don't show to user)
            error_log('Login DB Error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tracker - Login</title>
    <link rel="stylesheet" href="./assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h3>Welcome Back</h3>
            <p class="subtitle">Sign in to your Job Tracker account</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php if ($messageType === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Enter your email" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="forgot-password">
            <a href="forgot-password.php">Forgot your password?</a>
        </div>

        <div class="register-link">
            Don't have an account? <a href="register.php">Create one here</a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus on email field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>
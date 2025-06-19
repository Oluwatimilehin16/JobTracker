<?php
session_start();
include 'config.php';

$message = '';
$messageType = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $message = 'Email already exists. Please use a different email.';
            $messageType = 'error';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
            $result = $stmt->execute();

            if ($result) {
                $message = 'Registration successful! Welcome to Job Tracker.';
                $messageType = 'success';
                // Clear form data on success
                $firstName = $lastName = $email = '';
            } else {
                $message = 'Registration failed. Please try again.';
                $messageType = 'error';
            }

            $stmt->close();
        }

        $checkEmail->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tracker - Register</title>
    <link rel="stylesheet" href="./assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="logo-section">
            <h3>Create Account</h3>
            <p class="subtitle">Join Job Tracker and organize your job search</p>
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
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           placeholder="Sara" 
                           value="<?php echo htmlspecialchars($firstName ?? ''); ?>"
                           class="<?php echo isset($errors['first_name']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="error-message"><?php echo $errors['first_name']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           placeholder="Williams" 
                           value="<?php echo htmlspecialchars($lastName ?? ''); ?>"
                           class="<?php echo isset($errors['last_name']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="error-message"><?php echo $errors['last_name']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="sara@example.com" 
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
                           placeholder="Create a strong password"
                           class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign in here</a>
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
    </script>
</body>
</html>
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

$message = '';
$messageType = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $jobTitle = trim($_POST['job_title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $applicationLink = trim($_POST['application_link'] ?? '');
    $status = $_POST['status'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $salaryRange = trim($_POST['salary_range'] ?? '');
    $applicationDate = $_POST['application_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (empty($jobTitle)) {
        $errors['job_title'] = 'Job title is required';
    }

    if (empty($company)) {
        $errors['company'] = 'Company name is required';
    }

    if (empty($status)) {
        $errors['status'] = 'Status is required';
    }

    if (!empty($applicationLink) && !filter_var($applicationLink, FILTER_VALIDATE_URL)) {
        $errors['application_link'] = 'Please enter a valid URL';
    }

    if (empty($applicationDate)) {
        $errors['application_date'] = 'Application date is required';
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO job_applications (user_id, job_title, company, application_link, status, location, salary_range, application_date, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $jobTitle,
                $company,
                $applicationLink,
                $status,
                $location,
                $salaryRange,
                $applicationDate,
                $notes
            ]);

            if ($result) {
                $message = 'Job application added successfully!';
                $messageType = 'success';
                // Clear form data on success
                $jobTitle = $company = $applicationLink = $location = $salaryRange = $applicationDate = $notes = '';
                $status = '';
            } else {
                $message = 'Failed to add job application. Please try again.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tracker - Add Job Application</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/add_job.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-briefcase"></i>
                <span>JobTracker</span>
            </a>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_job.php" class="nav-link active">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Job</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="ai_assistant.php" class="nav-link">
                        <i class="fas fa-robot"></i>
                        <span>AI Assistant</span>
                    </a>
                </li>
            </ul>

            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Add New Job Application</h1>
            <p class="page-subtitle">Track your job application journey from start to finish</p>
        </div>

        <div class="form-container">
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
                        <label for="job_title">Job Title *</label>
                        <input type="text" 
                               id="job_title" 
                               name="job_title" 
                               placeholder="e.g., Frontend Developer"
                               value="<?php echo htmlspecialchars($jobTitle ?? ''); ?>"
                               class="<?php echo isset($errors['job_title']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['job_title'])): ?>
                            <div class="error-message"><?php echo $errors['job_title']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="company">Company *</label>
                        <input type="text" 
                               id="company" 
                               name="company" 
                               placeholder="e.g., Google"
                               value="<?php echo htmlspecialchars($company ?? ''); ?>"
                               class="<?php echo isset($errors['company']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['company'])): ?>
                            <div class="error-message"><?php echo $errors['company']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" 
                               id="location" 
                               name="location" 
                               placeholder="e.g., San Francisco, CA"
                               value="<?php echo htmlspecialchars($location ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="salary_range">Salary Range</label>
                        <input type="text" 
                               id="salary_range" 
                               name="salary_range" 
                               placeholder="e.g., $80,000 - $120,000"
                               value="<?php echo htmlspecialchars($salaryRange ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="application_date">Application Date *</label>
                        <input type="date" 
                               id="application_date" 
                               name="application_date" 
                               value="<?php echo htmlspecialchars($applicationDate ?? date('Y-m-d')); ?>"
                               class="<?php echo isset($errors['application_date']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['application_date'])): ?>
                            <div class="error-message"><?php echo $errors['application_date']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="status">Application Status *</label>
                        <select id="status" 
                                name="status"
                                class="<?php echo isset($errors['status']) ? 'input-error' : ''; ?>">
                            <option value="">Select Status</option>
                            <option value="Applied" <?php echo ($status ?? '') === 'Applied' ? 'selected' : ''; ?>>Applied</option>
                            <option value="Interviewing" <?php echo ($status ?? '') === 'Interviewing' ? 'selected' : ''; ?>>Interviewing</option>
                            <option value="Offer Received" <?php echo ($status ?? '') === 'Offer Received' ? 'selected' : ''; ?>>Offer Received</option>
                            <option value="Rejected" <?php echo ($status ?? '') === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="Withdrawn" <?php echo ($status ?? '') === 'Withdrawn' ? 'selected' : ''; ?>>Withdrawn</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <div class="error-message"><?php echo $errors['status']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="application_link">Application Link</label>
                    <input type="url" 
                           id="application_link" 
                           name="application_link" 
                           placeholder="https://careers.google.com/jobs/results/12345"
                           value="<?php echo htmlspecialchars($applicationLink ?? ''); ?>"
                           class="<?php echo isset($errors['application_link']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['application_link'])): ?>
                        <div class="error-message"><?php echo $errors['application_link']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" 
                              name="notes" 
                              placeholder="Add any additional notes about this application (e.g., referral contact, interview tips, company culture notes)"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus-circle"></i> Add Job Application
                </button>
            </form>
        </div>
    </div>

    <script>
        // Set today's date as default if no date is selected
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('application_date');
            if (!dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
        });

        // Auto-focus on job title field
        document.getElementById('job_title').focus();
    </script>
</body>
</html>
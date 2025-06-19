<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$jobId = intval($_GET['id'] ?? 0);
$message = '';
$messageType = '';

if ($jobId <= 0) {
    die('Invalid job ID.');
}

$stmt = $pdo->prepare("SELECT * FROM job_applications WHERE id = ? AND user_id = ?");
$stmt->execute([$jobId, $_SESSION['user_id']]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die('Job not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['job_title']);
    $company = trim($_POST['company']);
    $status = $_POST['status'];
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary_range']);
    $link = trim($_POST['application_link']);
    $notes = trim($_POST['notes']);
    $date = $_POST['application_date'];

    try {
        $update = $pdo->prepare("UPDATE job_applications SET job_title=?, company=?, status=?, location=?, salary_range=?, application_link=?, notes=?, application_date=? WHERE id=? AND user_id=?");
        $updated = $update->execute([$title, $company, $status, $location, $salary, $link, $notes, $date, $jobId, $_SESSION['user_id']]);

        if ($updated) {
            $message = "Application updated successfully!";
            $messageType = "success";
            // Refresh data
            $stmt->execute([$jobId, $_SESSION['user_id']]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "Update failed. Try again.";
            $messageType = "error";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Application - TrackWise</title>
    <link rel="stylesheet" href="./assets/css/edit_job.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Edit Application</h2>
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label>Job Title</label>
            <input type="text" name="job_title" value="<?= htmlspecialchars($job['job_title']) ?>" required>
        </div>

        <div>
            <label>Company</label>
            <input type="text" name="company" value="<?= htmlspecialchars($job['company']) ?>" required>
        </div>

        <div>
            <label>Status</label>
            <select name="status" required>
                <?php
                $statuses = ['Applied', 'Interviewing', 'Offer Received', 'Rejected', 'Withdrawn'];
                foreach ($statuses as $status) {
                    $selected = $job['status'] === $status ? 'selected' : '';
                    echo "<option value=\"$status\" $selected>$status</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>">
        </div>

        <div>
            <label>Salary Range</label>
            <input type="text" name="salary_range" value="<?= htmlspecialchars($job['salary_range']) ?>">
        </div>

        <div>
            <label>Application Link</label>
            <input type="url" name="application_link" value="<?= htmlspecialchars($job['application_link']) ?>">
        </div>

        <div class="full-width">
            <label>Notes</label>
            <textarea name="notes" rows="3"><?= htmlspecialchars($job['notes']) ?></textarea>
        </div>

        <div class="full-width">
            <label>Application Date</label>
            <input type="date" name="application_date" value="<?= htmlspecialchars($job['application_date']) ?>" required>
        </div>

        <button type="submit">Update Application</button>
    </form>
</div>
</body>
</html>

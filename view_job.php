<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die('Invalid access.');
}

$jobId = intval($_GET['id']);

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM job_applications WHERE id = ? AND user_id = ?");
$stmt->execute([$jobId, $_SESSION['user_id']]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die('Job not found or access denied.');
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Job - TrackWise</title>
    <link rel="stylesheet" href="./assets/css/view_job.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <h2><i class="fas fa-briefcase"></i> Application Details</h2>

    <div class="info"><div class="label">Job Title:</div><div class="value"><?= htmlspecialchars($job['job_title']) ?></div></div>
    <div class="info"><div class="label">Company:</div><div class="value"><?= htmlspecialchars($job['company']) ?></div></div>
    <div class="info"><div class="label">Location:</div><div class="value"><?= htmlspecialchars($job['location']) ?></div></div>
    <div class="info"><div class="label">Salary Range:</div><div class="value"><?= htmlspecialchars($job['salary_range']) ?></div></div>
    <div class="info"><div class="label">Status:</div><div class="value"><?= htmlspecialchars($job['status']) ?></div></div>
    <div class="info"><div class="label">Application Date:</div><div class="value"><?= formatDate($job['application_date']) ?></div></div>
    <div class="info"><div class="label">Application Link:</div><div class="value"><a class="value" href="<?= htmlspecialchars($job['application_link']) ?>" target="_blank"><?= htmlspecialchars($job['application_link']) ?></a></div></div>
    <div class="info"><div class="label">Notes:</div><div class="value"><?= nl2br(htmlspecialchars($job['notes'])) ?></div></div>

    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>
</body>
</html>

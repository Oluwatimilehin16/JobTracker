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

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $jobId = intval($_GET['id']);

    // Verify the job belongs to the current user
    $checkSql = "SELECT id FROM job_applications WHERE id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $jobId, $_SESSION['user_id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $deleteSql = "DELETE FROM job_applications WHERE id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $jobId, $_SESSION['user_id']);
        if ($deleteStmt->execute()) {
            $message = 'Job application deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete job application.';
            $messageType = 'error';
        }
    } else {
        $message = 'Job application not found or access denied.';
        $messageType = 'error';
    }
}

// Fetch job applications
$jobApplications = [];
$stats = ['total_applications' => 0, 'applied_count' => 0, 'interviewing_count' => 0, 'offer_count' => 0, 'rejected_count' => 0, 'withdrawn_count' => 0];

$sql = "
    SELECT id, job_title, company, location, salary_range, status, application_date, 
           application_link, notes, created_at 
    FROM job_applications 
    WHERE user_id = ? 
    ORDER BY application_date DESC, created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $jobApplications[] = $row;
}

// Get statistics
$statsSql = "
    SELECT 
        COUNT(*) as total_applications,
        SUM(CASE WHEN status = 'Applied' THEN 1 ELSE 0 END) as applied_count,
        SUM(CASE WHEN status = 'Interviewing' THEN 1 ELSE 0 END) as interviewing_count,
        SUM(CASE WHEN status = 'Offer Received' THEN 1 ELSE 0 END) as offer_count,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN status = 'Withdrawn' THEN 1 ELSE 0 END) as withdrawn_count
    FROM job_applications 
    WHERE user_id = ?
";
$statsStmt = $conn->prepare($statsSql);
$statsStmt->bind_param("i", $_SESSION['user_id']);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Applied':
            return 'status-applied';
        case 'Interviewing':
            return 'status-interviewing';
        case 'Offer Received':
            return 'status-offer';
        case 'Rejected':
            return 'status-rejected';
        case 'Withdrawn':
            return 'status-withdrawn';
        default:
            return 'status-default';
    }
}

// Helper function to format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tracker - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/dashboard.css">
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_job.php" class="nav-link">
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
        <!-- Page Header -->
        <div class="page-header">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
                    <p>Here are your current job applications and their progress</p>
                </div>
                <a href="add_job.php" class="add-job-btn">
                    <i class="fas fa-plus-circle"></i>
                    Add New Application
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number stat-total"><?php echo $stats['total_applications']; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-applied"><?php echo $stats['applied_count']; ?></div>
                <div class="stat-label">Applied</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-interviewing"><?php echo $stats['interviewing_count']; ?></div>
                <div class="stat-label">Interviewing</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-offer"><?php echo $stats['offer_count']; ?></div>
                <div class="stat-label">Offers Received</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?php echo $stats['rejected_count']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-withdrawn"><?php echo $stats['withdrawn_count']; ?></div>
                <div class="stat-label">Withdrawn</div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        <!-- Applications Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list-alt"></i>
                    Your Job Applications
                </div>
            </div>
            
            <?php if (empty($jobApplications)): ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <h3>No Applications Yet</h3>
                    <p>Start tracking your job search journey by adding your first application!</p>
                    <a href="add_job.php" class="add-job-btn">
                        <i class="fas fa-plus-circle"></i>
                        Add Your First Application
                    </a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Salary Range</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobApplications as $job): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['company']); ?></td>
                                    <td><?php echo htmlspecialchars($job['location'] ?: 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($job['salary_range'] ?: 'Not specified'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo getStatusBadgeClass($job['status']); ?>">
                                            <?php echo htmlspecialchars($job['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($job['application_date']); ?></td>
                                    <td class="notes-cell" title="<?php echo htmlspecialchars($job['notes']); ?>">
                                        <?php echo htmlspecialchars($job['notes'] ? (strlen($job['notes']) > 30 ? substr($job['notes'], 0, 30) . '...' : $job['notes']) : 'No notes'); ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_job.php?id=<?php echo $job['id']; ?>" class="btn-action btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn-action btn-edit" title="Edit Application">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $job['id']; ?>, '<?php echo htmlspecialchars($job['job_title']); ?>', '<?php echo htmlspecialchars($job['company']); ?>')" class="btn-action btn-delete" title="Delete Application">
                                                                                               <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p id="deleteMessage">Are you sure you want to delete this job application?</p>
            <div class="modal-buttons">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <a id="confirmDeleteBtn" href="#" class="modal-btn btn-confirm">Delete</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, jobTitle, company) {
            const modal = document.getElementById('deleteModal');
            const message = document.getElementById('deleteMessage');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            message.textContent = `Are you sure you want to delete "${jobTitle}" at "${company}"?`;
            confirmBtn.href = `?action=delete&id=${id}`;
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal if clicked outside content
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

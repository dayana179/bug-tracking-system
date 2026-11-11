
<?php
require_once __DIR__ . '/includes/functions.php';

// Default verification values.
$connectionStatus = false;
$databaseName = 'Not connected';
$totalBugs = 0;
$totalHistory = 0;
$latestBug = null;
$latestHistory = null;
$errorMessage = 'Database connection is unavailable.';

// Local XAMPP database settings.
// These are currently the same settings as config/database.php.
$host = 'localhost';
$dbname = 'bugtrack_db';
$username = 'root';
$password = '';

try {
    // Attempt a database connection.
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    $connectionStatus = true;
    $errorMessage = '';

    // Get the actual connected database name.
    $stmt = $pdo->query("SELECT DATABASE() AS database_name");
    $databaseName = $stmt->fetch()['database_name'];

    // Count all bug records.
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM bugs");
    $totalBugs = (int) $stmt->fetch()['total'];

    // Count all bug history records.
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM bug_history");
    $totalHistory = (int) $stmt->fetch()['total'];

    // Retrieve the latest bug report.
    $stmt = $pdo->query("
        SELECT id, title, priority, status, date_reported
        FROM bugs
        ORDER BY date_reported DESC, id DESC
        LIMIT 1
    ");
    $latestBug = $stmt->fetch();

    // Retrieve the latest status history event.
    $stmt = $pdo->query("
        SELECT
            bh.bug_id,
            bh.previous_status,
            bh.new_status,
            bh.change_date,
            b.title AS bug_title
        FROM bug_history AS bh
        INNER JOIN bugs AS b
            ON bh.bug_id = b.id
        ORDER BY bh.change_date DESC, bh.id DESC
        LIMIT 1
    ");
    $latestHistory = $stmt->fetch();

} catch (PDOException $e) {
    // Keep technical details in the server error log.
    error_log('System verification error: ' . $e->getMessage());

    $connectionStatus = false;
    $errorMessage = 'Unable to connect to the database or retrieve its verification information.';
}

$pageTitle = 'System Verification';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="page-title">
    <h1>System Verification</h1>
    <p>Check the database connection and verify the system records.</p>
</div>

<!-- Database connection status -->
<div class="card verification-card">
    <h2>Database Connection</h2>

    <div class="connection-status <?= $connectionStatus ? 'connected' : 'disconnected' ?>">
        <span class="connection-indicator"></span>

        <div>
            <strong>
                <?= $connectionStatus ? 'Connected Successfully' : 'Connection Failed' ?>
            </strong>

            <p>
                <?= $connectionStatus
                    ? 'BugTrack can communicate with its MySQL database.'
                    : e($errorMessage) ?>
            </p>
        </div>
    </div>

    <div class="verification-detail">
        <span>Database Name</span>
        <strong><?= e($databaseName) ?></strong>
    </div>
</div>

<?php if ($connectionStatus): ?>

    <!-- Database record summary -->
    <div class="verification-grid">

        <div class="verification-stat">
            <div class="verification-stat-icon">🐞</div>
            <div>
                <p>Total Bug Records</p>
                <h2><?= e($totalBugs) ?></h2>
            </div>
        </div>

        <div class="verification-stat">
            <div class="verification-stat-icon">📋</div>
            <div>
                <p>Total History Records</p>
                <h2><?= e($totalHistory) ?></h2>
            </div>
        </div>

    </div>

    <!-- Latest bug report -->
    <div class="card">
        <h2>Latest Bug Report</h2>

        <?php if ($latestBug): ?>

            <div class="verification-record">
                <div class="verification-detail">
                    <span>Bug ID</span>
                    <strong>#<?= e($latestBug['id']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Bug Title</span>
                    <strong><?= e($latestBug['title']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Priority</span>
                    <strong><?= e($latestBug['priority']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Current Status</span>
                    <strong><?= e($latestBug['status']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Date Reported</span>
                    <strong><?= e($latestBug['date_reported']) ?></strong>
                </div>
            </div>

        <?php else: ?>

            <p class="empty-state">No bug records are available.</p>

        <?php endif; ?>
    </div>

    <!-- Latest history event -->
    <div class="card">
        <h2>Latest Status Change</h2>

        <?php if ($latestHistory): ?>

            <div class="verification-record">
                <div class="verification-detail">
                    <span>Bug ID</span>
                    <strong>#<?= e($latestHistory['bug_id']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Bug Title</span>
                    <strong><?= e($latestHistory['bug_title']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Previous Status</span>
                    <strong>
                        <?= $latestHistory['previous_status'] === null
                            ? 'New Report'
                            : e($latestHistory['previous_status']) ?>
                    </strong>
                </div>

                <div class="verification-detail">
                    <span>New Status</span>
                    <strong><?= e($latestHistory['new_status']) ?></strong>
                </div>

                <div class="verification-detail">
                    <span>Change Date and Time</span>
                    <strong><?= e($latestHistory['change_date']) ?></strong>
                </div>
            </div>

        <?php else: ?>

            <p class="empty-state">No history records are available.</p>

        <?php endif; ?>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

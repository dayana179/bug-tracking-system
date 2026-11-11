
<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Create a CSRF token if one does not already exist.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all bugs that are not yet resolved.
$stmt = $pdo->prepare("
    SELECT id, title, description, priority, status, date_reported
    FROM bugs
    WHERE status IN ('Open', 'In Progress')
    ORDER BY date_reported DESC, id DESC
");
$stmt->execute();
$bugs = $stmt->fetchAll();

$pageTitle = 'Active Bugs';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="page-title">
    <div>
        <h1>Active Bugs</h1>
        <p>View and manage bugs that are still being worked on.</p>
    </div>
</div>

<?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
    <div class="alert alert-success">
        Bug status updated successfully. The history record was also saved.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        The status could not be updated. Please try again.
    </div>
<?php endif; ?>

<div class="card">
    <h2>Active Bug List</h2>
    <p class="description">
        Only Open and In Progress bugs are displayed here.
    </p>

    <?php if (count($bugs) > 0): ?>
        <div class="table-responsive">
            <table class="data-table active-bugs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bug Details</th>
                        <th>Priority</th>
                        <th>Current Status</th>
                        <th>Date Reported</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bugs as $bug): ?>
                        <tr>
                            <td><?= e($bug['id']) ?></td>
                            <td>
                                <strong><?= e($bug['title']) ?></strong>
                                <div class="bug-description">
                                    <?= e($bug['description']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="priority <?= strtolower(e($bug['priority'])) ?>">
                                    <?= e($bug['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?= $bug['status'] === 'Open' ? 'open' : 'in-progress' ?>">
                                    <?= e($bug['status']) ?>
                                </span>
                            </td>
                            <td><?= e($bug['date_reported']) ?></td>
                            <td>
                                <form method="POST" action="update_status.php" class="status-form">
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION['csrf_token']) ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="bug_id"
                                        value="<?= e($bug['id']) ?>"
                                    >

                                    <?php if ($bug['status'] === 'Open'): ?>
                                        <input type="hidden" name="new_status" value="In Progress">
                                        <button type="submit" class="btn btn-primary">
                                            Start Progress
                                        </button>
                                    <?php elseif ($bug['status'] === 'In Progress'): ?>
                                        <input type="hidden" name="new_status" value="Resolved">
                                        <button type="submit" class="btn btn-success">
                                            Mark Resolved
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">There are currently no active bugs.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

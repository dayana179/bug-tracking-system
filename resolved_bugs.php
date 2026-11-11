
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Retrieve all resolved bugs.
$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        description,
        priority,
        status,
        date_reported,
        date_resolved
    FROM bugs
    WHERE status = 'Resolved'
    ORDER BY date_resolved DESC, id DESC
");

$stmt->execute();
$resolvedBugs = $stmt->fetchAll();

$pageTitle = 'Resolved Bugs';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="page-title">
    <h1>Resolved Bugs</h1>
    <p>View bugs that have been successfully resolved.</p>
</div>

<div class="card">
    <div class="section-heading">
        <div>
            <h2>Resolved Bug Records</h2>
            <p class="table-description">
                A record of all bugs that have completed the resolution process.
            </p>
        </div>

        <span class="resolved-count">
            Total Resolved: <?= count($resolvedBugs) ?>
        </span>
    </div>

    <?php if (count($resolvedBugs) > 0): ?>

        <div class="table-responsive">
            <table class="data-table resolved-bugs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bug Details</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                        <th>Date Resolved</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($resolvedBugs as $bug): ?>
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
                                <span class="status resolved">
                                    <?= e($bug['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= e($bug['date_reported']) ?>
                            </td>

                            <td>
                                <?= $bug['date_resolved']
                                    ? e($bug['date_resolved'])
                                    : 'Not recorded' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <h3>No Resolved Bugs Yet</h3>
            <p>
                Resolved bugs will appear here after their status is updated
                from the Active Bugs page.
            </p>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

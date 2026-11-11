
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Retrieve bug history together with the related bug title.
$stmt = $pdo->prepare("
    SELECT
        bh.id AS history_id,
        bh.bug_id,
        bh.previous_status,
        bh.new_status,
        bh.change_date,
        b.title AS bug_title
    FROM bug_history AS bh
    INNER JOIN bugs AS b
        ON bh.bug_id = b.id
    ORDER BY bh.change_date DESC, bh.id DESC
");

$stmt->execute();
$historyRecords = $stmt->fetchAll();

// Count the number of history records.
$totalHistory = count($historyRecords);

$pageTitle = 'Bug History';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="page-title">
    <h1>Bug History</h1>
    <p>Track all recorded bug reports and status changes.</p>
</div>

<div class="card">
    <div class="section-heading">
        <div>
            <h2>Bug Activity Timeline</h2>
            <p class="table-description">
                A chronological record of bug reports and status updates.
            </p>
        </div>

        <span class="history-count">
            Total Records: <?= e($totalHistory) ?>
        </span>
    </div>

    <?php if ($totalHistory > 0): ?>

        <div class="table-responsive">
            <table class="data-table history-table">
                <thead>
                    <tr>
                        <th>History ID</th>
                        <th>Bug ID</th>
                        <th>Bug Title</th>
                        <th>Previous Status</th>
                        <th>New Status</th>
                        <th>Date and Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($historyRecords as $record): ?>
                        <tr>
                            <td><?= e($record['history_id']) ?></td>

                            <td>
                                #<?= e($record['bug_id']) ?>
                            </td>

                            <td>
                                <strong><?= e($record['bug_title']) ?></strong>
                            </td>

                            <td>
                                <?php if ($record['previous_status'] === null): ?>
                                    <span class="history-initial">
                                        New Report
                                    </span>
                                <?php else: ?>
                                    <span class="history-status">
                                        <?= e($record['previous_status']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="history-status <?= strtolower(str_replace(' ', '-', e($record['new_status']))) ?>">
                                    <?= e($record['new_status']) ?>
                                </span>
                            </td>

                            <td class="history-date">
                                <?= e($record['change_date']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <h3>No Bug History Available</h3>
            <p>
                History records will appear here when bugs are reported
                or their statuses are updated.
            </p>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

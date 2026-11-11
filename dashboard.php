
<?php

// Connect to the database
require_once 'config/database.php';

// Set page title
$pageTitle = 'Dashboard';

// ==========================================
// 1. RETRIEVE OVERALL BUG STATISTICS
// ==========================================

$statsQuery = "
    SELECT
        COUNT(*) AS total_bugs,

        SUM(CASE WHEN status = 'Open'
            THEN 1 ELSE 0 END) AS open_bugs,

        SUM(CASE WHEN status = 'In Progress'
            THEN 1 ELSE 0 END) AS in_progress_bugs,

        SUM(CASE WHEN status = 'Resolved'
            THEN 1 ELSE 0 END) AS resolved_bugs,

        SUM(CASE WHEN priority = 'High'
            THEN 1 ELSE 0 END) AS high_bugs,

        SUM(CASE WHEN priority = 'Medium'
            THEN 1 ELSE 0 END) AS medium_bugs,

        SUM(CASE WHEN priority = 'Low'
            THEN 1 ELSE 0 END) AS low_bugs,

        SUM(CASE
            WHEN priority = 'High'
            AND status != 'Resolved'
            THEN 1 ELSE 0
        END) AS high_unresolved

    FROM bugs
";

$statsStatement = $pdo->query($statsQuery);
$stats = $statsStatement->fetch();

// Convert database values to integers
$totalBugs = (int) ($stats['total_bugs'] ?? 0);
$openBugs = (int) ($stats['open_bugs'] ?? 0);
$inProgressBugs = (int) ($stats['in_progress_bugs'] ?? 0);
$resolvedBugs = (int) ($stats['resolved_bugs'] ?? 0);

$highBugs = (int) ($stats['high_bugs'] ?? 0);
$mediumBugs = (int) ($stats['medium_bugs'] ?? 0);
$lowBugs = (int) ($stats['low_bugs'] ?? 0);
$highUnresolved = (int) ($stats['high_unresolved'] ?? 0);

// ==========================================
// 2. CALCULATE RESOLUTION RATE
// ==========================================

$resolutionRate = $totalBugs > 0
    ? ($resolvedBugs / $totalBugs) * 100
    : 0;

// ==========================================
// 3. CALCULATE AVERAGE RESOLUTION TIME
// ==========================================

$averageTimeQuery = "
    SELECT AVG(
        TIMESTAMPDIFF(
            SECOND,
            date_reported,
            date_resolved
        )
    ) AS average_seconds

    FROM bugs

    WHERE status = 'Resolved'
    AND date_resolved IS NOT NULL
";

$averageTimeStatement = $pdo->query($averageTimeQuery);
$averageTimeResult = $averageTimeStatement->fetch();

$averageSeconds = $averageTimeResult['average_seconds'] ?? null;

$averageResolutionTime = 'No resolved bugs yet';

if ($averageSeconds !== null) {

    $averageHours = (float) $averageSeconds / 3600;

    if ($averageHours < 1) {

        $averageResolutionTime =
            number_format($averageHours * 60, 0) . ' minutes';

    } elseif ($averageHours < 48) {

        $averageResolutionTime =
            number_format($averageHours, 1) . ' hours';

    } else {

        $averageDays = $averageHours / 24;

        $averageResolutionTime =
            number_format($averageDays, 1) . ' days';
    }
}

// ==========================================
// 4. RETRIEVE RECENT BUG REPORTS
// ==========================================

$recentQuery = "
    SELECT
        id,
        title,
        priority,
        status,
        date_reported

    FROM bugs

    ORDER BY date_reported DESC, id DESC

    LIMIT 5
";

$recentStatement = $pdo->query($recentQuery);
$recentBugs = $recentStatement->fetchAll();

// ==========================================
// 5. RETRIEVE HIGH-PRIORITY UNRESOLVED BUGS
// ==========================================

$alertQuery = "
    SELECT
        id,
        title,
        status,
        date_reported

    FROM bugs

    WHERE priority = 'High'
    AND status != 'Resolved'

    ORDER BY date_reported ASC, id ASC

    LIMIT 5
";

$alertStatement = $pdo->query($alertQuery);
$highPriorityBugs = $alertStatement->fetchAll();

// ==========================================
// 6. PREPARE CHART PERCENTAGES
// ==========================================

$statusChartTotal = max($totalBugs, 1);

$openPercentage =
    ($openBugs / $statusChartTotal) * 100;

$progressPercentage =
    ($inProgressBugs / $statusChartTotal) * 100;

$resolvedPercentage =
    ($resolvedBugs / $statusChartTotal) * 100;

$priorityChartTotal = max($totalBugs, 1);

$highPercentage =
    ($highBugs / $priorityChartTotal) * 100;

$mediumPercentage =
    ($mediumBugs / $priorityChartTotal) * 100;

$lowPercentage =
    ($lowBugs / $priorityChartTotal) * 100;

// Load shared website layout
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<!-- ======================================
     DASHBOARD INTRODUCTION
======================================= -->

<div class="page-heading">

    <h1 class="page-title">Dashboard</h1>

    <p class="page-description">
        Monitor bug reports, project performance,
        issue priorities and resolution progress.
    </p>

</div>


<!-- ======================================
     MAIN SUMMARY CARDS
======================================= -->

<div class="dashboard-grid">

    <div class="stat-card total-card">

        <div class="stat-info">
            <p>Total Bugs</p>
            <h2><?= e($totalBugs) ?></h2>
        </div>

        <div class="stat-icon">🐛</div>

    </div>


    <div class="stat-card open-card">

        <div class="stat-info">
            <p>Open Bugs</p>
            <h2><?= e($openBugs) ?></h2>
        </div>

        <div class="stat-icon">⚠️</div>

    </div>


    <div class="stat-card progress-card">

        <div class="stat-info">
            <p>In Progress</p>
            <h2><?= e($inProgressBugs) ?></h2>
        </div>

        <div class="stat-icon">⏳</div>

    </div>


    <div class="stat-card resolved-card">

        <div class="stat-info">
            <p>Resolved Bugs</p>
            <h2><?= e($resolvedBugs) ?></h2>
        </div>

        <div class="stat-icon">✅</div>

    </div>

</div>


<!-- ======================================
     PROJECT PERFORMANCE CARDS
======================================= -->

<div class="performance-grid">

    <div class="performance-card">

        <div class="performance-heading">
            <span>Resolution Rate</span>
            <span class="performance-icon">📈</span>
        </div>

        <h2><?= number_format($resolutionRate, 1) ?>%</h2>

        <div class="progress-track">

            <div
                class="progress-fill resolution-fill"
                style="width: <?= e($resolutionRate) ?>%;"
            ></div>

        </div>

        <p>
            <?= e($resolvedBugs) ?> of
            <?= e($totalBugs) ?> bugs resolved
        </p>

    </div>


    <div class="performance-card">

        <div class="performance-heading">
            <span>Average Resolution Time</span>
            <span class="performance-icon">⏱️</span>
        </div>

        <h2 class="average-time">
            <?= e($averageResolutionTime) ?>
        </h2>

        <p>
            Average time taken to resolve recorded bugs.
        </p>

    </div>

</div>


<!-- ======================================
     BUG DISTRIBUTION CHARTS
======================================= -->

<div class="analytics-grid">

    <!-- Status distribution -->

    <div class="card analytics-card">

        <h2>Bug Status Distribution</h2>

        <p class="table-description">
            Current distribution of all recorded bugs.
        </p>


        <div class="chart-row">

            <div class="chart-label">
                <span>Open</span>
                <strong><?= e($openBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-open"
                    style="width: <?= e($openPercentage) ?>%;"
                ></div>

            </div>

        </div>


        <div class="chart-row">

            <div class="chart-label">
                <span>In Progress</span>
                <strong><?= e($inProgressBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-progress"
                    style="width: <?= e($progressPercentage) ?>%;"
                ></div>

            </div>

        </div>


        <div class="chart-row">

            <div class="chart-label">
                <span>Resolved</span>
                <strong><?= e($resolvedBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-resolved"
                    style="width: <?= e($resolvedPercentage) ?>%;"
                ></div>

            </div>

        </div>

    </div>


    <!-- Priority distribution -->

    <div class="card analytics-card">

        <h2>Bug Priority Distribution</h2>

        <p class="table-description">
            Distribution according to reported priority.
        </p>


        <div class="chart-row">

            <div class="chart-label">
                <span>High Priority</span>
                <strong><?= e($highBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-high"
                    style="width: <?= e($highPercentage) ?>%;"
                ></div>

            </div>

        </div>


        <div class="chart-row">

            <div class="chart-label">
                <span>Medium Priority</span>
                <strong><?= e($mediumBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-medium"
                    style="width: <?= e($mediumPercentage) ?>%;"
                ></div>

            </div>

        </div>


        <div class="chart-row">

            <div class="chart-label">
                <span>Low Priority</span>
                <strong><?= e($lowBugs) ?></strong>
            </div>

            <div class="chart-track">

                <div
                    class="chart-bar chart-low"
                    style="width: <?= e($lowPercentage) ?>%;"
                ></div>

            </div>

        </div>

    </div>

</div>


<!-- ======================================
     HIGH-PRIORITY BUG ALERTS
======================================= -->

<div class="card alert-panel">

    <div class="section-heading">

        <div>
            <h2>High-Priority Issues Requiring Attention</h2>

            <p class="table-description">
                Unresolved High-priority bugs that may require
                immediate project team attention.
            </p>
        </div>

        <span class="alert-count">
            <?= e($highUnresolved) ?> Unresolved
        </span>

    </div>


    <?php if (count($highPriorityBugs) > 0): ?>

        <div class="table-responsive">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bug Title</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($highPriorityBugs as $bug): ?>

                        <tr>

                            <td><?= e($bug['id']) ?></td>

                            <td><?= e($bug['title']) ?></td>

                            <td>
                                <span class="status-badge
                                    <?= $bug['status'] === 'Open'
                                        ? 'status-open'
                                        : 'status-progress' ?>">
                                    <?= e($bug['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= e(date(
                                    'd M Y, h:i A',
                                    strtotime($bug['date_reported'])
                                )) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <h3>No Unresolved High-Priority Bugs</h3>

            <p>
                All currently recorded High-priority bugs
                have been resolved, or no such bugs exist.
            </p>

        </div>

    <?php endif; ?>

</div>


<!-- ======================================
     RECENT BUG REPORTS
======================================= -->

<div class="card recent-card">

    <div class="section-heading">

        <div>

            <h2>Recent Bug Reports</h2>

            <p class="table-description">
                The five most recently reported bugs.
            </p>

        </div>

        <a href="active_bugs.php" class="btn">
            View Active Bugs
        </a>

    </div>


    <div class="table-responsive">

        <table class="data-table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Bug Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date Reported</th>
                </tr>

            </thead>


            <tbody>

                <?php if (count($recentBugs) > 0): ?>

                    <?php foreach ($recentBugs as $bug): ?>

                        <tr>

                            <td><?= e($bug['id']) ?></td>

                            <td><?= e($bug['title']) ?></td>

                            <td>

                                <span class="priority-badge
                                    <?= strtolower(e($bug['priority'])) ?>">

                                    <?= e($bug['priority']) ?>

                                </span>

                            </td>


                            <td>

                                <span class="status-badge
                                    <?php
                                    if ($bug['status'] === 'Open') {
                                        echo 'status-open';
                                    } elseif ($bug['status'] === 'In Progress') {
                                        echo 'status-progress';
                                    } else {
                                        echo 'status-resolved';
                                    }
                                    ?>">

                                    <?= e($bug['status']) ?>

                                </span>

                            </td>


                            <td>

                                <?= e(date(
                                    'd M Y, h:i A',
                                    strtotime($bug['date_reported'])
                                )) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5">
                            No bug reports have been recorded yet.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php

// Load shared footer
require_once 'includes/footer.php';

?>

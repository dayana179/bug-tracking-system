
<?php
// Start the session for CSRF protection.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/functions.php';

// Create a CSRF token if one does not already exist.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Read search and filter values.
$keyword = trim($_GET['keyword'] ?? '');
$priority = $_GET['priority'] ?? 'All';
$status = $_GET['status'] ?? 'All';
$sort = $_GET['sort'] ?? 'newest';

// Only allow recognised filter values.
$allowedPriorities = ['All', 'High', 'Medium', 'Low'];
$allowedStatuses = ['All', 'Open', 'In Progress'];

if (!in_array($priority, $allowedPriorities, true)) {
    $priority = 'All';
}

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'All';
}

// Sorting is restricted to these predefined SQL expressions.
$sortOptions = [
    'newest' => 'date_reported DESC, id DESC',
    'oldest' => 'date_reported ASC, id ASC',
    'title_asc' => 'title ASC',
    'title_desc' => 'title DESC',
    'priority' => "FIELD(priority, 'High', 'Medium', 'Low'), date_reported DESC"
];

if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'newest';
}

try {
    // Build the SQL query safely.
    $sql = "SELECT id, title, description, priority, status, date_reported
            FROM bugs
            WHERE status IN ('Open', 'In Progress')";

    $conditions = [];
    $parameters = [];


    // Search bug title and description.
    if ($keyword !== '') {
        $conditions[] = "(title LIKE :keyword_title OR description LIKE :keyword_description)";

        $searchTerm = '%' . $keyword . '%';

        $parameters[':keyword_title'] = $searchTerm;
        $parameters[':keyword_description'] = $searchTerm;
    }


    // Filter by priority.
    if ($priority !== 'All') {
        $conditions[] = "priority = :priority";
        $parameters[':priority'] = $priority;
    }

    // Filter by active status.
    if ($status !== 'All') {
        $conditions[] = "status = :status";
        $parameters[':status'] = $status;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY " . $sortOptions[$sort];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parameters);
    $bugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Active Bugs search error: ' . $e->getMessage());
    $bugs = [];
    $databaseError = true;
}

if (!isset($databaseError)) {
    $databaseError = false;
}

// Shorten long descriptions safely.
function previewDescription($description, $limit = 100)
{
    $description = (string) $description;

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($description, 0, $limit, '...');
    }

    if (strlen($description) > $limit) {
        return substr($description, 0, $limit - 3) . '...';
    }

    return $description;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <div class="page-heading">
        <h1>Active Bugs</h1>
        <p>Search, filter and manage bugs that are still being worked on.</p>
    </div>

    <!-- Search and filter panel -->
    <section class="filter-panel">

        <h2>Search and Filter Bugs</h2>

        <form method="GET" action="active_bugs.php" class="bug-filter-form">

            <div class="filter-field filter-search">
                <label for="keyword">Search Keyword</label>
                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    placeholder="Enter bug title or description"
                    value="<?= e($keyword) ?>"
                >
            </div>

            <div class="filter-field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="All" <?= $priority === 'All' ? 'selected' : '' ?>>All Priorities</option>
                    <option value="High" <?= $priority === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= $priority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= $priority === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All Active Statuses</option>
                    <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A–Z</option>
                    <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z–A</option>
                    <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Priority: High to Low</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="active_bugs.php" class="btn btn-secondary">Clear</a>
            </div>

        </form>
    </section>

    <!-- Search results -->
    <section class="table-section">

        <div class="table-heading">
            <div>
                <h2>Active Bug Reports</h2>
                <p>
                    <?= count($bugs) ?>
                    matching bug<?= count($bugs) === 1 ? '' : 's' ?> found
                </p>
            </div>
        </div>

        <?php if ($databaseError): ?>

            <div class="empty-state">
                <h3>Unable to load bug reports</h3>
                <p>Please check the database connection and try again.</p>
            </div>

        <?php elseif (empty($bugs)): ?>

            <div class="empty-state">
                <h3>No matching bugs found</h3>
                <p>Try changing your keyword or filter selections.</p>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="data-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bug Details</th>
                            <th>Priority</th>
                            <th>Status</th>
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
                                    <p class="bug-description-preview">
                                        <?= e(previewDescription($bug['description'])) ?>
                                    </p>
                                </td>

                                <td>
                                    <span class="priority-badge priority-<?= strtolower(e($bug['priority'])) ?>">
                                        <?= e($bug['priority']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', e($bug['status']))) ?>">
                                        <?= e($bug['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date('d M Y, h:i A', strtotime($bug['date_reported']))) ?>
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="update_status.php"
                                        class="status-update-form"
                                        onsubmit="return confirm('Are you sure you want to update this bug status?');"
                                    >

                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="bug_id" value="<?= e($bug['id']) ?>">

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

        <?php endif; ?>

    </section>

<?php include 'includes/footer.php'; ?>

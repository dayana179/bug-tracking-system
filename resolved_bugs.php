<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Resolved Bugs';

// Read and validate search filters.
$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$allowedPriorities = ['Low', 'Medium', 'High'];
$allowedSorts = ['newest', 'oldest', 'title_asc', 'title_desc', 'priority'];

if (!in_array($priority, $allowedPriorities, true)) {
    $priority = '';
}

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}

$where = ["status = 'Resolved'"];
$params = [];

if ($search !== '') {
    $where[] = '(title LIKE :search_title OR description LIKE :search_description)';
    $params['search_title'] = '%' . $search . '%';
    $params['search_description'] = '%' . $search . '%';
}

if ($priority !== '') {
    $where[] = 'priority = :priority';
    $params['priority'] = $priority;
}

$orderBy = [
    'newest' => 'date_resolved DESC, id DESC',
    'oldest' => 'date_resolved ASC, id ASC',
    'title_asc' => 'title ASC',
    'title_desc' => 'title DESC',
    'priority' => "CASE priority
        WHEN 'High' THEN 1
        WHEN 'Medium' THEN 2
        WHEN 'Low' THEN 3
        END ASC, date_resolved DESC"
];

$sql = 'SELECT * FROM bugs WHERE ' . implode(' AND ', $where)
     . ' ORDER BY ' . $orderBy[$sort];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

    <div class="page-heading">
        <h1>Resolved Bugs</h1>
        <p>Search and review bug reports that have been resolved.</p>
    </div>

    <section class="filter-panel">
        <h2>Search and Filter Resolved Bugs</h2>

        <form method="GET" action="resolved_bugs.php" class="bug-filter-form">
            <div class="filter-field filter-search">
                <label for="search">Keyword Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Search title or description..."
                    maxlength="150"
                >
            </div>

            <div class="filter-field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="">All Priorities</option>
                    <?php foreach ($allowedPriorities as $option): ?>
                        <option value="<?= e($option) ?>"
                            <?= $priority === $option ? 'selected' : '' ?>>
                            <?= e($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Resolved</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>First Resolved</option>
                    <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A–Z</option>
                    <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z–A</option>
                    <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Highest Priority First</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="resolved_bugs.php" class="btn btn-secondary">Clear Filters</a>
            </div>
        </form>
    </section>

    <section class="table-section">
        <div class="table-heading">
            <h2>Resolved Bug Records</h2>
            <p>
                Found <strong><?= count($bugs) ?></strong>
                matching resolved bug<?= count($bugs) === 1 ? '' : 's' ?>.
            </p>
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
                        <th>Date Resolved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bugs)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                No resolved bugs match your search or filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bugs as $bug): ?>
                            <tr>
                                <td><?= e($bug['id']) ?></td>
                                <td>
                                    <strong><?= e($bug['title']) ?></strong>
                                    <div class="bug-description-preview">
                                        <?= e(mb_strimwidth($bug['description'], 0, 90, '...')) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-badge priority-<?= strtolower(e($bug['priority'])) ?>">
                                        <?= e($bug['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-resolved">
                                        <?= e($bug['status']) ?>
                                    </span>
                                </td>
                                <td><?= e($bug['date_reported']) ?></td>
                                <td><?= e($bug['date_resolved'] ?? 'Not recorded') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
session_start();

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: active_bugs.php');
    exit;
}

// Check the CSRF token.
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submittedToken)
) {
    header('Location: active_bugs.php?error=1');
    exit;
}

// Validate the submitted bug ID.
$bugId = filter_var(
    $_POST['bug_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$newStatus = $_POST['new_status'] ?? '';

if (!$bugId || !is_string($newStatus)) {
    header('Location: active_bugs.php?error=1');
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock the bug row while its status is being changed.
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM bugs
        WHERE id = :id
        FOR UPDATE
    ");
    $stmt->execute(['id' => $bugId]);
    $bug = $stmt->fetch();

    if (!$bug) {
        throw new Exception('Bug not found.');
    }

    $previousStatus = $bug['status'];

    // Allow only the required workflow transitions.
    $allowedTransitions = [
        'Open' => 'In Progress',
        'In Progress' => 'Resolved'
    ];

    if (
        !isset($allowedTransitions[$previousStatus]) ||
        $allowedTransitions[$previousStatus] !== $newStatus
    ) {
        throw new Exception('Invalid status transition.');
    }

    // Update the bug. Set resolution date only when resolved.
    $updateStmt = $pdo->prepare("
        UPDATE bugs
        SET
            status = :new_status,
            date_resolved = CASE
                WHEN :resolved_status = 'Resolved'
                THEN CURRENT_TIMESTAMP
                ELSE date_resolved
            END
        WHERE id = :id
    ");

    $updateStmt->execute([
        'new_status' => $newStatus,
        'resolved_status' => $newStatus,
        'id' => $bugId
    ]);

    // Automatically record the status change.
    $historyStmt = $pdo->prepare("
        INSERT INTO bug_history
            (bug_id, previous_status, new_status)
        VALUES
            (:bug_id, :previous_status, :new_status)
    ");

    $historyStmt->execute([
        'bug_id' => $bugId,
        'previous_status' => $previousStatus,
        'new_status' => $newStatus
    ]);

    // Save both the bug update and its history record together.
    $pdo->commit();

    header('Location: active_bugs.php?updated=1');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Bug status update error: ' . $e->getMessage());

    header('Location: active_bugs.php?error=1');
    exit;
}

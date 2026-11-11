<aside class="sidebar" id="sidebar">

    <div class="sidebar-heading">
        MAIN MENU
    </div>

    <nav class="navigation">

        <a href="dashboard.php"
           class="nav-link <?= isActivePage('dashboard.php') ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <a href="report_bug.php"
           class="nav-link <?= isActivePage('report_bug.php') ?>">
            <span class="nav-icon">➕</span>
            <span>Report Bug</span>
        </a>

        <a href="active_bugs.php"
           class="nav-link <?= isActivePage('active_bugs.php') ?>">
            <span class="nav-icon">🐛</span>
            <span>Active Bugs</span>
        </a>

        <a href="resolved_bugs.php"
           class="nav-link <?= isActivePage('resolved_bugs.php') ?>">
            <span class="nav-icon">✅</span>
            <span>Resolved Bugs</span>
        </a>

        <a href="bug_history.php"
           class="nav-link <?= isActivePage('bug_history.php') ?>">
            <span class="nav-icon">📜</span>
            <span>Bug History</span>
        </a>

        <a href="verify.php"
           class="nav-link <?= isActivePage('verify.php') ?>">
            <span class="nav-icon">🔍</span>
            <span>System Verification</span>
        </a>

    </nav>

    <div class="sidebar-note">
        <p>BugTrack</p>
        <small>Project Monitoring System</small>
    </div>

</aside>

<main class="main-content">
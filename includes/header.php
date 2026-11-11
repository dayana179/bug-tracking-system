<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'BugTrack';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?> | BugTrack</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-shell">

    <header class="topbar">
        <div class="brand">
            <span class="brand-icon">🐞</span>
            <span>BugTrack</span>
        </div>

        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Toggle navigation"
            aria-expanded="false"
        >
            ☰
        </button>

        <div class="topbar-caption">
            Bug Tracking and Project Monitoring System
        </div>
    </header>
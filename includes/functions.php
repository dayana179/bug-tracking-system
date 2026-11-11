<?php

// Safely display text in HTML
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Check which page is currently active
function isActivePage($pageName)
{
    return basename($_SERVER['PHP_SELF']) === $pageName
        ? 'active'
        : '';
}
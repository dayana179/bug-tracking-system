<?php
// Database connection settings
$host = 'localhost';
$dbname = 'bugtrack_db';
$username = 'root';
$password = ''; 
// Create the database connection
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);

    // Show database errors as exceptions
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Return database records as associative arrays
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    // Use real prepared statements
    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

} catch (PDOException $e) {
    // Log the technical error for the developer
    error_log("Database connection error: " . $e->getMessage());

    // Stop execution with a general error message
    die("Unable to connect to the database. Please check your configuration.");
}
?>
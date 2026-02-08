<?php
/**
 * Database Setup Script
 * Run once: http://localhost/love_app/setup_db.php
 */

$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS love_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✔ Database 'love_app' created.<br>";

    $pdo->exec("USE love_app");

    // Drop old tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS story_pages");
    $pdo->exec("DROP TABLE IF EXISTS stories");
    echo "✔ Old tables dropped.<br>";

    // Create new stories table (JSON-based)
    $pdo->exec("
        CREATE TABLE stories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            story_key VARCHAR(32) UNIQUE NOT NULL,
            story_json LONGTEXT NOT NULL,
            is_paid TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_story_key (story_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✔ Table 'stories' created.<br>";

    // Unlock requests (payment screenshot + admin approval)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS unlock_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            story_key VARCHAR(32) NOT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(64) NOT NULL,
            screenshot_url VARCHAR(512) NOT NULL,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            token VARCHAR(64) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_story_key (story_key),
            INDEX idx_status (status),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✔ Table 'unlock_requests' created or exists.<br>";

    echo "<br><strong>✅ Setup complete!</strong><br>";
    echo "<a href='index.php'>Go to LoveFun App →</a>";

} catch (PDOException $e) {
    die("❌ Setup failed: " . $e->getMessage());
}

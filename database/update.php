<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Read and execute the update SQL file
    $sql = file_get_contents(__DIR__ . '/update_schema.sql');
    $conn->exec($sql);

    echo "Database schema updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database schema: " . $e->getMessage();
} 
<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/garbage_schedules.sql');
    $db->exec($sql);

    echo "Garbage schedules tables created successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
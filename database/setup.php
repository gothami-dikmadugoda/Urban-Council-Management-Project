<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/schema.sql');

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }

    echo "\nDatabase setup completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 
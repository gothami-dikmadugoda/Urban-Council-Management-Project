<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/add_department_id.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement separately
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }

    echo "<br>Department ID column added to complaints table successfully!";
} catch (PDOException $e) {
    echo "Error adding department_id column: " . $e->getMessage();
} 
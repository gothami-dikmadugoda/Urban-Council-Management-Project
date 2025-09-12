<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/garbage_schedules.sql');
    
    // Execute the SQL
    if ($db->multi_query($sql)) {
        do {
            // Store or discard the result
            if ($result = $db->store_result()) {
                $result->free();
            }
        } while ($db->next_result());
        
        echo "Garbage schedules table created successfully.\n";
    } else {
        throw new Exception("Error executing SQL: " . $db->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    $db->close();
} 
<?php
// Check for PostgreSQL environment variables (for Replit environment)
$use_postgres = false;

if (getenv('DATABASE_URL')) {
    $use_postgres = true;
    $db_url = parse_url(getenv('DATABASE_URL'));
    $pg_host = $db_url['host'];
    $pg_port = isset($db_url['port']) ? $db_url['port'] : '5432';
    $pg_user = $db_url['user'];
    $pg_password = $db_url['pass'];
    $pg_dbname = ltrim($db_url['path'], '/');
    
    // Create PostgreSQL connection using PDO
    try {
        $dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname;user=$pg_user;password=$pg_password";
        $conn = new PDO($dsn);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $db_error = "PostgreSQL Connection failed: " . $e->getMessage();
    }
} else {
    // Fallback to MySQL for local development with XAMPP
    $host = 'localhost';
    $dbname = 'urban_council';
    $username = 'root';
    $password = '';
    $port = 3306;
    
    // Create a MySQL connection
    try {
        $conn = new mysqli($host.':'.$port, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            $db_error = "MySQL Connection failed: " . $conn->connect_error;
        }
    } catch (Exception $e) {
        $db_error = "MySQL Connection failed: " . $e->getMessage();
    }




    
}


// Define a query function that works with both connection types
function db_query($sql, $params = [], $fetch_mode = null) {
    global $conn, $use_postgres;
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            if (stripos($sql, 'SELECT') === 0) {
                if ($fetch_mode == 'single') {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    } else {
        // MySQL query using mysqli
        try {
            if (!empty($params)) {
                // Convert PDO-style parameter array to mysqli prepared statement
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    error_log("MySQL prepare error: " . $conn->error);
                    return false;
                }
                
                // Build bind_param arguments
                $types = '';
                $bind_values = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bind_values[] = $param;
                }
                
                // Create references for bind_param
                $bind_params = array();
                $bind_params[] = $types;
                
                for ($i = 0; $i < count($bind_values); $i++) {
                    $bind_params[] = &$bind_values[$i];
                }
                
                call_user_func_array(array($stmt, 'bind_param'), $bind_params);
                $stmt->execute();
                
                if (stripos($sql, 'SELECT') === 0) {
                    $result = $stmt->get_result();
                    if ($fetch_mode == 'single') {
                        return $result->fetch_assoc();
                    } else {
                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }
                        return $rows;
                    }
                }
                return true;
            } else {
                // Simple query without parameters
                $result = $conn->query($sql);
                
                if (stripos($sql, 'SELECT') === 0) {
                    if ($result === false) {
                        error_log("MySQL query error: " . $conn->error);
                        return false;
                    }
                    
                    if ($fetch_mode == 'single') {
                        return $result->fetch_assoc();
                    } else {
                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }
                        return $rows;
                    }
                }
                return ($result !== false);
            }
        } catch (Exception $e) {
            error_log("MySQL error: " . $e->getMessage());
            return false;
        }
    }
}
?>
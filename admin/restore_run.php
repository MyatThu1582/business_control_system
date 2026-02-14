<?php
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

set_time_limit(0);

if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
    $msg = "No file uploaded.";
    if (isset($_FILES['sql_file']['error'])) {
        switch ($_FILES['sql_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $msg = "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg = "File was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $msg = "No file specified.";
                break;
        }
    }
    echo json_encode(["status" => "error", "message" => $msg]);
    exit;
}

$tmpPath = $_FILES['sql_file']['tmp_name'];
$name = $_FILES['sql_file']['name'];

if (!preg_match('/\.sql$/i', $name)) {
    echo json_encode(["status" => "error", "message" => "Only .sql files are allowed."]);
    exit;
}

if (!is_uploaded_file($tmpPath) || !is_readable($tmpPath)) {
    echo json_encode(["status" => "error", "message" => "Uploaded file could not be read."]);
    exit;
}

try {
    $sql = file_get_contents($tmpPath);
    if ($sql === false || $sql === '') {
        throw new Exception("Could not read uploaded file or file is empty.");
    }

    // Use mysqli for multi_query so MySQL runs the whole script natively (reliable restore)
    $mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');

    // Drop all current tables before restore (avoid "table already exists")
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    $res = $mysqli->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $mysqli->real_escape_string(MYSQL_DATABASE) . "'");
    if ($res) {
        while ($row = $res->fetch_array()) {
            $table = $row[0];
            $mysqli->query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`");
        }
        $res->free();
    }
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

    $ok = $mysqli->multi_query($sql);
    if (!$ok) {
        $err = $mysqli->error;
        $mysqli->close();
        throw new Exception("SQL error: " . $err);
    }

    // Drain all result sets from multi_query (required for multi_query to complete)
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->next_result());

    if ($mysqli->errno) {
        $err = $mysqli->error;
        $mysqli->close();
        throw new Exception("SQL error: " . $err);
    }

    $mysqli->close();

    echo json_encode([
        "status"   => "success",
        "message"  => "Restore completed successfully. Data has been updated.",
        "executed" => true
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status"  => "error",
        "message"  => $e->getMessage()
    ]);
}

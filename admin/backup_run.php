<?php
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

require __DIR__ . '/../config/config.php';

set_time_limit(0);

$backupDir = __DIR__ . "/backups/";
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filename = "backup_" . date("Y-m-d_H-i-s") . ".sql";
$filepath = $backupDir . $filename;

$chunkSize = 200; // rows per INSERT batch

try {
    $fh = fopen($filepath, 'w');
    if (!$fh) {
        throw new Exception("Could not create backup file.");
    }

    // Header
    fwrite($fh, "-- PHP-only backup\n");
    fwrite($fh, "-- " . date('Y-m-d H:i:s') . "\n");
    fwrite($fh, "SET NAMES utf8mb4;\n");
    fwrite($fh, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

    // Get all tables
    $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote(MYSQL_DATABASE) . " ORDER BY TABLE_NAME");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $table = trim($table);
        if ($table === '') continue;

        // DROP TABLE
        fwrite($fh, "DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`;\n");

        // CREATE TABLE
        $createStmt = $pdo->query("SHOW CREATE TABLE `" . str_replace('`', '``', $table) . "`");
        $createRow = $createStmt->fetch(PDO::FETCH_NUM);
        if ($createRow) {
            fwrite($fh, $createRow[1] . ";\n\n");
        }

        // Column names
        $colsStmt = $pdo->query("SHOW COLUMNS FROM `" . str_replace('`', '``', $table) . "`");
        $columns = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $colList = '`' . implode('`,`', array_map(function ($c) { return str_replace('`', '``', $c); }, $columns)) . '`';

        // Data in chunks
        $offset = 0;
        while (true) {
            $stmt = $pdo->query("SELECT * FROM `" . str_replace('`', '``', $table) . "` LIMIT " . (int)$chunkSize . " OFFSET " . (int)$offset);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) break;

            $values = [];
            foreach ($rows as $row) {
                $rowVals = [];
                foreach ($columns as $col) {
                    $v = isset($row[$col]) ? $row[$col] : null;
                    if ($v === null) {
                        $rowVals[] = 'NULL';
                    } else {
                        $rowVals[] = $pdo->quote($v);
                    }
                }
                $values[] = '(' . implode(',', $rowVals) . ')';
            }
            fwrite($fh, "INSERT INTO `" . str_replace('`', '``', $table) . "` ($colList) VALUES\n" . implode(",\n", $values) . ";\n\n");
            $offset += $chunkSize;
        }
    }

    fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($fh);

    if (filesize($filepath) === 0) {
        @unlink($filepath);
        throw new Exception("Backup file is empty.");
    }

    echo json_encode([
        "status" => "success",
        "file"   => $filename
    ]);
} catch (Exception $e) {
    if (isset($fh) && is_resource($fh)) fclose($fh);
    if (file_exists($filepath)) @unlink($filepath);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}

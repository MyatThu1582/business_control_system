<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../Config/config.php';
require '../Config/common.php';

// Include PhpSpreadsheet
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    if (empty($_FILES['excel_file']['name']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = $_FILES['excel_file']['error'] === UPLOAD_ERR_NO_FILE
            ? "Please select an Excel file to import!"
            : "File upload failed. Please try again.";
        header("Location: supplier.php");
        exit;
    }

    $fileTmpPath = $_FILES['excel_file']['tmp_name'];

    try {
        // Load Excel file
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            $_SESSION['error'] = "The Excel file is empty.";
            header("Location: supplier.php");
            exit;
        }

        // Validate headers (normalize: trim and lowercase)
        $requiredHeaders = ['supplier_id', 'supplier_name', 'supplier_phone', 'supplier_address'];
        $headers = array_map(function ($h) { return strtolower(trim((string) $h)); }, $rows[0]);
        $missing = array_diff($requiredHeaders, $headers);

        if (!empty($missing)) {
            $_SESSION['error'] = "Import failed! Missing columns: " . implode(', ', $missing);
            header("Location: supplier.php");
            exit;
        }

        // Map headers to index
        $headerMap = array_flip($headers);

        // Insert rows
        $imported = 0;
        foreach ($rows as $i => $row) {
            if ($i == 0) continue; // skip header

            $data = [
                'supplier_id' => trim($row[$headerMap['supplier_id']] ?? ''),
                'supplier_name' => trim($row[$headerMap['supplier_name']] ?? ''),
                'supplier_phone' => trim($row[$headerMap['supplier_phone']] ?? ''),
                'supplier_address' => trim($row[$headerMap['supplier_address']] ?? '')
            ];

            // Skip if mandatory fields missing
            if (empty($data['supplier_id']) || empty($data['supplier_name']) || empty($data['supplier_phone'])) continue;

            // Prevent duplicates by supplier_id
            $stmtCheck = $pdo->prepare("SELECT id FROM supplier WHERE supplier_id = :supplier_id");
            $stmtCheck->execute(['supplier_id' => $data['supplier_id']]);
            if ($stmtCheck->rowCount() > 0) continue;

            // Insert into DB
            $stmt = $pdo->prepare("INSERT INTO supplier (supplier_id, supplier_name, supplier_phone, supplier_address) VALUES (:supplier_id, :supplier_name, :supplier_phone, :supplier_address)");
            $stmt->execute($data);
            $imported++;
        }

        $_SESSION['success'] = "$imported supplier(s) imported successfully!";
        header("Location: supplier.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error reading Excel file: " . $e->getMessage();
        header("Location: supplier.php");
        exit;
    }
}

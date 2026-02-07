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
    if (!empty($_FILES['excel_file']['name'])) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            // Load Excel file
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Validate headers
            $requiredHeaders = ['supplier_id','supplier_name','supplier_phone','supplier_address'];
            $headers = array_map('strtolower', $rows[0]); // first row
            $missing = array_diff($requiredHeaders, $headers);

            if (!empty($missing)) {
                die("Import failed! Missing columns: " . implode(', ', $missing));
            }

            // Map headers to index
            $headerMap = array_flip($headers);

            // Insert rows
            $imported = 0;
            foreach ($rows as $i => $row) {
                if ($i == 0) continue; // skip header

                $data = [
                    'supplier_id' => $row[$headerMap['supplier_id']] ?? '',
                    'supplier_name' => $row[$headerMap['supplier_name']] ?? '',
                    'supplier_phone' => $row[$headerMap['supplier_phone']] ?? '',
                    'supplier_address' => $row[$headerMap['supplier_address']] ?? ''
                ];

                // Skip if mandatory fields missing
                if (empty($data['supplier_id']) || empty($data['supplier_name']) || empty($data['supplier_phone'])) continue;

                // Optional: prevent duplicates by supplier_id
                $stmtCheck = $pdo->prepare("SELECT id FROM supplier WHERE supplier_id = :supplier_id");
                $stmtCheck->execute(['supplier_id' => $data['supplier_id']]);
                if ($stmtCheck->rowCount() > 0) continue;

                // Insert into DB
                $stmt = $pdo->prepare("INSERT INTO supplier (supplier_id, supplier_name, supplier_phone, supplier_address) VALUES (:supplier_id, :supplier_name, :supplier_phone, :supplier_address)");
                $stmt->execute($data);
                $imported++;
            }

            $_SESSION['success'] = "$imported suppliers imported successfully!";
            header("Location: supplier.php");
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error reading Excel file: ' . $e->getMessage());
        }

    } else {
        die("Please select an Excel file to import!");
    }
}

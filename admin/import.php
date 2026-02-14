<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../config/config.php';
require '../config/common.php';

// PhpSpreadsheet
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Get table name from URL
$table = $_GET['table'] ?? '';

// Whitelist config
$config = [
    'supplier' => [
        'headers' => ['supplier_id', 'supplier_name', 'supplier_phone', 'supplier_address'],
        'table'   => 'supplier',
        'unique'  => 'supplier_id'
    ],
    'category' => [
        'headers' => ['categories_code','categories_name'],
        'table'   => 'categories',
        'unique'  => 'categories_code'
    ],
    'customer' => [
        'headers' => ['customer_id','customer_name','customer_phone','customer_address'],
        'table'   => 'customer',
        'unique'  => 'customer_id'
    ],
    'item' => [
        'headers' => [
            'item_id',
            'item_name',
            'categories_id',
            'original_price',
            'selling_price',
            'reorder_level',
            'expiry_date',
            'location'
        ],
        'table'   => 'item',
        'unique'  => 'item_id'
    ]
];

// Validate table
if (!isset($config[$table])) {
    $_SESSION['error'] = "Invalid import table.";
    header("Location: index.php");
    exit;
}

// Must come from import form
if (!isset($_POST['import'])) {
    $_SESSION['error'] = "Invalid import request.";
    header("Location: " . $table . ".php");
    exit;
}

// Validate upload
if (empty($_FILES['excel_file']['name']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = $_FILES['excel_file']['error'] === UPLOAD_ERR_NO_FILE
        ? "Please select an Excel file to import!"
        : "File upload failed.";
    header("Location: " . $table . ".php");
    exit;
}

$fileTmpPath = $_FILES['excel_file']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (empty($rows)) {
        $_SESSION['error'] = "Excel file is empty.";
        header("Location: " . $table . ".php");
        exit;
    }

    // Normalize Excel headers
    $excelHeaders = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
    $excelMap = array_flip($excelHeaders);

    $dbHeaders = $config[$table]['headers'];
    $uniqueKey = $config[$table]['unique'];
    $tableName = $config[$table]['table'];

    $imported = 0;

    foreach ($rows as $i => $row) {
        if ($i === 0) continue; // skip header row

        $data = [];

        // Fill data from Excel OR NULL
        foreach ($dbHeaders as $col) {
            if (isset($excelMap[$col])) {
                $data[$col] = trim($row[$excelMap[$col]] ?? '');
                if ($data[$col] === '') $data[$col] = null;
            } else {
                $data[$col] = null;
            }
        }

        // Unique must exist
        if (empty($data[$uniqueKey])) continue;

        // Item-specific handling
        if ($table === 'item') {
            // Numeric fields default to 0
            $data['original_price'] = is_numeric($data['original_price']) ? $data['original_price'] : 0;
            $data['selling_price']  = is_numeric($data['selling_price'])  ? $data['selling_price']  : 0;
            $data['reorder_level']   = is_numeric($data['reorder_level']) ? $data['reorder_level'] : 0;

            // categories_id optional
            if (!empty($data['categories_id'])) {
                $stmtCat = $pdo->prepare("SELECT id FROM categories WHERE categories_code = :code");
                $stmtCat->execute(['code' => $data['categories_id']]);
                if ($stmtCat->rowCount() === 0) $data['categories_id'] = null;
            }

            // expiry_date optional format check
            if (!empty($data['expiry_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['expiry_date'])) {
                $data['expiry_date'] = null;
            }
        }

        // Duplicate check
        $stmtCheck = $pdo->prepare("SELECT id FROM {$tableName} WHERE {$uniqueKey} = :val");
        $stmtCheck->execute(['val' => $data[$uniqueKey]]);
        if ($stmtCheck->rowCount() > 0) continue;

        // Insert row safely
        try {
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(',:', array_keys($data));
            $stmt = $pdo->prepare("INSERT INTO {$tableName} ($columns) VALUES ($placeholders)");
            $stmt->execute($data);
            $imported++;
        } catch (Exception $e) {
            // Log row error, continue
            error_log("Row {$i} import failed: " . $e->getMessage());
            continue;
        }
    }

    $_SESSION['success'] = "$imported record(s) imported successfully!";
    header("Location: " . $table . ".php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error reading Excel file: " . $e->getMessage();
    header("Location: " . $table . ".php");
    exit;
}
?>

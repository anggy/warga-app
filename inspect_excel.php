<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/storage/app/public/Formulir Pendataan Warga Perumahan Sharia Islamic Soreang.xlsx';

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (empty($rows)) {
        echo "No data found in file.\n";
        exit;
    }

    // Get the first row (headers)
    $headers = $rows[0];
    
    echo "Headers found:\n";
    print_r($headers);

    if (isset($rows[1])) {
        echo "\nFirst Row Data:\n";
        print_r($rows[1]);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

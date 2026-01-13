<?php
$file = '.env';
$lines = file($file);
$cleanLines = [];
foreach ($lines as $line) {
    if (trim($line) !== '' && strpos($line, 'L5_SWAGGER_CONST_HOST') === false) {
        $cleanLines[] = trim($line) . PHP_EOL;
    }
}
// Re-add correctly
$cleanLines[] = "L5_SWAGGER_CONST_HOST=http://localhost:8001/api" . PHP_EOL;
file_put_contents($file, implode('', $cleanLines));
echo "Fixed .env";

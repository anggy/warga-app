<?php
$content = file_get_contents('.env');
// Clean up null bytes (common in UTF-16 LE -> UTF-8 conversion failures in bare php)
$content = str_replace("\0", "", $content);
// Clean up carriage returns if mixed
$content = str_replace("\r\n", "\n", $content);
// Ensure only one newline per line
$lines = explode("\n", $content);
$cleanLines = [];
foreach ($lines as $line) {
    if (trim($line) !== '') {
        $cleanLines[] = trim($line);
    }
}
file_put_contents('.env', implode(PHP_EOL, $cleanLines));
echo "Sanitized .env";

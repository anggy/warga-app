<?php
$lines = file('.env', FILE_IGNORE_NEW_LINES);
$newLines = [];
foreach ($lines as $line) {
    $trim = trim($line);
    if (empty($trim) || str_starts_with($trim, '#')) {
        $newLines[] = $line;
        continue;
    }
    
    // Simple validation: must have an = sign
    if (strpos($trim, '=') === false) {
        continue; // Skip invalid lines
    }

    $parts = explode('=', $trim, 2);
    $key = trim($parts[0]);
    $value = isset($parts[1]) ? trim($parts[1]) : '';

    // Check for obvious corruption in key (spaces, lowercases if strictly uppercased convention, but strict env parser allows lowercase)
    // The previous error mentioned L5_SWAGGER_CONST_HOST, so let's stick to standard chars
    if (preg_match('/[^a-zA-Z0-9_.]/', $key)) {
        continue; // Skip keys with weird characters
    }

    $newLines[] = "$key=$value";
}

file_put_contents('.env', implode(PHP_EOL, $newLines));
echo "Refined .env file.";

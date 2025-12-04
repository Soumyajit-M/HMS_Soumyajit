<?php
$sql = file_get_contents(__DIR__ . '/../database/schema_complete.sql');
$statements = explode(';', $sql);

echo "Processing schema statements...\n\n";

$processed = 0;
foreach ($statements as $i => $stmt) {
    // Remove comment lines
    $lines = explode("\n", $stmt);
    $sqlLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || preg_match('/^--/', $trimmed)) {
            continue;
        }
        $sqlLines[] = $line;
    }
    
    $clean = trim(implode("\n", $sqlLines));
    
    if ($clean === '') {
        continue;
    }
    
    $processed++;
    echo "Statement #$processed:\n";
    if (preg_match('/CREATE\s+(TABLE|INDEX)/i', $clean, $matches)) {
        echo "  Type: {$matches[1]}\n";
        if (preg_match('/CREATE\s+(?:TABLE|INDEX)\s+(?:IF NOT EXISTS\s+)?(\w+)/i', $clean, $nameMatch)) {
            echo "  Name: {$nameMatch[1]}\n";
        }
    } else if (preg_match('/INSERT/i', $clean)) {
        echo "  Type: INSERT\n";
    }
    echo "  Length: " . strlen($clean) . " bytes\n";
    echo "  Preview: " . substr($clean, 0, 80) . "...\n\n";
}

echo "\nTotal statements to process: $processed\n";

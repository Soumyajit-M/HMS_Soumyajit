<?php
$sql = file_get_contents(__DIR__ . '/../database/schema_complete.sql');
$stmts = explode(';', $sql);

echo "Searching for billing_item_tracking statements...\n\n";

foreach ($stmts as $i => $stmt) {
    if (stripos($stmt, 'billing_item_tracking') !== false && 
        (stripos($stmt, 'CREATE TABLE') !== false || stripos($stmt, 'CREATE INDEX') !== false)) {
        echo "Statement #$i:\n";
        echo substr(trim($stmt), 0, 150) . "...\n\n";
    }
}

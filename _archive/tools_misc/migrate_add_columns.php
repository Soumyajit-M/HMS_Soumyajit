<?php
// Migration: add missing created_at columns to doctors and payments if they don't exist
$sqliteFile = __DIR__ . '/../database/hms_database.sqlite';
if (!file_exists($sqliteFile)) {
    echo "ERROR: SQLite file not found: $sqliteFile\n";
    exit(2);
}
$pdo = new PDO('sqlite:' . $sqliteFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("PRAGMA table_info('" . $table . "')");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    return in_array($column, $cols);
}

$changes = [];
// doctors.created_at
if (!columnExists($pdo, 'doctors', 'created_at')) {
    $pdo->exec("ALTER TABLE doctors ADD COLUMN created_at TEXT");
    $pdo->exec("UPDATE doctors SET created_at = datetime('now') WHERE created_at IS NULL OR created_at = ''");
    $changes[] = "Added doctors.created_at (populated for existing rows)";
}
// payments.created_at
if (!columnExists($pdo, 'payments', 'created_at')) {
    $pdo->exec("ALTER TABLE payments ADD COLUMN created_at TEXT");
    $pdo->exec("UPDATE payments SET created_at = datetime('now') WHERE created_at IS NULL OR created_at = ''");
    $changes[] = "Added payments.created_at (populated for existing rows)";
}

if (empty($changes)) {
    echo "No changes needed.\n";
    exit(0);
}

foreach ($changes as $c) echo $c . "\n";
exit(0);

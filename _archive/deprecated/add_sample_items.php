<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Billing.php';

$billing = new Billing();

$items = [
    [
        'item_name' => 'Consultation',
        'description' => 'General medical consultation',
        'quantity' => 1,
        'unit_price' => 150.00,
        'total_price' => 150.00
    ],
    [
        'item_name' => 'Lab Test',
        'description' => 'Complete blood count',
        'quantity' => 1,
        'unit_price' => 75.00,
        'total_price' => 75.00
    ],
    [
        'item_name' => 'Medication',
        'description' => 'Prescribed medication',
        'quantity' => 2,
        'unit_price' => 25.00,
        'total_price' => 50.00
    ]
];

$billId = 12; // BILL202511298995

foreach ($items as $item) {
    $result = $billing->addBillingItem($billId, $item);
    if ($result) {
        echo "Added item: {$item['item_name']}\n";
    }
}

// Update the bill total
$database = new Database();
$conn = $database->getConnection();
$stmt = $conn->prepare("UPDATE billing SET total_amount = 275.00, balance_amount = 275.00 WHERE id = :id");
$stmt->bindValue(':id', $billId);
$stmt->execute();

echo "\nSuccessfully added 3 items to bill ID $billId\n";
echo "Updated total amount to $275.00\n";
echo "Now click the Print button again to see the full invoice.\n";
?>

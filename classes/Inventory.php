<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Validation.php';

class Inventory {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Categories
    public function getAllCategories() {
        try {
            $sql = "SELECT * FROM inventory_categories ORDER BY category_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Items
    public function createItem($data) {
        try {
            $validationErrors = [];

            if (empty($data['item_name'])) {
                $validationErrors[] = 'Item name is required';
            }
            if (empty($data['category_id'])) {
                $validationErrors[] = 'Category is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO inventory_items (item_name, category_id, item_code, description, unit_of_measure, reorder_level, storage_location) 
                    VALUES (:item_name, :category_id, :item_code, :description, :unit_of_measure, :reorder_level, :storage_location)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':item_name', $data['item_name']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $item_code = $data['item_code'] ?? null;
            $stmt->bindParam(':item_code', $item_code);
            $description = $data['description'] ?? null;
            $stmt->bindParam(':description', $description);
            $unit_of_measure = $data['unit_of_measure'] ?? 'Unit';
            $stmt->bindParam(':unit_of_measure', $unit_of_measure);
            $reorder_level = $data['reorder_level'] ?? 10;
            $stmt->bindParam(':reorder_level', $reorder_level);
            $storage_location = $data['storage_location'] ?? null;
            $stmt->bindParam(':storage_location', $storage_location);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Item created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create item'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllItems() {
        try {
            $sql = "SELECT ii.*, ic.category_name,
                    COALESCE(SUM(ib.quantity_remaining), 0) as total_quantity
                    FROM inventory_items ii
                    LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                    LEFT JOIN inventory_batches ib ON ii.id = ib.item_id AND ib.quantity_remaining > 0
                    GROUP BY ii.id
                    ORDER BY ii.item_name";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getItemById($id) {
        try {
            $sql = "SELECT ii.*, ic.category_name,
                    COALESCE(SUM(ib.quantity_remaining), 0) as total_quantity
                    FROM inventory_items ii
                    LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                    LEFT JOIN inventory_batches ib ON ii.id = ib.item_id AND ib.quantity_remaining > 0
                    WHERE ii.id = :id
                    GROUP BY ii.id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateItem($id, $data) {
        try {
            $sql = "UPDATE inventory_items SET 
                    item_name = :item_name,
                    category_id = :category_id,
                    item_code = :item_code,
                    description = :description,
                    unit_of_measure = :unit_of_measure,
                    reorder_level = :reorder_level,
                    storage_location = :storage_location
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':item_name', $data['item_name']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':item_code', $data['item_code']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':unit_of_measure', $data['unit_of_measure']);
            $stmt->bindParam(':reorder_level', $data['reorder_level']);
            $stmt->bindParam(':storage_location', $data['storage_location']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Item updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update item'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Batches
    public function createBatch($data) {
        try {
            $validationErrors = [];

            if (empty($data['item_id'])) {
                $validationErrors[] = 'Item ID is required';
            }
            if (empty($data['batch_number'])) {
                $validationErrors[] = 'Batch number is required';
            }
            if (empty($data['quantity_received'])) {
                $validationErrors[] = 'Quantity is required';
            }

            if (!empty($validationErrors)) {
                return ['success' => false, 'message' => implode(', ', $validationErrors)];
            }

            $sql = "INSERT INTO inventory_batches (item_id, batch_number, quantity_received, quantity_remaining, unit_cost, expiry_date, supplier_name, received_date) 
                    VALUES (:item_id, :batch_number, :quantity_received, :quantity_remaining, :unit_cost, :expiry_date, :supplier_name, :received_date)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':item_id', $data['item_id']);
            $stmt->bindParam(':batch_number', $data['batch_number']);
            $stmt->bindParam(':quantity_received', $data['quantity_received']);
            $quantity_remaining = $data['quantity_received'];
            $stmt->bindParam(':quantity_remaining', $quantity_remaining);
            $unit_cost = $data['unit_cost'] ?? null;
            $stmt->bindParam(':unit_cost', $unit_cost);
            $expiry_date = $data['expiry_date'] ?? null;
            $stmt->bindParam(':expiry_date', $expiry_date);
            $supplier_name = $data['supplier_name'] ?? null;
            $stmt->bindParam(':supplier_name', $supplier_name);
            $received_date = $data['received_date'] ?? date('Y-m-d');
            $stmt->bindParam(':received_date', $received_date);

            if ($stmt->execute()) {
                // Create a transaction record
                $this->createTransaction([
                    'item_id' => $data['item_id'],
                    'transaction_type' => 'IN',
                    'quantity' => $data['quantity_received'],
                    'batch_id' => $this->conn->lastInsertId(),
                    'reference_type' => 'Purchase',
                    'performed_by' => $data['performed_by'] ?? null
                ]);
                
                return ['success' => true, 'message' => 'Batch created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create batch'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getBatchesByItem($itemId) {
        try {
            $sql = "SELECT * FROM inventory_batches 
                    WHERE item_id = :item_id AND quantity_remaining > 0
                    ORDER BY expiry_date ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':item_id', $itemId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Transactions
    public function createTransaction($data) {
        try {
            $sql = "INSERT INTO inventory_transactions (item_id, transaction_type, quantity, batch_id, transaction_date, reference_type, reference_id, performed_by, notes) 
                    VALUES (:item_id, :transaction_type, :quantity, :batch_id, :transaction_date, :reference_type, :reference_id, :performed_by, :notes)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':item_id', $data['item_id']);
            $stmt->bindParam(':transaction_type', $data['transaction_type']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $batch_id = $data['batch_id'] ?? null;
            $stmt->bindParam(':batch_id', $batch_id);
            $transaction_date = $data['transaction_date'] ?? date('Y-m-d H:i:s');
            $stmt->bindParam(':transaction_date', $transaction_date);
            $reference_type = $data['reference_type'] ?? null;
            $stmt->bindParam(':reference_type', $reference_type);
            $reference_id = $data['reference_id'] ?? null;
            $stmt->bindParam(':reference_id', $reference_id);
            $performed_by = $data['performed_by'] ?? null;
            $stmt->bindParam(':performed_by', $performed_by);
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':notes', $notes);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Transaction recorded successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to record transaction'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function issueItem($itemId, $quantity, $data) {
        try {
            // Get available batches (FIFO)
            $batches = $this->getBatchesByItem($itemId);
            
            if (empty($batches)) {
                return ['success' => false, 'message' => 'No stock available'];
            }

            $remainingToIssue = $quantity;
            $this->conn->beginTransaction();

            try {
                foreach ($batches as $batch) {
                    if ($remainingToIssue <= 0) break;

                    $issueFromBatch = min($remainingToIssue, $batch['quantity_remaining']);
                    
                    // Update batch quantity
                    $sql = "UPDATE inventory_batches SET quantity_remaining = quantity_remaining - :quantity 
                            WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':quantity', $issueFromBatch);
                    $stmt->bindParam(':id', $batch['id']);
                    $stmt->execute();

                    // Record transaction
                    $this->createTransaction([
                        'item_id' => $itemId,
                        'transaction_type' => 'OUT',
                        'quantity' => $issueFromBatch,
                        'batch_id' => $batch['id'],
                        'reference_type' => $data['reference_type'] ?? 'Issue',
                        'reference_id' => $data['reference_id'] ?? null,
                        'performed_by' => $data['performed_by'] ?? null,
                        'notes' => $data['notes'] ?? null
                    ]);

                    $remainingToIssue -= $issueFromBatch;
                }

                if ($remainingToIssue > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Insufficient stock available'];
                }

                $this->conn->commit();
                return ['success' => true, 'message' => 'Item issued successfully'];
            } catch (Exception $e) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Failed to issue item: ' . $e->getMessage()];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getTransactionsByItem($itemId) {
        try {
            $sql = "SELECT it.*, ib.batch_number 
                    FROM inventory_transactions it
                    LEFT JOIN inventory_batches ib ON it.batch_id = ib.id
                    WHERE it.item_id = :item_id
                    ORDER BY it.transaction_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':item_id', $itemId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getLowStockItems() {
        try {
            $sql = "SELECT ii.*, ic.category_name,
                    COALESCE(SUM(ib.quantity_remaining), 0) as total_quantity
                    FROM inventory_items ii
                    LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                    LEFT JOIN inventory_batches ib ON ii.id = ib.item_id AND ib.quantity_remaining > 0
                    GROUP BY ii.id
                    HAVING total_quantity <= ii.reorder_level
                    ORDER BY total_quantity ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getExpiringItems($days = 30) {
        try {
            $sql = "SELECT ib.*, ii.item_name, ic.category_name
                    FROM inventory_batches ib
                    JOIN inventory_items ii ON ib.item_id = ii.id
                    LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                    WHERE ib.quantity_remaining > 0 
                    AND ib.expiry_date IS NOT NULL
                    AND ib.expiry_date <= DATE('now', '+{$days} days')
                    ORDER BY ib.expiry_date ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

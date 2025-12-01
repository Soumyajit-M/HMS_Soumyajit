-- Auto Billing System Schema Extensions

-- Add billing items table to store individual charges
CREATE TABLE IF NOT EXISTS billing_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bill_id INTEGER NOT NULL,
    item_type VARCHAR(50) NOT NULL, -- 'Bed Charge', 'Lab Test', 'Doctor Consultation', 'Medicine', 'Procedure'
    description TEXT,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity INTEGER DEFAULT 1,
    charge_type VARCHAR(20) DEFAULT 'one-time', -- 'one-time' or 'daily'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES billing (id) ON DELETE CASCADE
);

-- Add billing item tracking table to link items to their sources
CREATE TABLE IF NOT EXISTS billing_item_tracking (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bill_id INTEGER NOT NULL,
    billing_item_id INTEGER NOT NULL,
    item_type VARCHAR(50) NOT NULL, -- 'admission', 'lab_test', 'consultation', 'medicine'
    reference_id INTEGER, -- ID from source table (room_id, test_id, doctor_id, item_id)
    service_date TIMESTAMP,
    order_id INTEGER, -- Link to lab_orders or other order tables
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES billing (id) ON DELETE CASCADE,
    FOREIGN KEY (billing_item_id) REFERENCES billing_items (id) ON DELETE CASCADE
);

-- Add updated_at column to billing table if not exists
ALTER TABLE billing
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_billing_items_bill ON billing_items (bill_id);

CREATE INDEX IF NOT EXISTS idx_billing_tracking_bill ON billing_item_tracking (bill_id);

CREATE INDEX IF NOT EXISTS idx_billing_tracking_type ON billing_item_tracking (item_type, reference_id);

-- Sample data structure showing how it works:
/*
Example: Patient Admission Flow

1. Patient admitted to room
- Auto creates billing record
- Adds "Bed Charge" item (daily rate)
- Tracks in billing_item_tracking (admission, room_id)

2. Doctor consultation
- Adds "Doctor Consultation" item
- Tracks in billing_item_tracking (consultation, doctor_id, appointment_id)

3. Lab test ordered
- Adds "Lab Test" item for each test
- Tracks in billing_item_tracking (lab_test, test_id, order_id)

4. Medicine prescribed
- Adds "Medicine" item with quantity
- Tracks in billing_item_tracking (medicine, inventory_item_id)

5. Patient discharge
- Calculates total bed days
- Updates bed charge quantity
- Finalizes bill total
- Status: pending payment
*/
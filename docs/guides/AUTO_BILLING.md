# Auto-Billing System - User Guide

## 🎯 Overview

The Auto-Billing System automatically tracks and charges all patient services:

- **Bed/Room Charges** - Automatically calculated daily
- **Doctor Consultations** - Added when appointments are completed
- **Lab Tests** - Added when tests are ordered
- **Medicines** - Added when prescribed from inventory
- **Other Services** - Can be added manually

## 📋 Setup Instructions

### Step 1: Run Migration

```bash
php tools/migrate_auto_billing.php
```

This creates:

- `billing_items` table
- `billing_item_tracking` table
- Adds necessary columns to existing tables
- Inserts sample lab test data

### Step 2: Configure Prices

**Set Doctor Consultation Fees:**

```sql
UPDATE doctors SET consultation_fee = 1000 WHERE id = 1;
```

**Set Room Charges:**

```sql
UPDATE rooms SET charge_per_day = 1500 WHERE room_type = 'Private';
UPDATE rooms SET charge_per_day = 800 WHERE room_type = 'General';
UPDATE rooms SET charge_per_day = 3000 WHERE room_type = 'ICU';
```

**Set Medicine Prices:**

```sql
UPDATE inventory_items SET unit_price = 50.00 WHERE id = 1;
```

## 🔄 How It Works

### Automatic Billing Flow

```
Patient Admitted
    ↓
[Auto-Billing Creates Bill]
    ↓
Bed Charge Added (Daily Rate)
    ↓
Doctor Consultation → Consultation Fee Added
    ↓
Lab Test Ordered → Test Charges Added
    ↓
Medicine Prescribed → Medicine Cost Added
    ↓
Patient Discharged
    ↓
[Auto-Billing Finalizes Bill]
    ↓
Total Days Calculated
    ↓
Final Bill Generated
```

## 💻 PHP Usage Examples

### 1. Track Patient Admission

```php
require_once 'classes/AutoBilling.php';

$autoBilling = new AutoBilling();

// When patient is admitted to a room
$result = $autoBilling->trackAdmission(
    $patientId = 5,
    $roomId = 3,
    $admissionDate = '2025-12-01 10:00:00'
);

// Result: Bill created, bed charges added automatically
```

### 2. Track Lab Test (Already Integrated)

```php
// This happens automatically when you create lab order
$laboratory = new Laboratory();

$result = $laboratory->createOrder([
    'patient_id' => 5,
    'doctor_id' => 2,
    'tests' => [1, 2, 3], // Test IDs
    'priority' => 'Urgent'
]);

// Auto-billing tracks each test automatically
```

### 3. Track Consultation (Already Integrated)

```php
// This happens automatically when appointment is completed
$appointment = new Appointment();

$result = $appointment->updateAppointmentStatus($appointmentId, 'completed');

// Auto-billing adds consultation fee automatically
```

### 4. Track Medicine

```php
$autoBilling = new AutoBilling();

$result = $autoBilling->trackMedicine(
    $patientId = 5,
    $itemId = 10,  // Inventory item ID
    $quantity = 2
);
```

### 5. Calculate Bed Charges

```php
// Calculate total days and update bed charges
$result = $autoBilling->calculateBedCharges($billId);

echo "Patient stayed for: " . $result['days'] . " days";
```

### 6. Get Detailed Bill

```php
$result = $autoBilling->getDetailedBill($billId);

$bill = $result['bill'];
echo "Patient: " . $bill['first_name'] . " " . $bill['last_name'];
echo "Total Amount: ₹" . $bill['total_amount'];

foreach ($bill['items'] as $item) {
    echo $item['item_type'] . ": ₹" . ($item['unit_price'] * $item['quantity']);
}
```

### 7. Finalize Bill (Discharge)

```php
// When patient is discharged
$result = $autoBilling->finalizeBill($billId);

// This:
// - Calculates final bed charge days
// - Updates total amount
// - Returns complete bill details
```

## 🌐 API Usage Examples

### Track Admission

```javascript
fetch("api/auto-billing.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    action: "admission",
    patient_id: 5,
    room_id: 3,
    admission_date: "2025-12-01 10:00:00",
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

### Track Lab Test

```javascript
fetch("api/auto-billing.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    action: "lab_test",
    patient_id: 5,
    test_id: 2,
    order_id: 10,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

### Get Bill Details

```javascript
fetch("api/auto-billing.php?action=bill&bill_id=15")
  .then((response) => response.json())
  .then((data) => {
    const bill = data.bill;
    console.log("Total:", bill.total_amount);
    console.log("Items:", bill.items);
  });
```

### Finalize Bill

```javascript
fetch("api/auto-billing.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    action: "finalize",
    bill_id: 15,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log("Final Bill:", data.bill));
```

## 📊 Database Structure

### billing_items

```
id, bill_id, item_type, description, unit_price, quantity, charge_type
```

### billing_item_tracking

```
id, bill_id, billing_item_id, item_type, reference_id, service_date, order_id
```

### Example Billing Record

```
Bill #BILL-20251201-5-1733052000
├── Bed Charge (Private Room 101) - ₹1,500/day × 3 days = ₹4,500
├── Doctor Consultation (Dr. Smith - Cardiology) - ₹1,000
├── Lab Test (CBC) - ₹500
├── Lab Test (Blood Glucose) - ₹200
├── Medicine (Paracetamol 500mg) - ₹50 × 10 = ₹500
└── Total: ₹6,700
```

## 🎨 Frontend Integration Example

### Admission Form Handler

```javascript
document
  .getElementById("admitPatientForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    // 1. Assign room (existing functionality)
    await assignRoom(formData);

    // 2. Auto-track admission for billing
    await fetch("api/auto-billing.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "admission",
        patient_id: formData.get("patient_id"),
        room_id: formData.get("room_id"),
        admission_date: new Date().toISOString(),
      }),
    });

    alert("Patient admitted and billing started automatically!");
  });
```

### Discharge Handler

```javascript
async function dischargePatient(patientId, billId) {
  // 1. Finalize bill
  const billResponse = await fetch("api/auto-billing.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      action: "finalize",
      bill_id: billId,
    }),
  });

  const billData = await billResponse.json();

  // 2. Show bill to user
  displayBillDetails(billData.bill);

  // 3. Release room
  await releaseRoom(patientId);
}
```

## ⚙️ Configuration

### Charge Types

- **one-time**: Single charge (consultation, lab test)
- **daily**: Recurring daily charge (bed, equipment rental)

### Item Types

- `Bed Charge`
- `Doctor Consultation`
- `Lab Test`
- `Medicine`
- `Procedure`
- `Equipment`
- Custom types

## 🔍 Troubleshooting

**Bill not created automatically?**

- Check if patient has an active (pending) bill
- System reuses existing pending bills

**Bed charges not calculating?**

- Run `calculateBedCharges($billId)` before finalizing
- System auto-calculates on finalize

**Consultation not added?**

- Ensure appointment status is set to 'completed'
- Check doctor has consultation_fee set

**Lab tests not charged?**

- Verify test exists in lab_test_catalog
- Check test has standard_price set

## 📝 Best Practices

1. **Always finalize bill before discharge**
2. **Set all prices before going live**
3. **Run bed charge calculation daily for long-stay patients**
4. **Keep billing_item_tracking for audit trail**
5. **Use API for frontend operations**

## 🚀 Next Steps

- Integrate with rooms.php for admission tracking
- Add discharge button that finalizes bills
- Create bill preview modal
- Add billing reports
- Implement insurance claim integration

**Last Updated:** December 1, 2025

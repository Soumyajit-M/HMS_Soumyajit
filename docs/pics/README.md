# HMS Project Report - Diagrams Collection

This directory contains all professional SVG diagrams used in the Hospital Management System project report.

## 📊 Diagrams Included

### 1. Figure 3.1 - SDLC Diagram

**File**: `Figure_3.1_SDLC_Diagram.svg`

- **Type**: Waterfall Model
- **Description**: Shows the 6-phase Software Development Life Cycle
- **Phases**: Planning → Analysis → Design → Implementation → Testing → Deployment
- **Features**: Includes feedback loop showing iterative nature
- **Size**: 700×400px
- **Used in**: Chapter 3 - System Analysis (Page 11)

### 2. Figure 3.8 - Class Diagram

**File**: `Figure_3.8_Class_Diagram.svg`

- **Type**: UML Class Diagram
- **Description**: Object-oriented structure of the HMS
- **Classes Shown**:
  - Patient (with CRUD methods)
  - Doctor (with scheduling methods)
  - Appointment (with booking methods)
  - Billing (with payment methods)
  - Auth (authentication/authorization)
  - Inventory (stock management)
  - Database (abstract base class)
- **Relationships**: Dependency relationships to Database class
- **Size**: 750×480px
- **Used in**: Chapter 3 - System Analysis (Page 34)

### 3. Figure 3.9 - Activity Diagram

**File**: `Figure_3.9_Activity_Diagram.svg`

- **Type**: UML Activity Diagram
- **Description**: Workflow for patient appointment booking
- **Process Flow**:
  1. Receptionist login
  2. Check if patient exists (decision point)
  3. Add new patient OR continue
  4. Select doctor and date
  5. Check doctor availability (decision point)
  6. Create appointment OR select different time
- **Features**: Decision diamonds, parallel flows, start/end nodes
- **Size**: 600×480px
- **Used in**: Chapter 3 - System Analysis (Page 36)

### 4. Figure 3.10 - Sequence Diagram

**File**: `Figure_3.10_Sequence_Diagram.svg`

- **Type**: UML Sequence Diagram
- **Description**: Interaction sequence for appointment booking
- **Participants**:
  - Receptionist (Actor)
  - UI/Form (Presentation)
  - Appointment (Business Logic)
  - Doctor (Data Access)
  - Database (Persistence)
- **Messages**: 14 interactions showing synchronous calls and returns
- **Features**: Lifelines, activation boxes, return messages
- **Size**: 700×460px
- **Used in**: Chapter 3 - System Analysis (Page 38)

### 5. Figure 3.11 - ER Diagram

**File**: `Figure_3.11_ER_Diagram.svg`

- **Type**: Entity-Relationship Diagram
- **Description**: Complete database schema
- **Entities**:
  - USERS (authentication)
  - PATIENTS (patient records)
  - DOCTORS (doctor profiles)
  - APPOINTMENTS (scheduling)
  - BILLING (financial transactions)
  - INVENTORY (stock management)
  - DEPARTMENTS (organizational structure)
- **Relationships**:
  - Users ↔ Doctors (1:1)
  - Patients ↔ Appointments (1:N)
  - Doctors ↔ Appointments (1:N)
  - Patients ↔ Billing (1:N)
  - Departments ↔ Doctors (1:N)
- **Features**: Color-coded entities, primary/foreign key indicators
- **Size**: 750×480px
- **Used in**: Chapter 3 - System Analysis (Page 40)

### 6. Figure 3.12 - Use Case Diagram

**File**: `Figure_3.12_Use_Case_Diagram.svg`

- **Type**: UML Use Case Diagram
- **Description**: System functionality and user interactions
- **Actors**:
  - Admin (system administrator)
  - Doctor (medical staff)
  - Receptionist (front desk)
- **Use Cases**:
  - Manage Users
  - Manage Doctors
  - Manage Patients
  - Schedule Appointments
  - View Medical Records
  - Generate Bills
  - Manage Inventory
  - Generate Reports
  - Laboratory Tests
  - Telemedicine
- **Features**: System boundary, actor-use case associations
- **Size**: 750×480px
- **Used in**: Chapter 3 - System Analysis (Page 42)

## 📐 Technical Specifications

### Format

- **Type**: SVG (Scalable Vector Graphics)
- **Encoding**: UTF-8
- **XML Version**: 1.0

### Design Standards

- **Font**: Times New Roman (academic standard)
- **Font Sizes**:
  - Titles: 13-14pt bold
  - Labels: 10-11pt
  - Notes: 9pt italic
- **Color Scheme**:
  - Blue (#2196F3, #E3F2FD) - User/Admin related
  - Green (#4CAF50, #E8F5E9) - Patient/Medical related
  - Orange (#FF9800, #FFF3E0) - Appointments/Scheduling
  - Purple (#9C27B0, #F3E5F5) - Billing/Financial
  - Teal (#009688, #E0F2F1) - Inventory/Resources
  - Pink (#E91E63, #FCE4EC) - Doctor related
  - Yellow (#FBC02D, #FFF9C4) - Departments/Organization
- **Stroke Width**: 1-2px for shapes, 1.5px for associations
- **Markers**: Arrow markers for directed relationships

### Quality Features

- ✅ Vector graphics (infinite scalability)
- ✅ Print-ready (300 DPI equivalent)
- ✅ Web-compatible (all modern browsers)
- ✅ Accessible (semantic SVG elements)
- ✅ Professional appearance

## 🎨 Usage in Report

### HTML Integration

These diagrams are embedded in `Complete_Project_Report.html` using inline SVG:

```html
<div class="figure">
  <div class="figure-box">
    <svg width="..." height="..." xmlns="http://www.w3.org/2000/svg">
      <!-- Diagram content -->
    </svg>
  </div>
  <p class="caption">Figure X.X: Diagram Title</p>
</div>
```

### Standalone Usage

Each SVG file can be:

- Opened directly in web browsers
- Imported into Microsoft Word/PowerPoint
- Edited in vector graphics software (Inkscape, Adobe Illustrator)
- Converted to PNG/JPG for other formats
- Included in presentations

## 📁 File Naming Convention

Format: `Figure_X.Y_Description.svg`

- **Figure_X.Y**: Corresponds to figure number in report
- **Description**: Brief descriptor (SDLC, ER, Class, etc.)
- **Extension**: .svg (Scalable Vector Graphics)

## 🔧 Editing Diagrams

### Recommended Tools

1. **Inkscape** (Free, Open Source)
   - Download: https://inkscape.org/
   - Best for: Complex editing, adding elements
2. **Adobe Illustrator** (Commercial)
   - Best for: Professional editing, export to other formats
3. **Draw.io** (Free, Online)
   - URL: https://app.diagrams.net/
   - Best for: Quick edits, UML diagrams
4. **Text Editor** (VS Code, Notepad++)
   - Best for: Minor text changes, color adjustments

### Editing Tips

- Maintain Times New Roman font for consistency
- Keep color scheme consistent with original
- Preserve aspect ratios when resizing
- Test in browser after editing
- Validate SVG syntax

## 📤 Export Options

### To PNG (Raster Image)

```bash
# Using Inkscape command line
inkscape Figure_3.1_SDLC_Diagram.svg --export-png=output.png --export-dpi=300
```

### To PDF

```bash
# Using Inkscape
inkscape Figure_3.1_SDLC_Diagram.svg --export-pdf=output.pdf
```

### To Microsoft Word

1. Open SVG in browser
2. Right-click → Copy image
3. Paste into Word document
   OR
4. Insert → Pictures → Select SVG file
5. Word will embed as vector graphic

## 📊 Diagram Statistics

| Diagram       | Elements     | Entities/Objects        | Relationships       | Complexity |
| ------------- | ------------ | ----------------------- | ------------------- | ---------- |
| SDLC          | 6 phases     | 6                       | 5 arrows + feedback | Medium     |
| ER Diagram    | 7 entities   | 7 tables                | 5 relationships     | High       |
| Class Diagram | 7 classes    | 7                       | 3 dependencies      | High       |
| Activity      | 8 activities | 6 actions + 2 decisions | 11 flows            | Medium     |
| Sequence      | 5 objects    | 5 lifelines             | 14 messages         | High       |
| Use Case      | 10 use cases | 3 actors + 10 cases     | 13 associations     | Medium     |

## ✅ Quality Checklist

All diagrams meet the following criteria:

- [x] Professional appearance
- [x] Clear labeling and legends
- [x] Consistent color scheme
- [x] Academic font (Times New Roman)
- [x] Proper UML notation (where applicable)
- [x] High contrast for readability
- [x] Print-ready quality
- [x] Web-optimized file size
- [x] XML well-formed and valid
- [x] Accessible structure

## 📚 References

### UML Standards

- UML 2.5 Specification: https://www.omg.org/spec/UML/
- UML Notation Guide: https://www.uml-diagrams.org/

### SVG Standards

- W3C SVG Specification: https://www.w3.org/Graphics/SVG/
- SVG Tutorial: https://developer.mozilla.org/en-US/docs/Web/SVG

## 📞 Support

For questions or issues with diagrams:

- Check diagram in modern browser (Chrome, Firefox, Edge)
- Validate SVG syntax at: https://validator.w3.org/
- Open in Inkscape for detailed inspection
- Compare with original in report HTML

---

**Created**: December 5, 2025
**Format**: SVG (Scalable Vector Graphics)
**Total Diagrams**: 6
**Total Size**: ~50KB (all files combined)
**Quality**: Professional Academic Standard ⭐⭐⭐⭐⭐

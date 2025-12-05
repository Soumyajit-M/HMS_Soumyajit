# Project Report Export Guide

## Overview

The Hospital Management System project report is available in `docs/Complete_Project_Report.html` and formatted according to academic guidelines.

## Formatting Specifications

✅ **Paper Size:** A4 (210mm × 297mm)  
✅ **Font:** Times New Roman  
✅ **Font Sizes:**

- Body Text: 12pt
- Headings: 14pt
- Chapter Titles: 16pt

✅ **Margins:**

- Top: 1 inch (25.4mm)
- Bottom: 1 inch (25.4mm)
- Left: 1.5 inches (38.1mm)
- Right: 1 inch (25.4mm)

✅ **Line Spacing:** 1.5 lines

---

## Export Methods

### Method 1: Quick Export (Recommended)

Run the export script:

```powershell
.\Export-Report.ps1
```

This will:

1. Open the HTML report in your browser
2. Display step-by-step PDF export instructions
3. Show DOCX export options

### Method 2: Manual PDF Export

1. Open `docs/Complete_Project_Report.html` in **Chrome** or **Edge**
2. Press **Ctrl+P** (or File → Print)
3. Configure print settings:
   - **Destination:** Save as PDF / Microsoft Print to PDF
   - **Paper size:** A4
   - **Margins:** Custom
     - Top: 25.4mm (1 inch)
     - Bottom: 25.4mm (1 inch)
     - Left: 38.1mm (1.5 inches)
     - Right: 25.4mm (1 inch)
   - **Options:** Uncheck "Headers and footers"
4. Click **Save**
5. Save as: `docs/HMS_Project_Report.pdf`

### Method 3: Manual DOCX Export

**Option A: Using Microsoft Word**

1. Right-click `docs/Complete_Project_Report.html`
2. Select **Open with → Microsoft Word**
3. Go to **File → Save As**
4. Select **Word Document (.docx)**
5. Save as: `docs/HMS_Project_Report.docx`
6. Verify formatting:
   - Font: Times New Roman
   - Sizes: 12pt (body), 14pt (headings), 16pt (chapters)
   - Line spacing: 1.5
   - Margins: Top/Bottom/Right=1in, Left=1.5in

**Option B: Using Pandoc (Automatic)**

1. Install Pandoc:
   ```powershell
   winget install --id=JohnMacFarlane.Pandoc
   ```
2. Run conversion:
   ```powershell
   pandoc docs/Complete_Project_Report.html -o docs/HMS_Project_Report.docx --standalone
   ```

---

## Verification Checklist

After exporting, verify the following:

### PDF Checklist

- [ ] All pages are A4 size
- [ ] Margins are correct (Top/Bottom/Right=1in, Left=1.5in)
- [ ] Font is Times New Roman throughout
- [ ] Font sizes: 12pt (body), 14pt (headings), 16pt (chapters)
- [ ] Line spacing is 1.5
- [ ] Page numbers are centered at bottom
- [ ] No headers/footers except page numbers
- [ ] All chapters start on new pages
- [ ] Tables and figures are properly formatted
- [ ] Code blocks are readable

### DOCX Checklist

- [ ] All pages are A4 size
- [ ] Margins are correct (Top/Bottom/Right=1in, Left=1.5in)
- [ ] Font is Times New Roman throughout
- [ ] Font sizes are correct
- [ ] Line spacing is 1.5
- [ ] Page breaks are properly placed
- [ ] Tables maintain structure
- [ ] Images/figures are included
- [ ] Table of contents is functional

---

## Troubleshooting

### PDF Export Issues

**Problem:** Margins are incorrect

- **Solution:** Use Custom margins and manually enter exact values

**Problem:** Content is cut off

- **Solution:** Select "Fit to page" or reduce scaling to 100%

**Problem:** Page breaks in wrong places

- **Solution:** The HTML has `page-break-after: always` CSS - ensure browser respects this

### DOCX Export Issues

**Problem:** Formatting is lost

- **Solution:** Use Pandoc instead of direct Word import

**Problem:** Tables are broken

- **Solution:** Adjust column widths in Word after import

**Problem:** Code blocks are not monospaced

- **Solution:** Manually apply Courier New font to code sections

---

## Files Generated

After successful export, you should have:

- `docs/HMS_Project_Report.pdf` - PDF version (67 pages)
- `docs/HMS_Project_Report.docx` - Word version (67 pages)

---

## Support

For issues with report export:

1. Check browser console (F12) for errors
2. Try a different browser (Chrome recommended)
3. Ensure sufficient disk space
4. Check file permissions in docs/ folder

---

## Academic Compliance

This report format complies with standard academic guidelines:

- A4 paper size as specified
- Times New Roman font as required
- Proper margins (1" standard, 1.5" left for binding)
- 1.5 line spacing for readability
- Consistent heading hierarchy
- Professional formatting throughout

**The HTML source file already contains all correct formatting - no manual adjustments needed when exporting to PDF via browser print.**

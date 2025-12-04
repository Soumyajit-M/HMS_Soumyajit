# Hospital Management System - Report Export Tool
# PowerShell script to help export the project report

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Hospital Management System - Report Export Tool" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

$inputHtml = "docs\Complete_Project_Report.html"
$outputPdf = "docs\HMS_Project_Report.pdf"
$outputDocx = "docs\HMS_Project_Report.docx"

# Check if input file exists
if (-not (Test-Path $inputHtml)) {
    Write-Host "Error: Input file not found: $inputHtml" -ForegroundColor Red
    exit 1
}

Write-Host "Found input file: $inputHtml" -ForegroundColor Green

# Open HTML in browser for PDF export
Write-Host ""
Write-Host "Opening report in browser for PDF export..." -ForegroundColor Yellow
Start-Process (Resolve-Path $inputHtml)

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "PDF EXPORT INSTRUCTIONS" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The report has been opened in your default browser." -ForegroundColor White
Write-Host ""
Write-Host "To export as PDF:" -ForegroundColor Yellow
Write-Host "  1. Press Ctrl+P (or File > Print)" -ForegroundColor White
Write-Host "  2. Destination: Save as PDF / Microsoft Print to PDF" -ForegroundColor White
Write-Host "  3. Paper size: A4" -ForegroundColor White
Write-Host "  4. Margins: Custom" -ForegroundColor White
Write-Host "     - Top: 25.4mm (1 inch)" -ForegroundColor Gray
Write-Host "     - Bottom: 25.4mm (1 inch)" -ForegroundColor Gray
Write-Host "     - Left: 38.1mm (1.5 inches)" -ForegroundColor Gray
Write-Host "     - Right: 25.4mm (1 inch)" -ForegroundColor Gray
Write-Host "  5. Options: Uncheck 'Headers and footers'" -ForegroundColor White
Write-Host "  6. Click 'Save' and save as:" -ForegroundColor White
Write-Host "     $outputPdf" -ForegroundColor Gray
Write-Host ""

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "DOCX EXPORT INSTRUCTIONS" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "For DOCX format, install Pandoc:" -ForegroundColor Yellow
Write-Host "  1. Run: winget install --id=JohnMacFarlane.Pandoc" -ForegroundColor White
Write-Host "  2. Rerun this script" -ForegroundColor White
Write-Host ""
Write-Host "Or manually:" -ForegroundColor Yellow
Write-Host "  1. Open the HTML file in Microsoft Word" -ForegroundColor White
Write-Host "  2. File > Save As > Word Document (.docx)" -ForegroundColor White
Write-Host "  3. Adjust formatting:" -ForegroundColor White
Write-Host "     - Font: Times New Roman" -ForegroundColor Gray
Write-Host "     - Size: 12pt (body), 14pt (headings), 16pt (chapters)" -ForegroundColor Gray
Write-Host "     - Line spacing: 1.5" -ForegroundColor Gray
Write-Host "     - Margins: Top/Bottom/Right=1in, Left=1.5in" -ForegroundColor Gray
Write-Host ""

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "FORMATTING SPECIFICATIONS" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The HTML file already has correct formatting:" -ForegroundColor Green
Write-Host "  Paper Size: A4 (210mm x 297mm)" -ForegroundColor White
Write-Host "  Font: Times New Roman" -ForegroundColor White
Write-Host "  Font Sizes: 12pt (text), 14pt (headings), 16pt (chapters)" -ForegroundColor White
Write-Host "  Margins: Top/Bottom/Right=1in, Left=1.5in" -ForegroundColor White
Write-Host "  Line Spacing: 1.5 lines" -ForegroundColor White
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

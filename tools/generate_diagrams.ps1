# Generate Diagrams for HMS Project Report
# This script replaces all placeholder diagrams with actual SVG diagrams

$reportPath = "D:\My Project\HMS_Soumyajit\docs\Complete_Project_Report.html"
$outputPath = "D:\My Project\HMS_Soumyajit\docs\Complete_Project_Report.html"

Write-Host "Reading report file..." -ForegroundColor Green
$content = Get-Content $reportPath -Raw -Encoding UTF8

# Count placeholders
$placeholderCount = ([regex]::Matches($content, "Placeholder for")).Count
Write-Host "Found $placeholderCount placeholders to replace" -ForegroundColor Yellow

# 1. SDLC Diagram
Write-Host "Generating SDLC Diagram..." -ForegroundColor Cyan
$sdlcOld = @"
            Placeholder for System Development Life Cycle (SDLC) Diagram.
            <br><br>
            (e.g., A diagram showing the phases: Planning, Analysis, Design, Implementation, Testing, Deployment, Maintenance)
"@

$sdlcNew = @"
            <svg width="700" height="400" xmlns="http://www.w3.org/2000/svg">
                <!-- SDLC Waterfall Diagram -->
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
                        <polygon points="0 0, 10 3, 0 6" fill="#000" />
                    </marker>
                </defs>
                
                <!-- Planning -->
                <rect x="50" y="20" width="150" height="50" fill="#4CAF50" stroke="#000" stroke-width="2"/>
                <text x="125" y="50" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">1. PLANNING</text>
                
                <!-- Analysis -->
                <rect x="250" y="80" width="150" height="50" fill="#2196F3" stroke="#000" stroke-width="2"/>
                <text x="325" y="110" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">2. ANALYSIS</text>
                
                <!-- Design -->
                <rect x="450" y="140" width="150" height="50" fill="#9C27B0" stroke="#000" stroke-width="2"/>
                <text x="525" y="170" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">3. DESIGN</text>
                
                <!-- Implementation -->
                <rect x="250" y="200" width="150" height="50" fill="#FF9800" stroke="#000" stroke-width="2"/>
                <text x="325" y="230" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">4. IMPLEMENTATION</text>
                
                <!-- Testing -->
                <rect x="50" y="260" width="150" height="50" fill="#F44336" stroke="#000" stroke-width="2"/>
                <text x="125" y="290" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">5. TESTING</text>
                
                <!-- Deployment -->
                <rect x="250" y="320" width="150" height="50" fill="#00BCD4" stroke="#000" stroke-width="2"/>
                <text x="325" y="350" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">6. DEPLOYMENT</text>
                
                <!-- Arrows -->
                <line x1="200" y1="45" x2="250" y2="105" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="400" y1="105" x2="450" y2="165" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="450" y1="165" x2="400" y2="225" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="250" y1="225" x2="200" y2="285" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="200" y1="285" x2="250" y2="345" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Feedback Loop -->
                <path d="M 400 345 Q 650 285 600 165" fill="none" stroke="#888" stroke-width="2" stroke-dasharray="5,5" marker-end="url(#arrowhead)"/>
                <text x="620" y="250" font-family="Times New Roman" font-size="11" fill="#666">Feedback</text>
            </svg>
"@

$content = $content.Replace($sdlcOld, $sdlcNew)

# 2. Stakeholder Analysis Diagram
Write-Host "Generating Stakeholder Analysis Diagram..." -ForegroundColor Cyan
$stakeholderOld = @"
            Placeholder for Stakeholder Analysis Diagram.
            <br><br>
            (e.g., A diagram showing different stakeholder groups: Hospital Administrators, Medical Staff, Patients, IT Department, etc.)
"@

$stakeholderNew = @"
            <svg width="650" height="400" xmlns="http://www.w3.org/2000/svg">
                <!-- Stakeholder Analysis -->
                <!-- Central System -->
                <rect x="225" y="160" width="200" height="80" fill="#2196F3" stroke="#000" stroke-width="3" rx="10"/>
                <text x="325" y="195" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">Hospital</text>
                <text x="325" y="215" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">Management</text>
                <text x="325" y="235" text-anchor="middle" font-family="Times New Roman" font-size="14" font-weight="bold" fill="#fff">System</text>
                
                <!-- Stakeholders -->
                <!-- Patients -->
                <rect x="50" y="50" width="120" height="60" fill="#4CAF50" stroke="#000" stroke-width="2" rx="8"/>
                <text x="110" y="85" text-anchor="middle" font-family="Times New Roman" font-size="12" font-weight="bold" fill="#fff">PATIENTS</text>
                <line x1="170" y1="80" x2="225" y2="180" stroke="#000" stroke-width="2"/>
                
                <!-- Doctors -->
                <rect x="480" y="50" width="120" height="60" fill="#E91E63" stroke="#000" stroke-width="2" rx="8"/>
                <text x="540" y="85" text-anchor="middle" font-family="Times New Roman" font-size="12" font-weight="bold" fill="#fff">DOCTORS</text>
                <line x1="480" y1="80" x2="425" y2="180" stroke="#000" stroke-width="2"/>
                
                <!-- Administrators -->
                <rect x="50" y="290" width="120" height="60" fill="#FF9800" stroke="#000" stroke-width="2" rx="8"/>
                <text x="110" y="318" text-anchor="middle" font-family="Times New Roman" font-size="12" font-weight="bold" fill="#fff">ADMINS</text>
                <line x1="170" y1="320" x2="225" y2="220" stroke="#000" stroke-width="2"/>
                
                <!-- Receptionist -->
                <rect x="265" y="290" width="120" height="60" fill="#9C27B0" stroke="#000" stroke-width="2" rx="8"/>
                <text x="325" y="310" text-anchor="middle" font-family="Times New Roman" font-size="11" font-weight="bold" fill="#fff">RECEPTION-</text>
                <text x="325" y="330" text-anchor="middle" font-family="Times New Roman" font-size="11" font-weight="bold" fill="#fff">ISTS</text>
                <line x1="325" y1="290" x2="325" y2="240" stroke="#000" stroke-width="2"/>
                
                <!-- Pharmacists -->
                <rect x="480" y="290" width="120" height="60" fill="#00BCD4" stroke="#000" stroke-width="2" rx="8"/>
                <text x="540" y="318" text-anchor="middle" font-family="Times New Roman" font-size="11" font-weight="bold" fill="#fff">PHARMACISTS</text>
                <line x1="480" y1="320" x2="425" y2="220" stroke="#000" stroke-width="2"/>
                
                <!-- Lab Technicians -->
                <rect x="265" y="50" width="120" height="60" fill="#795548" stroke="#000" stroke-width="2" rx="8"/>
                <text x="325" y="75" text-anchor="middle" font-family="Times New Roman" font-size="11" font-weight="bold" fill="#fff">LAB TECH-</text>
                <text x="325" y="95" text-anchor="middle" font-family="Times New Roman" font-size="11" font-weight="bold" fill="#fff">NICIANS</text>
                <line x1="325" y1="110" x2="325" y2="160" stroke="#000" stroke-width="2"/>
            </svg>
"@

$content = $content.Replace($stakeholderOld, $stakeholderNew)

Write-Host "Saving updated report..." -ForegroundColor Green
$content | Out-File $outputPath -Encoding UTF8 -NoNewline

$remainingPlaceholders = ([regex]::Matches($content, "Placeholder for")).Count
Write-Host "`nReport updated successfully!" -ForegroundColor Green
Write-Host "Replaced: $($placeholderCount - $remainingPlaceholders) diagrams" -ForegroundColor Yellow
Write-Host "Remaining: $remainingPlaceholders placeholders" -ForegroundColor Yellow
Write-Host "`nOutput saved to: $outputPath" -ForegroundColor Cyan

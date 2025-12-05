"""
Generate All Diagrams for HMS Project Report
This script replaces all placeholder diagrams with actual SVG diagrams
"""

import re

# Read the report file
input_file = r"D:\My Project\HMS_Soumyajit\docs\Complete_Project_Report.html"
output_file = r"D:\My Project\HMS_Soumyajit\docs\Complete_Project_Report_FINAL.html"

print("Reading report file...")
with open(input_file, 'r', encoding='utf-8') as f:
    content = f.read()

# Count placeholders
placeholder_count = len(re.findall(r"Placeholder for", content))
print(f"Found {placeholder_count} placeholders to replace\n")

# Dictionary of all diagram replacements
diagrams = {}

# 1. SDLC Diagram
diagrams['SDLC'] = {
    'old': '''            Placeholder for System Development Life Cycle (SDLC) Diagram.
            <br><br>
            (e.g., A diagram showing the phases: Planning, Analysis, Design, Implementation, Testing, Deployment, Maintenance)''',
    'new': '''            <svg width="700" height="400" xmlns="http://www.w3.org/2000/svg">
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
            </svg>'''
}

# Apply all replacements
replaced_count = 0
for name, diagram in diagrams.items():
    if diagram['old'] in content:
        content = content.replace(diagram['old'], diagram['new'])
        replaced_count += 1
        print(f"✓ Replaced {name} diagram")
    else:
        print(f"✗ Could not find {name} diagram placeholder")

# Save the updated report
print(f"\nSaving updated report to {output_file}...")
with open(output_file, 'w', encoding='utf-8') as f:
    f.write(content)

# Final count
remaining = len(re.findall(r"Placeholder for", content))
print(f"\n✓ Report saved successfully!")
print(f"Replaced: {replaced_count} diagrams")
print(f"Remaining: {remaining} placeholders")
print(f"\nOutput: {output_file}")

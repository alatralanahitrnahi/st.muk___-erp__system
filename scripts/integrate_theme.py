#!/usr/bin/env python3
import re

dashboards = [
    'public/secure_super_admin.html',
    'public/secure_principal.html',
    'public/secure_admin.html',
    'public/secure_faculty.html',
    'public/secure_student.html'
]

for filepath in dashboards:
    with open(filepath, 'r') as f:
        content = f.read()
    
    # Add theme CSS in head if not present
    if 'department-theme.css' not in content:
        content = content.replace(
            '</head>',
            '    <link rel="stylesheet" href="/css/department-theme.css">\n</head>'
        )
    
    # Add theme JS before closing body if not present
    if 'theme.js' not in content:
        content = content.replace(
            '</body>',
            '    <script src="/js/theme.js"></script>\n</body>'
        )
    
    # Add skip link after body tag if not present
    if 'skip-link' not in content:
        content = content.replace(
            '<body>',
            '<body>\n    <a href="#main-content" class="skip-link">Skip to main content</a>'
        )
    
    # Add main landmark if not present
    if 'id="main-content"' not in content and 'class="main-content"' in content:
        content = content.replace(
            'class="main-content"',
            'id="main-content" class="main-content" role="main"'
        )
    
    # Add ARIA live region for announcements if not present
    if 'theme-announcement' not in content:
        content = content.replace(
            '<body>',
            '<body>\n    <div id="theme-announcement" class="sr-only" aria-live="polite" aria-atomic="true"></div>'
        )
    
    # Add lang attribute to html if not present
    if '<html>' in content:
        content = content.replace('<html>', '<html lang="en">')
    
    with open(filepath, 'w') as f:
        f.write(content)
    
    print(f'✅ Updated {filepath}')

print('\n✅ All dashboards updated with theme and WCAG enhancements!')

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
    
    # Add module-loader.js before closing body if not present
    if 'module-loader.js' not in content:
        content = content.replace(
            '<script src="/js/theme.js"></script>',
            '<script src="/js/module-loader.js"></script>\n    <script src="/js/theme.js"></script>'
        )
    
    # Ensure main-content has proper ID
    if 'id="main-content"' not in content and 'class="main-content"' in content:
        content = re.sub(
            r'<div class="main-content"',
            '<div id="main-content" class="main-content"',
            content,
            count=1
        )
    
    with open(filepath, 'w') as f:
        f.write(content)
    
    print(f'✅ Updated {filepath}')

print('\n✅ All dashboards updated with module loader!')

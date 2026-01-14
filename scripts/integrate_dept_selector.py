#!/usr/bin/env python3
import re

dashboards = {
    'public/secure_principal.html': 'principal',
    'public/secure_faculty.html': 'faculty',
    'public/secure_student.html': 'student',
    'public/secure_super_admin.html': 'super-admin'
}

dept_selector_html = '''
    <!-- Department Selector -->
    <div id="department-selector-container" style="padding: 0 2rem; background: white; border-bottom: 1px solid #e2e8f0;"></div>
'''

init_code = '''
            initDepartmentSelector();'''

for filepath, role in dashboards.items():
    try:
        with open(filepath, 'r') as f:
            content = f.read()
        
        # Add department selector container after header closing div, before container div
        if '<div id="department-selector-container"' not in content:
            content = re.sub(
                r'(</div>\s*<div class="container">)',
                f'{dept_selector_html}\\n\\1',
                content,
                count=1
            )
        
        # Add initDepartmentSelector() call in DOMContentLoaded
        if 'initDepartmentSelector()' not in content:
            content = re.sub(
                r"(document\.addEventListener\('DOMContentLoaded',\s*function\(\)\s*\{[^\}]*)",
                f'\\1{init_code}',
                content,
                count=1
            )
        
        with open(filepath, 'w') as f:
            f.write(content)
        
        print(f'✅ Integrated department selector into {filepath}')
    
    except Exception as e:
        print(f'❌ Error processing {filepath}: {e}')

print('\n✅ All dashboards updated!')

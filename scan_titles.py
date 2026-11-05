import os, re

base = r'C:\laragon\www\bankmini\resources\views'
for root, dirs, files in os.walk(base):
    for f in files:
        if f.endswith('.blade.php') and ('admin' in root or 'teller' in root):
            path = os.path.join(root, f)
            rel = os.path.relpath(path, base)
            with open(path, 'r', encoding='utf-8', errors='ignore') as fp:
                content = fp.read()
            
            p_title = re.findall(r"@section\(['\"]page-title['\"],\s*['\"](.*?)['\"]", content)
            h_title = re.findall(r"@section\(['\"]header_title['\"],\s*['\"](.*?)['\"]", content)
            t_title = re.findall(r"@section\(['\"]title['\"],\s*['\"](.*?)['\"]", content)
            
            print(f'{rel}:')
            print(f'   header_title: {h_title}')
            print(f'   page-title  : {p_title}')
            print(f'   title       : {t_title}')


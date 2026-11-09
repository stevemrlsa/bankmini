import os

for root, dirs, files in os.walk('app'):
    for file in files:
        if file.endswith('.php'):
            p = os.path.join(root, file)
            with open(p, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                if "'notes'" in content or '"notes"' in content:
                    lines = content.splitlines()
                    for i, l in enumerate(lines):
                        if 'notes' in l:
                            print(f"{p}:{i+1}: {l.strip()}")


import os

views_dir = r'c:\laragon\www\bankmini\resources\views'
for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith('.blade.php'):
            full_p = os.path.join(root, f)
            rel_p = os.path.relpath(full_p, views_dir)
            with open(full_p, 'r', encoding='utf-8', errors='ignore') as fp:
                lines = [l.strip() for l in fp.readlines()[:12]]
            if any("@extends('layouts.app')" in l or '@extends("layouts.app")' in l for l in lines):
                ht = [l for l in lines if 'header_title' in l]
                pt = [l for l in lines if 'page-title' in l]
                t = [l for l in lines if "'title'" in l or '"title"' in l]
                print(f"{rel_p:35} | HT: {ht}")


#!/bin/bash
# Deploy script for jenincare.shop
# Syncs git-tracked Laravel files into public_html/ and copies the APK

cd "$(dirname "$0")" || exit 1

echo "=== Pulling latest code ==="
git pull origin master

echo "=== Syncing tracked files to public_html/ ==="
for f in $(git ls-files | grep -v '^public_html/\|^SkinAnalyzer/\|^.git'); do
    mkdir -p "public_html/$(dirname "$f")" && cp "$f" "public_html/$f"
done

echo "=== Copying APK ==="
cp public/app-update.apk public_html/public/app-update.apk 2>/dev/null || true

echo "=== Clearing Laravel cache ==="
cd public_html
php artisan optimize:clear

echo "=== Done ==="

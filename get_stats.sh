#!/bin/bash
echo "--- Laravel Version ---"
php artisan --version
echo "--- Vue/Inertia Version ---"
grep '"vue"' package.json | head -1
grep '"@inertiajs/vue3"' package.json | head -1
echo "--- Tenant Package ---"
grep stancl composer.json
echo "--- Central Migrations ---"
ls -1 database/migrations/*.php 2>/dev/null | wc -l
echo "--- Tenant Migrations ---"
ls -1 database/migrations/tenant/*.php 2>/dev/null | wc -l
echo "--- Models ---"
find app/Models -name "*.php" -type f | wc -l
echo "--- Controllers ---"
find app/Http/Controllers -name "*.php" -type f | wc -l
echo "--- Service Classes ---"
find app/Services -name "*.php" -type f 2>/dev/null | wc -l
echo "--- Policies ---"
find app/Policies -name "*.php" -type f 2>/dev/null | wc -l
echo "--- Events ---"
find app/Events -name "*.php" -type f 2>/dev/null | wc -l
echo "--- Listeners ---"
find app/Listeners -name "*.php" -type f 2>/dev/null | wc -l
echo "--- Jobs ---"
find app/Jobs -name "*.php" -type f 2>/dev/null | wc -l
echo "--- Routes (files) ---"
find routes -name "*.php" -type f | wc -l
echo "--- Vue Pages ---"
find resources/js/Pages -name "*.vue" -type f 2>/dev/null | wc -l
echo "--- Vue Components ---"
find resources/js/Components -name "*.vue" -type f 2>/dev/null | wc -l

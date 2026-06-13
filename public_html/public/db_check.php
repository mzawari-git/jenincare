<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$texts = ['ابدئي', 'مستعدة', 'روتينك', 'انضمي', 'آلاف'];

// Check settings table
echo "=== settings table ===\n";
$rows = DB::table('settings')->get();
foreach ($rows as $row) {
    foreach ($texts as $t) {
        if (str_contains($row->value, $t)) {
            echo "key: {$row->key}\n";
            echo "value: {$row->value}\n\n";
        }
    }
}

// Check hero_slides
echo "=== hero_slides table ===\n";
$rows = DB::table('hero_slides')->get();
foreach ($rows as $row) {
    foreach (['title_ar', 'subtitle_ar', 'description_ar', 'button_text_ar', 'badge_text_ar'] as $col) {
        if ($row->$col) {
            foreach ($texts as $t) {
                if (str_contains($row->$col, $t)) {
                    echo "id: {$row->id}, col: $col\n";
                    echo "value: {$row->$col}\n\n";
                    break;
                }
            }
        }
    }
}

// Check categories
echo "=== categories table ===\n";
$rows = DB::table('categories')->get();
foreach ($rows as $row) {
    foreach (['name_ar', 'description_ar'] as $col) {
        if ($row->$col) {
            foreach ($texts as $t) {
                if (str_contains($row->$col, $t)) {
                    echo "id: {$row->id}, col: $col\n";
                    echo "value: {$row->$col}\n\n";
                    break;
                }
            }
        }
    }
}

// Check products
echo "=== products table ===\n";
$rows = DB::table('products')->get();
foreach ($rows as $row) {
    foreach (['name_ar', 'description_ar'] as $col) {
        if ($row->$col) {
            foreach ($texts as $t) {
                if (str_contains($row->$col, $t)) {
                    echo "id: {$row->id}, col: $col\n";
                    echo "value: {$row->$col}\n\n";
                    break;
                }
            }
        }
    }
}

// Full text search across all text columns
echo "=== RAW SEARCH ===\n";
$tables = DB::select("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND (DATA_TYPE LIKE '%text%' OR DATA_TYPE LIKE '%varchar%') AND (TABLE_NAME NOT LIKE 'migrations' AND TABLE_NAME NOT LIKE 'password_reset_tokens' AND TABLE_NAME NOT LIKE 'personal_access_tokens' AND TABLE_NAME NOT LIKE 'failed_jobs')", [env('DB_DATABASE')]);
foreach ($tables as $t) {
    $tableName = $t->TABLE_NAME;
    $colName = $t->COLUMN_NAME;
    try {
        $found = DB::select("SELECT id, $colName as val FROM $tableName WHERE $colName LIKE '%مستعدة%' LIMIT 1");
        if (!empty($found)) {
            echo "FOUND in: $tableName.$colName\n";
            foreach ($found as $f) {
                echo "  id={$f->id}, value=" . mb_substr($f->val, 0, 200) . "\n";
            }
        }
    } catch (\Exception $e) {
        // skip
    }
}

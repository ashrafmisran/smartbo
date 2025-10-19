<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking daftara table structure:\n";
echo "=================================\n\n";

try {
    $columns = DB::connection('ssdp')->select("DESCRIBE daftara");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nTotal daftara records: " . DB::connection('ssdp')->selectOne("SELECT COUNT(*) as count FROM daftara")->count . "\n";
    
    echo "\nSample daftara records:\n";
    $sample = DB::connection('ssdp')->select("SELECT * FROM daftara LIMIT 3");
    foreach ($sample as $record) {
        echo "Sample: ";
        foreach ((array)$record as $key => $value) {
            echo "{$key}='{$value}' ";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
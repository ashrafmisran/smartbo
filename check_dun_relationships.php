<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking relationship mappings\n";
echo "==============================\n\n";

// Check DUN record
echo "DUN record (REC_ID=344):\n";
$dun = DB::connection('ssdp')->select("SELECT * FROM dun WHERE REC_ID = 344")[0];
echo "Kod_Negeri: '{$dun->Kod_Negeri}', Kod_Parlimen: '{$dun->Kod_Parlimen}', Kod_DUN: '{$dun->Kod_DUN}'\n\n";

// Check Negeri table structure
echo "Negeri table structure:\n";
$negeri_columns = DB::connection('ssdp')->select("DESCRIBE negeri");
foreach ($negeri_columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

// Check for matching negeri
echo "\nLooking for negeri with Kod_Negeri = '{$dun->Kod_Negeri}':\n";
$negeri = DB::connection('ssdp')->select("SELECT * FROM negeri WHERE Kod_Negeri = '{$dun->Kod_Negeri}'");
if (count($negeri) > 0) {
    echo "Found: Kod_Negeri='{$negeri[0]->Kod_Negeri}', Nama_Negeri='{$negeri[0]->Nama_Negeri}'\n";
} else {
    echo "No match found. First 3 negeri records:\n";
    $all_negeri = DB::connection('ssdp')->select("SELECT * FROM negeri LIMIT 3");
    foreach ($all_negeri as $n) {
        echo "- Kod_Negeri: '{$n->Kod_Negeri}', Nama_Negeri: '{$n->Nama_Negeri}'\n";
    }
}

// Check Parlimen table structure
echo "\nParlimen table structure:\n";
$parlimen_columns = DB::connection('ssdp')->select("DESCRIBE parlimen");
foreach ($parlimen_columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

// Check for matching parlimen
echo "\nLooking for parlimen with Kod_Parlimen = '{$dun->Kod_Parlimen}':\n";
$parlimen = DB::connection('ssdp')->select("SELECT * FROM parlimen WHERE Kod_Parlimen = '{$dun->Kod_Parlimen}'");
if (count($parlimen) > 0) {
    echo "Found: Kod_Parlimen='{$parlimen[0]->Kod_Parlimen}', Nama_Parlimen='{$parlimen[0]->Nama_Parlimen}'\n";
} else {
    echo "No match found. First 3 parlimen records:\n";
    $all_parlimen = DB::connection('ssdp')->select("SELECT * FROM parlimen LIMIT 3");
    foreach ($all_parlimen as $p) {
        echo "- Kod_Parlimen: '{$p->Kod_Parlimen}', Nama_Parlimen: '{$p->Nama_Parlimen}'\n";
    }
}
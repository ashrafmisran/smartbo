<?php
require_once 'vendor/autoload.php';

use App\Models\Dun;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Detailed Dun Relationship Testing\n";
echo "==================================\n\n";

try {
    $dun = Dun::where('REC_ID', 344)->first();
    
    if ($dun) {
        echo "DUN Record:\n";
        echo "- REC_ID: {$dun->REC_ID}\n";
        echo "- Kod_Negeri: '{$dun->Kod_Negeri}'\n";
        echo "- Kod_Parlimen: '{$dun->Kod_Parlimen}'\n";
        echo "- Kod_DUN: '{$dun->Kod_DUN}'\n";
        echo "- Nama_DUN: '{$dun->Nama_DUN}'\n\n";
        
        echo "Testing negeri relationship:\n";
        $negeri = $dun->negeri;
        if ($negeri) {
            echo "- Found negeri: {$negeri->Nama_Negeri} (Kod: {$negeri->Kod_Negeri})\n";
        } else {
            echo "- Negeri relationship returned null\n";
            echo "- Trying direct query...\n";
            $directNegeri = \App\Models\Negeri::where('Kod_Negeri', $dun->Kod_Negeri)->first();
            if ($directNegeri) {
                echo "- Direct query found: {$directNegeri->Nama_Negeri}\n";
            } else {
                echo "- Direct query also returned null\n";
            }
        }
        
        echo "\nTesting parlimen relationship:\n";
        $parlimen = $dun->parlimen;
        if ($parlimen) {
            echo "- Found parlimen: {$parlimen->Nama_Parlimen} (Kod: {$parlimen->Kod_Parlimen})\n";
        } else {
            echo "- Parlimen relationship returned null\n";
            echo "- Trying direct query...\n";
            $directParlimen = \App\Models\Parlimen::where('Kod_Parlimen', $dun->Kod_Parlimen)->first();
            if ($directParlimen) {
                echo "- Direct query found: {$directParlimen->Nama_Parlimen}\n";
            } else {
                echo "- Direct query also returned null\n";
            }
        }
        
        echo "\nTesting accessors:\n";
        echo "- full_location: " . $dun->full_location . "\n";
        echo "- daerahs_count: " . $dun->daerahs_count . "\n";
        echo "- lokalitis_count: " . $dun->lokalitis_count . "\n";
        echo "- pengundis_count: " . $dun->pengundis_count . "\n";
        
    } else {
        echo "DUN record not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
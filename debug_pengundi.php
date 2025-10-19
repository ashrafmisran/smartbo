<?php
require_once 'vendor/autoload.php';

use App\Models\Dun;
use App\Models\Pengundi;
use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Debugging Pengundi Count for DUN BALUNG\n";
echo "=======================================\n\n";

try {
    $dun = Dun::where('REC_ID', 344)->first();
    
    if ($dun) {
        echo "DUN Record:\n";
        echo "- Kod_Negeri: '{$dun->Kod_Negeri}'\n";
        echo "- Kod_Parlimen: '{$dun->Kod_Parlimen}'\n";
        echo "- Kod_DUN: '{$dun->Kod_DUN}'\n\n";
        
        echo "Zero-padded values:\n";
        $padded_negeri = str_pad($dun->Kod_Negeri, 2, '0', STR_PAD_LEFT);
        $padded_parlimen = str_pad($dun->Kod_Parlimen, 3, '0', STR_PAD_LEFT);
        $padded_dun = str_pad($dun->Kod_DUN, 3, '0', STR_PAD_LEFT);
        
        echo "- Padded Kod_Negeri: '{$padded_negeri}'\n";
        echo "- Padded Kod_Parlimen: '{$padded_parlimen}'\n";
        echo "- Padded Kod_DUN: '{$padded_dun}'\n\n";
        
        echo "Checking pengundi/daftara table:\n";
        
        // Check total records in daftara
        $total = DB::connection('ssdp')->selectOne("SELECT COUNT(*) as total FROM daftara");
        echo "Total records in daftara: " . $total->total . "\n\n";
        
        // Check if there are any records matching the DUN codes
        echo "Looking for pengundi with exact match:\n";
        $exact_match = DB::connection('ssdp')->select("
            SELECT COUNT(*) as count FROM daftara 
            WHERE Kod_Negeri = '{$dun->Kod_Negeri}' 
            AND Kod_Parlimen = '{$dun->Kod_Parlimen}' 
            AND Kod_DUN = '{$dun->Kod_DUN}'
        ");
        echo "Exact match count: " . $exact_match[0]->count . "\n";
        
        echo "Looking for pengundi with zero-padded match:\n";
        $padded_match = DB::connection('ssdp')->select("
            SELECT COUNT(*) as count FROM daftara 
            WHERE Kod_Negeri = '{$padded_negeri}' 
            AND Kod_Parlimen = '{$padded_parlimen}' 
            AND Kod_DUN = '{$padded_dun}'
        ");
        echo "Padded match count: " . $padded_match[0]->count . "\n\n";
        
        // Show sample daftara records to understand the data format
        echo "Sample daftara records:\n";
        $samples = DB::connection('ssdp')->select("
            SELECT Kod_Negeri, Kod_Parlimen, Kod_DUN, Nama 
            FROM daftara 
            LIMIT 5
        ");
        foreach ($samples as $sample) {
            echo "- Negeri: '{$sample->Kod_Negeri}', Parlimen: '{$sample->Kod_Parlimen}', DUN: '{$sample->Kod_DUN}', Nama: '{$sample->Nama}'\n";
        }
        
        // Check if there are any records in this negeri/parlimen
        echo "\nRecords in same Negeri (12):\n";
        $negeri_count = DB::connection('ssdp')->selectOne("
            SELECT COUNT(*) as count FROM daftara WHERE Kod_Negeri = '12'
        ");
        echo "Count: " . $negeri_count->count . "\n";
        
        echo "\nRecords in same Parlimen (190):\n";
        $parlimen_count = DB::connection('ssdp')->selectOne("
            SELECT COUNT(*) as count FROM daftara WHERE Kod_Parlimen = '190'
        ");
        echo "Count: " . $parlimen_count->count . "\n";
        
    } else {
        echo "No DUN records found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
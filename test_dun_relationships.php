<?php
require_once 'vendor/autoload.php';

use App\Models\Dun;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Final Dun Model Relationships Test\n";
echo "===================================\n\n";

try {
    $dun = Dun::where('REC_ID', 344)->first();
    
    if ($dun) {
        echo "DUN: " . $dun->Nama_DUN . "\n";
        echo "Negeri: " . ($dun->negeri ? $dun->negeri->Nama_Negeri : 'No negeri') . "\n";
        echo "Parlimen: " . ($dun->parlimen ? $dun->parlimen->Nama_Parlimen : 'No parlimen') . "\n";
        echo "Full location: " . $dun->full_location . "\n";
        
        echo "\nTesting custom methods:\n";
        echo "getDaerahs() count: " . $dun->getDaerahs()->count() . "\n";
        echo "getLokalitis() count: " . $dun->getLokalitis()->count() . "\n";
        echo "getPengundis() count: " . $dun->getPengundis()->count() . "\n";
        
        echo "\nTesting accessors:\n";
        echo "daerahs_count: " . $dun->daerahs_count . "\n";
        echo "lokalitis_count: " . $dun->lokalitis_count . "\n";
        echo "pengundis_count: " . $dun->pengundis_count . "\n";
        
        echo "\nTesting scopes:\n";
        $byNegeri = Dun::byNegeri('12')->count();
        echo "DUNs in Negeri 12: " . $byNegeri . "\n";
        
        $byParlimen = Dun::byParlimen('190')->count();
        echo "DUNs in Parlimen 190: " . $byParlimen . "\n";
        
        echo "\nTesting withCounts scope:\n";
        $dunWithCounts = Dun::withCounts()->where('REC_ID', 344)->first();
        if ($dunWithCounts) {
            echo "DUN with counts: " . $dunWithCounts->Nama_DUN . "\n";
            echo "- daerahs_count: " . ($dunWithCounts->daerahs_count ?? 'N/A') . "\n";
            echo "- lokalitis_count: " . ($dunWithCounts->lokalitis_count ?? 'N/A') . "\n";
            echo "- pengundis_count: " . ($dunWithCounts->pengundis_count ?? 'N/A') . "\n";
        }
        
    } else {
        echo "No DUN records found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
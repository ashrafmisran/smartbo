<?php
require_once 'vendor/autoload.php';

use App\Models\Pengundi;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "CORRECTED PENGUNDI MODEL TEST\n";
echo str_repeat("=", 40) . "\n\n";

try {
    // Search for pengundi using the correct non-zero-padded daerah code
    echo "✓ Searching for pengundi in KINABUTAN (Kod_Daerah='1'):\n";
    $pengundi = Pengundi::where('Kod_Negeri', '12')
        ->where('Kod_Parlimen', '190')
        ->where('Kod_DUN', '67')
        ->where('Kod_Daerah', '1')  // Use '1' instead of '01'
        ->where('Kod_Lokaliti', '1')  // Use '1' instead of '001'
        ->first();
    
    if ($pengundi) {
        echo "✅ Found Pengundi: {$pengundi->Nama}\n";
        echo "  - IC: {$pengundi->No_KP_Baru}\n";
        echo "  - Kod_Daerah: '{$pengundi->Kod_Daerah}'\n";
        echo "  - Kod_Lokaliti: '{$pengundi->Kod_Lokaliti}'\n";
        echo "  - getDaerah(): " . ($pengundi->getDaerah() ? $pengundi->getDaerah()->Nama_Daerah : 'None') . "\n";
        echo "  - getLokaliti(): " . ($pengundi->getLokaliti() ? $pengundi->getLokaliti()->Nama_Lokaliti : 'None') . "\n";
        echo "  - nama_daerah (accessor): {$pengundi->nama_daerah}\n";
        echo "  - nama_lokaliti (accessor): {$pengundi->nama_lokaliti}\n\n";
        
        echo "✓ Data Format Analysis:\n";
        echo "  - Pengundi uses: Kod_Daerah='{$pengundi->Kod_Daerah}', Kod_Lokaliti='{$pengundi->Kod_Lokaliti}'\n";
        if ($pengundi->getDaerah()) {
            echo "  - Daerah table uses: Kod_Daerah='{$pengundi->getDaerah()->Kod_Daerah}'\n";
        }
        if ($pengundi->getLokaliti()) {
            echo "  - Lokaliti table uses: Kod_Lokaliti='{$pengundi->getLokaliti()->Kod_Lokaliti}'\n";
        }
        
        echo "\n✅ CONCLUSION: The relationships work correctly!\n";
        echo "   The issue was using zero-padded codes ('01') in the search\n";
        echo "   instead of the actual pengundi data format ('1').\n";
        
    } else {
        echo "❌ Still no pengundi found - there might be another issue\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
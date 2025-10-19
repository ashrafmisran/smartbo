<?php
require_once 'vendor/autoload.php';

use App\Models\Pengundi;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "PENGUNDI MODEL SPECIFIC TEST\n";
echo str_repeat("=", 40) . "\n\n";

try {
    // Find any pengundi in the KINABUTAN daerah
    echo "Looking for pengundi in KINABUTAN (Kod_Daerah=01):\n";
    $pengundi = Pengundi::where('Kod_Negeri', '12')
        ->where('Kod_Parlimen', '190')
        ->where('Kod_DUN', '67')
        ->where('Kod_Daerah', '01')
        ->first();
    
    if ($pengundi) {
        echo "✓ Found Pengundi: {$pengundi->Nama}\n";
        echo "  - IC: {$pengundi->No_KP_Baru}\n";
        echo "  - Kod_Lokaliti: {$pengundi->Kod_Lokaliti}\n";
        echo "  - getDaerah(): " . ($pengundi->getDaerah() ? $pengundi->getDaerah()->Nama_Daerah : 'None') . "\n";
        echo "  - getLokaliti(): " . ($pengundi->getLokaliti() ? $pengundi->getLokaliti()->Nama_Lokaliti : 'None') . "\n";
        echo "  - nama_daerah (accessor): {$pengundi->nama_daerah}\n";
        echo "  - nama_lokaliti (accessor): {$pengundi->nama_lokaliti}\n\n";
        
        // Test if the pengundi's lokaliti exists
        if ($pengundi->getLokaliti()) {
            $lokaliti = $pengundi->getLokaliti();
            echo "✓ Pengundi's Lokaliti Details:\n";
            echo "  - Nama: {$lokaliti->Nama_Lokaliti}\n";
            echo "  - Kod_Lokaliti: {$lokaliti->Kod_Lokaliti}\n";
            echo "  - Full Location: {$lokaliti->full_location}\n";
        }
    } else {
        echo "No pengundi found in KINABUTAN daerah\n";
        
        // Let's find any pengundi and test relationships
        echo "\nLooking for any pengundi in BALUNG DUN:\n";
        $any_pengundi = Pengundi::where('Kod_Negeri', '12')
            ->where('Kod_Parlimen', '190')
            ->where('Kod_DUN', '67')
            ->first();
            
        if ($any_pengundi) {
            echo "✓ Found Any Pengundi: {$any_pengundi->Nama}\n";
            echo "  - Kod_Daerah: {$any_pengundi->Kod_Daerah}\n";
            echo "  - Kod_Lokaliti: {$any_pengundi->Kod_Lokaliti}\n";
            echo "  - getDaerah(): " . ($any_pengundi->getDaerah() ? $any_pengundi->getDaerah()->Nama_Daerah : 'None') . "\n";
            echo "  - getLokaliti(): " . ($any_pengundi->getLokaliti() ? $any_pengundi->getLokaliti()->Nama_Lokaliti : 'None') . "\n";
            echo "  - nama_daerah (accessor): {$any_pengundi->nama_daerah}\n";
            echo "  - nama_lokaliti (accessor): {$any_pengundi->nama_lokaliti}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
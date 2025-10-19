<?php
require_once 'vendor/autoload.php';

use App\Models\Negeri;
use App\Models\Parlimen;
use App\Models\Dun;
use App\Models\Daerah;
use App\Models\Lokaliti;
use App\Models\Pengundi;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "COMPREHENSIVE ELECTORAL HIERARCHY RELATIONSHIP TEST\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. NEGERI MODEL TEST
    echo "1. NEGERI MODEL (SABAH)\n";
    echo str_repeat("-", 30) . "\n";
    $negeri = Negeri::where('Kod_Negeri', '12')->first();
    
    if ($negeri) {
        echo "✓ Negeri: {$negeri->Nama_Negeri}\n";
        echo "  - Parlimens: {$negeri->parlimens_count}\n";
        echo "  - DUNs: {$negeri->duns_count}\n";
        echo "  - Daerahs: {$negeri->daerahs_count}\n";
        echo "  - Lokalitis: {$negeri->lokalitis_count}\n";
        echo "  - Pengundis: {$negeri->pengundis_count}\n";
        echo "  - Full Location: {$negeri->full_location}\n\n";
    }

    // 2. PARLIMEN MODEL TEST
    echo "2. PARLIMEN MODEL (TAWAU)\n";
    echo str_repeat("-", 30) . "\n";
    $parlimen = Parlimen::where('Kod_Parlimen', '190')->first();
    
    if ($parlimen) {
        echo "✓ Parlimen: {$parlimen->Nama_Parlimen}\n";
        echo "  - Parent Negeri: " . ($parlimen->negeri ? $parlimen->negeri->Nama_Negeri : 'None') . "\n";
        echo "  - DUNs: {$parlimen->duns_count}\n";
        echo "  - Daerahs: {$parlimen->daerahs_count}\n";
        echo "  - Lokalitis: {$parlimen->lokalitis_count}\n";
        echo "  - Pengundis: {$parlimen->pengundis_count}\n";
        echo "  - Full Location: {$parlimen->full_location}\n\n";
    }

    // 3. DUN MODEL TEST  
    echo "3. DUN MODEL (BALUNG)\n";
    echo str_repeat("-", 30) . "\n";
    $dun = Dun::where('Kod_DUN', '67')->where('Kod_Parlimen', '190')->first();
    
    if ($dun) {
        echo "✓ DUN: {$dun->Nama_DUN}\n";
        echo "  - Parent Negeri: " . ($dun->negeri ? $dun->negeri->Nama_Negeri : 'None') . "\n";
        echo "  - Parent Parlimen: " . ($dun->parlimen ? $dun->parlimen->Nama_Parlimen : 'None') . "\n";
        echo "  - Daerahs: {$dun->daerahs_count}\n";
        echo "  - Lokalitis: {$dun->lokalitis_count}\n";
        echo "  - Pengundis: {$dun->pengundis_count}\n";
        echo "  - Full Location: {$dun->full_location}\n\n";
    }

    // 4. DAERAH MODEL TEST
    echo "4. DAERAH MODEL (KINABUTAN)\n";
    echo str_repeat("-", 30) . "\n";
    $daerah = Daerah::where('Kod_Daerah', '01')
        ->where('Kod_DUN', '67')
        ->where('Kod_Parlimen', '190')
        ->first();
    
    if ($daerah) {
        echo "✓ Daerah: {$daerah->Nama_Daerah}\n";
        echo "  - Parent Negeri: " . ($daerah->negeri ? $daerah->negeri->Nama_Negeri : 'None') . "\n";
        echo "  - Parent Parlimen: " . ($daerah->parlimen ? $daerah->parlimen->Nama_Parlimen : 'None') . "\n";
        echo "  - Parent DUN: " . ($daerah->dun ? $daerah->dun->Nama_DUN : 'None') . "\n";
        echo "  - Lokalitis: {$daerah->lokalitis_count}\n";
        echo "  - Pengundis: {$daerah->pengundi_count}\n";
        echo "  - Full Location: {$daerah->full_location}\n\n";
    }

    // 5. LOKALITI MODEL TEST
    echo "5. LOKALITI MODEL (KINABUTAN)\n";
    echo str_repeat("-", 30) . "\n";
    $lokaliti = Lokaliti::where('Kod_Lokaliti', '001')
        ->where('Kod_Daerah', '01')
        ->where('Kod_DUN', '67')
        ->first();
    
    if ($lokaliti) {
        echo "✓ Lokaliti: {$lokaliti->Nama_Lokaliti}\n";
        echo "  - Parent Negeri: " . ($lokaliti->negeri ? $lokaliti->negeri->Nama_Negeri : 'None') . "\n";
        echo "  - Parent Parlimen: " . ($lokaliti->parlimen ? $lokaliti->parlimen->Nama_Parlimen : 'None') . "\n";
        echo "  - Parent DUN: " . ($lokaliti->dun ? $lokaliti->dun->Nama_DUN : 'None') . "\n";
        echo "  - Parent Daerah: " . ($lokaliti->getDaerah() ? $lokaliti->getDaerah()->first()->Nama_Daerah : 'None') . "\n";
        echo "  - Pengundis: {$lokaliti->pengundi_count}\n";
        echo "  - Full Location: {$lokaliti->full_location}\n\n";
    }

    // 6. PENGUNDI MODEL TEST
    echo "6. PENGUNDI MODEL (Sample Voter)\n";
    echo str_repeat("-", 30) . "\n";
    $pengundi = Pengundi::where('Kod_Negeri', '12')
        ->where('Kod_Parlimen', '190')
        ->where('Kod_DUN', '67')
        ->where('Kod_Daerah', '01')
        ->where('Kod_Lokaliti', '001')
        ->first();
    
    if ($pengundi) {
        echo "✓ Pengundi: {$pengundi->Nama}\n";
        echo "  - Parent Daerah: " . ($pengundi->getDaerah() ? $pengundi->getDaerah()->Nama_Daerah : 'None') . "\n";
        echo "  - Parent Lokaliti: " . ($pengundi->getLokaliti() ? $pengundi->getLokaliti()->Nama_Lokaliti : 'None') . "\n";
        echo "  - Nama Daerah (accessor): {$pengundi->nama_daerah}\n";
        echo "  - Nama Lokaliti (accessor): {$pengundi->nama_lokaliti}\n\n";
    }

    // 7. HIERARCHY CONSISTENCY TEST
    echo "7. HIERARCHY CONSISTENCY CHECK\n";
    echo str_repeat("-", 30) . "\n";
    
    if ($negeri && $parlimen && $dun && $daerah && $lokaliti) {
        echo "✓ Checking upward hierarchy consistency:\n";
        
        // Check if lokaliti's parent daerah matches our daerah
        $lokaliti_daerah = $lokaliti->getDaerah()->first();
        echo "  - Lokaliti → Daerah: " . ($lokaliti_daerah && $lokaliti_daerah->REC_ID == $daerah->REC_ID ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if daerah's parent dun matches our dun  
        $daerah_dun = $daerah->dun;
        echo "  - Daerah → DUN: " . ($daerah_dun && $daerah_dun->REC_ID == $dun->REC_ID ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if dun's parent parlimen matches our parlimen
        $dun_parlimen = $dun->parlimen;
        echo "  - DUN → Parlimen: " . ($dun_parlimen && $dun_parlimen->REC_ID == $parlimen->REC_ID ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if parlimen's parent negeri matches our negeri
        $parlimen_negeri = $parlimen->negeri;
        echo "  - Parlimen → Negeri: " . ($parlimen_negeri && $parlimen_negeri->REC_ID == $negeri->REC_ID ? "✓ Match" : "✗ Mismatch") . "\n";
        
        echo "\n✓ Checking downward hierarchy consistency:\n";
        
        // Check if negeri contains our parlimen
        $negeri_has_parlimen = $negeri->parlimens()->where('REC_ID', $parlimen->REC_ID)->exists();
        echo "  - Negeri contains Parlimen: " . ($negeri_has_parlimen ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if parlimen contains our dun
        $parlimen_has_dun = $parlimen->getDuns()->where('REC_ID', $dun->REC_ID)->count() > 0;
        echo "  - Parlimen contains DUN: " . ($parlimen_has_dun ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if dun contains our daerah
        $dun_has_daerah = $dun->getDaerahs()->where('REC_ID', $daerah->REC_ID)->count() > 0;
        echo "  - DUN contains Daerah: " . ($dun_has_daerah ? "✓ Match" : "✗ Mismatch") . "\n";
        
        // Check if daerah contains our lokaliti
        $daerah_has_lokaliti = $daerah->getLokalitis()->where('REC_ID', $lokaliti->REC_ID)->count() > 0;
        echo "  - Daerah contains Lokaliti: " . ($daerah_has_lokaliti ? "✓ Match" : "✗ Mismatch") . "\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ COMPREHENSIVE RELATIONSHIP TEST COMPLETED\n";
    echo str_repeat("=", 60) . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
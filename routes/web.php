<?php

use Illuminate\Support\Facades\Route;
use App\Models\Dun;
use App\Models\Pengundi;

Route::get('/', function () {
    //return view('welcome');
    return redirect('/bo');
});

Route::get('/debug-pengundi', function () {
    try {
        $dun = Dun::first();
        if ($dun) {
            echo "DUN found: {$dun->Nama_DUN} (Kod: {$dun->Kod_DUN}, Type: " . gettype($dun->Kod_DUN) . ")<br>";
        }
        
        $pengundi = Pengundi::first();
        if ($pengundi) {
            echo "Pengundi found: {$pengundi->Nama} (Kod_DUN: {$pengundi->Kod_DUN}, Type: " . gettype($pengundi->Kod_DUN) . ")<br>";
        }
        
        // Test direct query
        $dunCode = $dun ? $dun->Kod_DUN : '01';
        $pengundis = Pengundi::where('Kod_DUN', $dunCode)->limit(3)->get();
        echo "<br>Direct query with DUN code '{$dunCode}' found " . $pengundis->count() . " pengundis:<br>";
        
        foreach ($pengundis as $p) {
            echo "- {$p->Nama} (DUN: {$p->Kod_DUN})<br>";
        }
        
        return response("Debug complete");
        
    } catch (Exception $e) {
        return response("Error: " . $e->getMessage());
    }
});

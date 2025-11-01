<?php

namespace Database\Seeders;

use App\Models\Kawasan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class KawasanCsvSeeder extends Seeder
{
    /**
     * Seed the kawasan table from resources/data/Senarai_Parlimen_Malaysia__Lengkap_.csv
     * CSV columns: id,name,negeri (where negeri is a numeric code; WP is 14 and split by id)
     */
    public function run(): void
    {
        $path = base_path('resources/data/Senarai_Parlimen_Malaysia__Lengkap_.csv');

        if (!File::exists($path)) {
            $this->command?->error('CSV not found at resources/data/Senarai_Parlimen_Malaysia__Lengkap_.csv');
            $this->command?->line('Place the file there with columns: id,name,negeri');
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->command?->error('Unable to open CSV file for reading.');
            return;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->command?->error('CSV is empty.');
            return;
        }

        // Normalize header
        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $header);

        $idIdx = array_search('id', $header, true);
        $nameIdx = array_search('name', $header, true);
        $negeriIdx = array_search('negeri', $header, true);

        if ($idIdx === false || $nameIdx === false || $negeriIdx === false) {
            fclose($handle);
            $this->command?->error('CSV must have headers: id,name,negeri');
            return;
        }

        $rows = [];
        $count = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 3) {
                continue;
            }

            $id = (int) trim((string) $data[$idIdx]);
            $name = trim((string) $data[$nameIdx]);
            $negeriCode = (int) trim((string) $data[$negeriIdx]);
            if ($id <= 0 || $name === '') {
                continue;
            }

            $negeri = $this->mapNegeriCodeToEnum($negeriCode, $id, $name);
            if ($negeri === null) {
                $this->command?->warn("Skipping id=$id name=$name due to unknown negeri code: $negeriCode");
                continue;
            }

            $rows[] = [
                'id' => $id,
                'name' => $name,
                'negeri' => $negeri,
            ];
            $count++;
        }
        fclose($handle);

        // Sort by id
        usort($rows, fn ($a, $b) => $a['id'] <=> $b['id']);

        // Upsert in chunks for safety
        foreach (array_chunk($rows, 500) as $chunk) {
            Kawasan::upsert($chunk, ['id'], ['name', 'negeri']);
        }

        $this->command?->info("Seeded kawasan from CSV: $count records");
    }

    private function mapNegeriCodeToEnum(int $code, int $id, string $name): ?string
    {
        switch ($code) {
            case 1: return 'Johor';
            case 2: return 'Kedah';
            case 3: return 'Kelantan';
            case 4: return 'Melaka';
            case 5: return 'Negeri Sembilan';
            case 6: return 'Pahang';
            case 7: return 'Perak';
            case 8: return 'Perlis';
            case 9: return 'Pulau Pinang';
            case 10: return 'Selangor';
            case 11: return 'Terengganu';
            case 12: return 'Sabah';
            case 13: return 'Sarawak';
            case 14: return 'Wilayah Persekutuan';
            default:
                return null;
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Kawasan;

class KawasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // We'll fetch the full list of Malaysian federal constituencies from Wikipedia
        // (List of Malaysian electoral districts) and parse P.xxx entries per state.
        // Then we seed kawasan with:
        // - id: numeric code (P.001 => 1)
        // - name: constituency name
        // - negeri: mapped to our enum (Pulau Pinang, Melaka, Wilayah Persekutuan, etc.)

        $stateSections = [
            // anchor id on the page => negeri enum value
            'Perlis' => 'Perlis',
            'Kedah' => 'Kedah',
            'Kelantan' => 'Kelantan',
            'Terengganu' => 'Terengganu',
            'Penang' => 'Pulau Pinang',
            'Perak' => 'Perak',
            'Pahang' => 'Pahang',
            'Selangor' => 'Selangor',
            'Federal_Territory_of_Kuala_Lumpur' => 'Wilayah Persekutuan',
            'Federal_Territory_of_Putrajaya' => 'Wilayah Persekutuan',
            'Negeri_Sembilan' => 'Negeri Sembilan',
            'Malacca' => 'Melaka',
            'Johor' => 'Johor',
            'Federal_Territory_of_Labuan' => 'Wilayah Persekutuan',
            'Sabah' => 'Sabah',
            'Sarawak' => 'Sarawak',
        ];

        // Optional local JSON override (for offline/deterministic seeding).
        // Provide an array of objects: [{"id":1,"name":"Padang Besar","negeri":"Perlis"}, ...]
        $localJson = base_path('resources/data/kawasan_parlimen.json');
        $entries = null;

        if (is_file($localJson)) {
            $json = @file_get_contents($localJson);
            if ($json !== false) {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $entries = $decoded;
                }
            }
        }

        $html = null;
        if ($entries === null) {
            $url = 'https://en.wikipedia.org/wiki/List_of_Malaysian_electoral_districts';
            $resp = Http::timeout(20)->get($url);
            if ($resp->ok()) {
                $html = $resp->body();
            } else {
                $this->command?->warn('KawasanSeeder: Failed to download Wikipedia page. Will fallback to SSDP.parlimen if available.');
            }
        }

        // Build an ordered list of section offsets to slice per-state segments.
        $offsets = [];
        foreach ($stateSections as $anchorId => $negeri) {
            $needle = 'id="' . $anchorId . '"';
            $pos = strpos($html, $needle);
            if ($pos !== false) {
                $offsets[] = [
                    'id' => $anchorId,
                    'negeri' => $negeri,
                    'pos' => $pos,
                ];
            }
        }

        if ($entries === null && empty($offsets)) {
            // Wikipedia failed; fallback to SSDP.parlimen for whatever is available
            $this->fallbackFromSSDP();
            return;
        }

        // Sort by position in the HTML to determine section boundaries
        usort($offsets, fn($a, $b) => $a['pos'] <=> $b['pos']);

        $now = now();
        $seen = [];
        $payload = [];

        if ($entries !== null) {
            // Use local JSON data directly
            foreach ($entries as $row) {
                $code = (int) ($row['id'] ?? 0);
                $name = isset($row['name']) ? trim((string)$row['name']) : '';
                $negeri = isset($row['negeri']) ? trim((string)$row['negeri']) : '';

                if ($code <= 0 || $name === '' || $negeri === '') {
                    continue;
                }

                if (isset($seen[$code])) {
                    continue;
                }
                $seen[$code] = true;

                $payload[] = [
                    'id' => $code,
                    'name' => $name,
                    'negeri' => $negeri,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        } else {
            foreach ($offsets as $i => $info) {
                $start = $info['pos'];
                $end = $offsets[$i + 1]['pos'] ?? strlen($html);
                $segment = substr($html, (int)$start, (int)($end - $start));

                // Extract all occurrences like: "P.001 Padang Besar" up to the next cell boundary (| or <)
                $matches = [];
                $count = preg_match_all('/P\.\s?(\d{3})\s+([^\|\<\n\r]+?)(?=\s*\||<)/u', $segment, $matches, PREG_SET_ORDER);

                if ($count === false || $count === 0) {
                    continue;
                }

                foreach ($matches as $m) {
                    $code = (int) ltrim($m[1], '0');
                    $name = trim(html_entity_decode($m[2]));

                    if ($code <= 0 || $name === '') {
                        continue;
                    }

                    // Deduplicate if the same P code somehow appears twice in the section
                    if (isset($seen[$code])) {
                        continue;
                    }
                    $seen[$code] = true;

                    $payload[] = [
                        'id' => $code,
                        'name' => $name,
                        'negeri' => $info['negeri'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (empty($payload)) {
            $this->command?->warn('KawasanSeeder: No constituencies parsed. Fallback to SSDP.parlimen as last resort.');
            $this->fallbackFromSSDP();
            return;
        }

        // Upsert by primary key 'id'
        Kawasan::query()->upsert($payload, ['id'], ['name', 'negeri', 'updated_at']);
    }

    private function fallbackFromSSDP(): void
    {
        // Map Kod_Negeri to the enum labels defined in the kawasan migration
        $negeriMap = [
            '1' => 'Johor', '01' => 'Johor',
            '2' => 'Kedah', '02' => 'Kedah',
            '3' => 'Kelantan', '03' => 'Kelantan',
            '4' => 'Melaka', '04' => 'Melaka',
            '5' => 'Negeri Sembilan', '05' => 'Negeri Sembilan',
            '6' => 'Pahang', '06' => 'Pahang',
            '7' => 'Perak', '07' => 'Perak',
            '8' => 'Perlis', '08' => 'Perlis',
            '9' => 'Pulau Pinang', '09' => 'Pulau Pinang',
            '10' => 'Selangor',
            '11' => 'Terengganu',
            '12' => 'Sabah',
            '13' => 'Sarawak',
            '14' => 'Wilayah Persekutuan',
        ];

        try {
            $rows = DB::connection('ssdp')
                ->table('parlimen')
                ->select(['Kod_Parlimen', 'Nama_Parlimen', 'Kod_Negeri'])
                ->orderBy('Kod_Parlimen')
                ->get();
        } catch (\Throwable $e) {
            $this->command?->warn('KawasanSeeder: SSDP fallback failed: ' . $e->getMessage());
            return;
        }

        $now = now();
        $payload = [];

        foreach ($rows as $r) {
            $id = (int) ltrim((string) $r->Kod_Parlimen, '0');
            if ($id <= 0) {
                $id = (int) $r->Kod_Parlimen;
            }

            $negeriKey = (string) $r->Kod_Negeri;
            $negeri = $negeriMap[$negeriKey] ?? $negeriMap[ltrim($negeriKey, '0')] ?? null;

            if (!$negeri) {
                continue;
            }

            $payload[] = [
                'id' => $id,
                'name' => (string) $r->Nama_Parlimen,
                'negeri' => $negeri,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($payload)) {
            Kawasan::query()->upsert($payload, ['id'], ['name', 'negeri', 'updated_at']);
        }
    }
}

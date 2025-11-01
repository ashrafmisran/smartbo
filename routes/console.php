<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// kawasan:extract-wikipedia
// Parse a saved Wikipedia content dump and produce a deterministic JSON for Kawasan seeding.
Artisan::command('kawasan:extract-wikipedia', function () {
    $inputPath = base_path('resources/data/wikipedia_malaysian_electoral_districts.txt');
    $outputPath = base_path('resources/data/kawasan_parlimen.json');

    if (!File::exists($inputPath)) {
        $this->error("Missing input file: resources/data/wikipedia_malaysian_electoral_districts.txt");
        $this->line('Download or copy the main content of the Wikipedia page "List of Malaysian electoral districts" into that file.');
        return 1;
    }

    $text = File::get($inputPath);
    $lines = preg_split("/\r?\n/", $text);

    // Map Wikipedia section headings to our enum values in the kawasan table
    $headingToEnum = [
        'Perlis' => 'Perlis',
        'Kedah' => 'Kedah',
        'Kelantan' => 'Kelantan',
        'Terengganu' => 'Terengganu',
        'Penang' => 'Pulau Pinang', // Wikipedia uses Penang, enum uses Pulau Pinang
        'Perak' => 'Perak',
        'Pahang' => 'Pahang',
        'Selangor' => 'Selangor',
        'Federal Territory of Kuala Lumpur' => 'Wilayah Persekutuan Kuala Lumpur',
        'Federal Territory of Putrajaya' => 'Wilayah Persekutuan Putrajaya',
        'Negeri Sembilan' => 'Negeri Sembilan',
        'Malacca' => 'Melaka', // Wikipedia uses Malacca, enum uses Melaka
        'Johor' => 'Johor',
        'Federal Territory of Labuan' => 'Wilayah Persekutuan Labuan',
        'Sabah' => 'Sabah',
        'Sarawak' => 'Sarawak',
    ];

    $currentHeading = null;
    $rows = [];
    $seen = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        // Detect section headings like "## Perlis" or "## Federal Territory of Kuala Lumpur"
        if (preg_match('/^##\s+(.+?)\s*$/', $line, $m)) {
            $heading = $m[1];
            // Normalize known variations
            if (isset($headingToEnum[$heading])) {
                $currentHeading = $heading;
                $this->line("Section: $heading → enum {$headingToEnum[$heading]}");
            } else {
                // Unknown heading; ignore but keep previous
            }
            continue;
        }

        // Match table row cells that begin a federal constituency entry: "| P.001 Padang Besar | ..."
        if (preg_match('/^\|\s*P\.(\d{3})\s+([^|\n]+?)\s*\|/u', $line, $m)) {
            if (!$currentHeading || !isset($headingToEnum[$currentHeading])) {
                // We must know the state context
                continue;
            }
            $id = (int) $m[1];
            $name = trim($m[2]);

            // Clean common HTML entities/whitespace
            $name = preg_replace('/\s+/u', ' ', $name);
            $name = trim($name, "\xC2\xA0 \t\r\n");

            // Some names appear with trailing references or footnote markers; strip [..]
            $name = preg_replace('/\[[^\]]*\]/u', '', $name);

            // De-duplicate if any
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;

            $rows[] = [
                'id' => $id,
                'name' => $name,
                'negeri' => $headingToEnum[$currentHeading],
            ];
        }
    }

    if (empty($rows)) {
        $this->error('No constituencies found. Ensure the input file contains the Wikipedia page main content with the P.xxx tables.');
        return 1;
    }

    // Sort by id numeric ascending
    usort($rows, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    File::ensureDirectoryExists(dirname($outputPath));
    File::put($outputPath, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $this->info("Wrote " . count($rows) . " constituencies to resources/data/kawasan_parlimen.json");
    $this->line('You can now run: php artisan db:seed --class=KawasanSeeder');
    return 0;
})->describe('Extract kawasan_parlimen.json from a saved Wikipedia content dump in resources/data');

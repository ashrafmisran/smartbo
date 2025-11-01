Kawasan (Parlimen) seed data

This project seeds the `kawasan` table (federal constituencies) using one of three sources, in priority order:

1) Local JSON override (recommended for offline/CI)
   - Create a file at `resources/data/kawasan_parlimen.json` containing an array of objects:
     [
       { "id": 1, "name": "Padang Besar", "negeri": "Perlis" },
       { "id": 2, "name": "Kangar", "negeri": "Perlis" },
       ...
     ]
   - See `resources/data/kawasan_parlimen.sample.json` for the exact shape.
   - `id` must be the official numeric P code (P.001 -> 1; P.160 -> 160), no prefix and no leading zeros.
   - `negeri` must be one of the enum values defined in the migration:
     Johor, Kedah, Kelantan, Melaka, Negeri Sembilan, Pahang, Perak, Perlis, Pulau Pinang, Selangor, Terengganu, Sabah, Sarawak, Wilayah Persekutuan

2) Wikipedia scrape (automatic)
   - Seeder fetches https://en.wikipedia.org/wiki/List_of_Malaysian_electoral_districts and parses P.xxx entries per state section.
   - If network access is blocked or the structure changes, this step is skipped automatically.

3) SSDP fallback (best-effort)
   - If (1) and (2) are unavailable, the seeder reads from `ssdp.parlimen` and upserts whatever rows exist.

Run:
- php artisan migrate --force
- php artisan db:seed --class=KawasanSeeder

Notes:
- For reliability in production/CI, prefer maintaining `resources/data/kawasan_parlimen.json` with the full list (222 entries). This avoids network flakiness and page format drift.

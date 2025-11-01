<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PengundiCountService
{
    /**
     * Per-request in-memory cache to avoid repeated Cache::get() on every row.
     */
    protected static ?array $memoryCache = null;

    /**
     * Cache key for grouped counts by (Kod_Negeri, Kod_Parlimen)
     */
    protected static function cacheKey(): string
    {
        $db = config('database.connections.ssdp.database');
        return "pengundis:counts:by_parlimen:" . $db;
    }

    /**
     * Return associative array: ["{Kod_Negeri}:{Kod_Parlimen}" => int count]
     */
    public static function getCountsByParlimen(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        $data = Cache::store('file')->remember(self::cacheKey(), now()->addDay(), function () {
            $rows = DB::connection('ssdp')
                ->table('daftara')
                ->select(['Kod_Negeri', 'Kod_Parlimen', DB::raw('COUNT(*) as c')])
                ->groupBy(['Kod_Negeri', 'Kod_Parlimen'])
                ->get();

            $out = [];
            foreach ($rows as $r) {
                $key = self::key($r->Kod_Negeri, $r->Kod_Parlimen);
                $out[$key] = (int) $r->c;
            }
            return $out;
        });

        // Store in memory for this request to prevent N+1 cache lookups
        self::$memoryCache = $data;
        return $data;
    }

    /**
     * Get count for one Parlimen, defaulting to 0 if not present.
     */
    public static function getCount(string $kodNegeri, string $kodParlimen): int
    {
        $all = self::getCountsByParlimen();
        $key = self::key($kodNegeri, $kodParlimen);
        return $all[$key] ?? 0;
    }

    protected static function key(string $kodNegeri, string $kodParlimen): string
    {
        return rtrim($kodNegeri) . ':' . rtrim($kodParlimen);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PengundiCountByLokalitiService
{
    protected static ?array $memoryCache = null;

    protected static function cacheKey(): string
    {
        $db = config('database.connections.ssdp.database');
        return "pengundis:counts:by_lokaliti:" . $db;
    }

    /**
     * Return associative array keyed by normalized codes
     * key format: N:{Kod_Negeri}|P:{Kod_Parlimen}|DUN:{Kod_DUN}|DR:{Kod_Daerah}|L:{Kod_Lokaliti}
     */
    public static function getCounts(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        $data = Cache::store('file')->remember(self::cacheKey(), now()->addDay(), function () {
            $rows = DB::connection('ssdp')
                ->table('daftara')
                ->select([
                    'Kod_Negeri', 'Kod_Parlimen', 'Kod_DUN', 'Kod_Daerah', 'Kod_Lokaliti',
                    DB::raw('COUNT(*) as c'),
                ])
                ->groupBy(['Kod_Negeri', 'Kod_Parlimen', 'Kod_DUN', 'Kod_Daerah', 'Kod_Lokaliti'])
                ->get();

            $out = [];
            foreach ($rows as $r) {
                $key = self::key($r->Kod_Negeri, $r->Kod_Parlimen, $r->Kod_DUN, $r->Kod_Daerah, $r->Kod_Lokaliti);
                $out[$key] = (int) $r->c;
            }
            return $out;
        });

        self::$memoryCache = $data;
        return $data;
    }

    public static function getCount(string $kodNegeri, string $kodParlimen, string $kodDun, string $kodDaerah, string $kodLokaliti): int
    {
        $all = self::getCounts();
        $key = self::key($kodNegeri, $kodParlimen, $kodDun, $kodDaerah, $kodLokaliti);
        return $all[$key] ?? 0;
    }

    protected static function norm(string $v): string
    {
        $n = ltrim($v, '0');
        return $n === '' ? '0' : $n;
    }

    protected static function key(string $kodNegeri, string $kodParlimen, string $kodDun, string $kodDaerah, string $kodLokaliti): string
    {
        return 'N:' . self::norm($kodNegeri)
            . '|P:' . self::norm($kodParlimen)
            . '|DUN:' . self::norm($kodDun)
            . '|DR:' . self::norm($kodDaerah)
            . '|L:' . self::norm($kodLokaliti);
    }
}

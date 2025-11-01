<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LokalitiCountByDaerahService
{
    protected static ?array $memoryCache = null;

    protected static function cacheKey(): string
    {
        $db = config('database.connections.ssdp.database');
        return "lokaliti:counts:by_daerah:" . $db;
    }

    /**
     * Return associative array keyed by normalized codes:
     * N:{Kod_Negeri}|P:{Kod_Parlimen}|DUN:{Kod_DUN}|DR:{Kod_Daerah}
     */
    public static function getCounts(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        $data = Cache::store('file')->remember(self::cacheKey(), now()->addDay(), function () {
            $rows = DB::connection('ssdp')
                ->table('lokaliti')
                ->select([
                    'Kod_Negeri', 'Kod_Parlimen', 'Kod_DUN', 'Kod_Daerah',
                    DB::raw('COUNT(*) as c'),
                ])
                ->groupBy(['Kod_Negeri', 'Kod_Parlimen', 'Kod_DUN', 'Kod_Daerah'])
                ->get();

            $out = [];
            foreach ($rows as $r) {
                $key = self::key($r->Kod_Negeri, $r->Kod_Parlimen, $r->Kod_DUN, $r->Kod_Daerah);
                $out[$key] = (int) $r->c;
            }
            return $out;
        });

        self::$memoryCache = $data;
        return $data;
    }

    public static function getCount(string $kodNegeri, string $kodParlimen, string $kodDun, string $kodDaerah): int
    {
        $all = self::getCounts();
        $key = self::key($kodNegeri, $kodParlimen, $kodDun, $kodDaerah);
        return $all[$key] ?? 0;
    }

    protected static function norm(string $v): string
    {
        $n = ltrim($v, '0');
        return $n === '' ? '0' : $n;
    }

    protected static function key(string $kodNegeri, string $kodParlimen, string $kodDun, string $kodDaerah): string
    {
        return 'N:' . self::norm($kodNegeri)
            . '|P:' . self::norm($kodParlimen)
            . '|DUN:' . self::norm($kodDun)
            . '|DR:' . self::norm($kodDaerah);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PengundiCountByDunService
{
    protected static ?array $memoryCache = null;

    protected static function cacheKey(): string
    {
        $db = config('database.connections.ssdp.database');
        return "pengundis:counts:by_dun:" . $db;
    }

    /**
     * Returns ["N:{n}|P:{p}|DUN:{d}" => c]
     */
    public static function getCounts(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        $data = Cache::store('file')->remember(self::cacheKey(), now()->addDay(), function () {
            $rows = DB::connection('ssdp')
                ->table('daftara')
                ->select(['Kod_Negeri','Kod_Parlimen','Kod_DUN', DB::raw('COUNT(*) as c')])
                ->groupBy(['Kod_Negeri','Kod_Parlimen','Kod_DUN'])
                ->get();

            $out = [];
            foreach ($rows as $r) {
                $key = self::key($r->Kod_Negeri, $r->Kod_Parlimen, $r->Kod_DUN);
                $out[$key] = (int) $r->c;
            }
            return $out;
        });

        self::$memoryCache = $data;
        return $data;
    }

    public static function getCount(string $kodNegeri, string $kodParlimen, string $kodDun): int
    {
        $all = self::getCounts();
        return $all[self::key($kodNegeri, $kodParlimen, $kodDun)] ?? 0;
    }

    protected static function norm(string $v): string
    {
        $n = ltrim($v, '0');
        return $n === '' ? '0' : $n;
    }

    protected static function key(string $kodNegeri, string $kodParlimen, string $kodDun): string
    {
        return 'N:' . self::norm($kodNegeri)
            . '|P:' . self::norm($kodParlimen)
            . '|DUN:' . self::norm($kodDun);
    }
}

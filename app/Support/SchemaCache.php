<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Cache untuk pemeriksaan metadata skema (hasTable / hasColumn).
 *
 * Setiap panggilan Schema::hasColumn()/hasTable() menjalankan query ke
 * information_schema. Di shared hosting (InfinityFree) query ini lambat
 * ~50-200ms, dan pada hot path (polling notifikasi, dashboard) dipanggil
 * belasan kali per request sehingga membebani CPU/DB.
 *
 * Hasilnya disimpan dua lapis: memori (per request) + cache store (antar
 * request, TTL 1 jam).
 *
 * Invalidasi memakai "generasi": flush() menaikkan nomor generasi sehingga
 * seluruh kunci lama otomatis dianggap basi, tanpa perlu knowing kunci mana
 * saja yang pernah dibuat.
 */
class SchemaCache
{
    /** TTL cache persisten (detik). */
    private const TTL = 3600;

    private const GEN_KEY = 'schema:gen';

    /** @var array<string, bool> */
    private static array $columns = [];

    /** @var array<string, bool> */
    private static array $tables = [];

    /** @var int|null */
    private static ?int $gen = null;

    /** @var bool|null - override untuk test (lihat bypassCacheForTesting) */
    private static ?bool $bypassOverride = null;

    public static function hasTable(string $table): bool
    {
        if (array_key_exists($table, self::$tables)) {
            return self::$tables[$table];
        }

        $store = self::store();
        if ($store !== null) {
            $cached = $store->get(self::key('tbl:' . $table));
            if (is_bool($cached)) {
                return self::$tables[$table] = $cached;
            }
        }

        $exists = Schema::hasTable($table);
        if ($store !== null) {
            $store->put(self::key('tbl:' . $table), $exists, self::TTL);
        }

        return self::$tables[$table] = $exists;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (array_key_exists($cacheKey, self::$columns)) {
            return self::$columns[$cacheKey];
        }

        $store = self::store();
        if ($store !== null) {
            $cached = $store->get(self::key('col:' . $cacheKey));
            if (is_bool($cached)) {
                return self::$columns[$cacheKey] = $cached;
            }
        }

        $exists = self::hasTable($table)
            && in_array($column, self::columnsOf($table), true);

        if ($store !== null) {
            $store->put(self::key('col:' . $cacheKey), $exists, self::TTL);
        }

        return self::$columns[$cacheKey] = $exists;
    }

    /**
     * Daftar kolom sebuah tabel; diambil dari cache store bila tersedia.
     */
    private static function columnsOf(string $table): array
    {
        $store = self::store();

        if ($store === null) {
            return Schema::getColumnListing($table);
        }

        $cached = $store->get(self::key('cols:' . $table));
        if (is_array($cached)) {
            return $cached;
        }

        $columns = Schema::getColumnListing($table);
        $store->put(self::key('cols:' . $table), $columns, self::TTL);

        return $columns;
    }

    /**
     * Buang seluruh cache skema (panggil setelah migrate / fresh install).
     *
     * Hanya menaikkan generasi; kunci lama membusuk sendiri lewat TTL dan
     * tidak perlu dihapus satu per satu (store bisa database/file/redis).
     */
    public static function flush(): void
    {
        self::$columns = [];
        self::$tables = [];
        self::$gen = null;

        $store = self::store();
        if ($store === null) {
            return;
        }

        try {
            $store->forever(self::GEN_KEY, self::generation() + 1);
        } catch (\Throwable $e) {
            // abaikan: cache hanya optimasi, kegagalan tidak boleh fatal
        }
    }

    private static function key(string $suffix): string
    {
        return 'schema:' . self::generation() . ':' . $suffix;
    }

    private static function generation(): int
    {
        if (self::$gen !== null) {
            return self::$gen;
        }

        $store = self::store();
        if ($store === null) {
            return self::$gen = 1;
        }

        try {
            $gen = $store->get(self::GEN_KEY);
            if (is_int($gen) || (is_string($gen) && ctype_digit($gen))) {
                return self::$gen = (int) $gen;
            }
            $store->forever(self::GEN_KEY, 1);
        } catch (\Throwable $e) {
            // abaikan
        }

        return self::$gen = 1;
    }

    /**
     * Cache store untuk metadata skema.
     *
     * Cache dilewati HANYA saat menjalankan perintah yang bisa mengubah skema
     * (migrate / db:seed / schema:*), agar pemeriksaan selalu melihat definisi
     * terbaru. Perintah lain memakai cache seperti request web biasa.
     */
    private static function store()
    {
        if (self::schemaMayBeChanging()) {
            return null;
        }

        try {
            return cache()->store();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function schemaMayBeChanging(): bool
    {
        if (self::$bypassOverride !== null) {
            return self::$bypassOverride;
        }

        if (!app()->runningInConsole()) {
            return false;
        }

        $command = $_SERVER['argv'][1] ?? '';

        return (bool) preg_match('/^(migrate|db:seed|schema:)/', $command);
    }

    /** Hanya untuk test: paksa bypass cache aktif (true) atau nonaktif (false/null). */
    public static function bypassCacheForTesting(?bool $bypass): void
    {
        self::$bypassOverride = $bypass;
        self::reset();
    }

    /** Hanya untuk test: bersihkan state in-memory tanpa mengubah store. */
    public static function reset(): void
    {
        self::$columns = [];
        self::$tables = [];
        self::$gen = null;
    }
}

<?php

use App\Support\TahunAjar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memisahkan kelas Santri menjadi dua: pengajian dan sekolah.
 *
 * Sebelumnya satu Santri hanya punya satu `kelas`, padahal di pesantren ada
 * dua jalur yang berjalan bersamaan:
 *   - kelas pengajian  (Kelas 1-6, Takhosus I/II) — tidak naik otomatis,
 *     dan tidak semua orangnya naik;
 *   - kelas sekolah    (1 SMP, Ibtida 3, 10 SMA)  — naik satu tingkat tiap tahun.
 *
 * Satu kolom teks tidak bisa menyimpan keduanya, jadi ditambah dua pasang kolom
 * (teks + foreign key ke kelas_master) sesuai jenisnya.
 *
 * Kolom lama `kelas` / `kelas_master_id` TIDAK dihapus: keduanya dipakai
 * sebagai cermin nilai yang paling sering dipakai (kelas sekolah; kalau kosong,
 * memakai kelas pengajian) supaya halaman lama, cetak, dan ekspor lama tetap
 * benar tanpa harus ditulis ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('santris')) {
            return;
        }

        if (!Schema::hasColumn('santris', 'kelas_pengajian')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->string('kelas_pengajian', 100)->nullable()->after('kelas');
            });
        }

        if (!Schema::hasColumn('santris', 'kelas_sekolah')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->string('kelas_sekolah', 100)->nullable()->after('kelas_pengajian');
            });
        }

        if (Schema::hasTable('kelas_master')) {
            if (!Schema::hasColumn('santris', 'kelas_pengajian_id')) {
                Schema::table('santris', function (Blueprint $table) {
                    $table->foreignId('kelas_pengajian_id')
                        ->nullable()
                        ->after('kelas_sekolah')
                        ->constrained('kelas_master')
                        ->nullOnDelete();
                });
            }

            if (!Schema::hasColumn('santris', 'kelas_sekolah_id')) {
                Schema::table('santris', function (Blueprint $table) {
                    $table->foreignId('kelas_sekolah_id')
                        ->nullable()
                        ->after('kelas_pengajian_id')
                        ->constrained('kelas_master')
                        ->nullOnDelete();
                });
            }
        }

        $this->backfill();
    }

    public function down(): void
    {
        if (!Schema::hasTable('santris')) {
            return;
        }

        Schema::table('santris', function (Blueprint $table) {
            foreach (['kelas_pengajian_id', 'kelas_sekolah_id'] as $col) {
                if (Schema::hasColumn('santris', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
        });

        Schema::table('santris', function (Blueprint $table) {
            foreach (['kelas_pengajian', 'kelas_sekolah'] as $col) {
                if (Schema::hasColumn('santris', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Isi kolom baru dari `kelas_master` yang sudah terhubung.
     *
     * Kelas yang/master-nya belum ada (Santri lama, atau kelas yang jenisnya
     * baru diisi admin setelah migrasi) dibaca dari namanya dengan tebakan yang
     * sama seperti di halaman /kelas, lalu dicoba dicocokkan ke master yang
     * namanya sama di tahun ajar berjalan.
     *
     * Hanya baris yang kolom barunya masih kosong yang ditulis, jadi migrasi
     * ini aman dijalankan ulang.
     */
    private function backfill(): void
    {
        if (!Schema::hasTable('santris')) {
            return;
        }

        $adaMaster = Schema::hasTable('kelas_master')
            && Schema::hasColumn('santris', 'kelas_master_id');

        $kolom = ['id', 'kelas'];
        if ($adaMaster) {
            $kolom[] = 'kelas_master_id';
        }
        foreach (['kelas_pengajian', 'kelas_sekolah'] as $col) {
            $kolom[] = $col;
        }
        foreach (['kelas_pengajian_id', 'kelas_sekolah_id'] as $col) {
            if (Schema::hasColumn('santris', $col)) {
                $kolom[] = $col;
            }
        }

        $santris = DB::table('santris')->select(array_unique($kolom))->get();

        $masterCache = [];
        if ($adaMaster) {
            foreach (DB::table('kelas_master')->select(['id', 'nama', 'jenis'])->get() as $m) {
                $masterCache[$m->id] = $m;
            }
        }

        $perTahun = [];
        if ($adaMaster) {
            foreach (DB::table('kelas_master')->select(['id', 'tahun_ajar', 'nama'])->get() as $m) {
                $perTahun[$m->tahun_ajar][mb_strtolower($m->nama)] = $m->id;
            }
        }

        $tahunAjar = TahunAjar::sekarang();

        $punyaKolomId = Schema::hasColumn('santris', 'kelas_pengajian_id')
            && Schema::hasColumn('santris', 'kelas_sekolah_id');

        foreach ($santris as $s) {
            $master = $adaMaster && $s->kelas_master_id
                ? ($masterCache[$s->kelas_master_id] ?? null)
                : null;

            if ($master) {
                // Jenis tersimpan di master yang menang; kalau kolomnya masih
                // kosong (baris lama sebelum migrasi `jenis`), tebak dari nama.
                $jenis = $master->jenis ?: TahunAjar::deteksiJenis((string) $master->nama);
                $nama = (string) $master->nama;
            } else {
                $nama = trim((string) $s->kelas);
                if ($nama === '' || $nama === '-') {
                    continue;
                }
                $jenis = TahunAjar::deteksiJenis($nama);
            }

            $pengajian = $jenis === TahunAjar::JENIS_PENGAJIAN ? $nama : null;
            $sekolah = $jenis === TahunAjar::JENIS_PENGAJIAN ? null : $nama;

            $perubahan = [];

            if ($pengajian !== null && trim((string) $s->kelas_pengajian) === '') {
                $perubahan['kelas_pengajian'] = $pengajian;
                if ($punyaKolomId && ! $s->kelas_pengajian_id) {
                    $id = $s->kelas_master_id ?: ($perTahun[$tahunAjar][mb_strtolower($pengajian)] ?? null);
                    if ($id) {
                        $perubahan['kelas_pengajian_id'] = $id;
                    }
                }
            }

            if ($sekolah !== null && trim((string) $s->kelas_sekolah) === '') {
                $perubahan['kelas_sekolah'] = $sekolah;
                if ($punyaKolomId && ! $s->kelas_sekolah_id) {
                    $id = $s->kelas_master_id ?: ($perTahun[$tahunAjar][mb_strtolower($sekolah)] ?? null);
                    if ($id) {
                        $perubahan['kelas_sekolah_id'] = $id;
                    }
                }
            }

            if ($perubahan) {
                DB::table('santris')->where('id', $s->id)->update($perubahan);
            }
        }
    }
};

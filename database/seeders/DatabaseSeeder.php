<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kamar;
use App\Models\Santri;
use App\Models\PresensiSholat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users
        User::updateOrCreate(
            ['email' => 'admin@sijamaah.id'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => 'password',
        ]);

        // Seed Kamars
        $kamars = [
            ['nama_kamar' => 'Kamar A', 'deskripsi' => 'Asrama Santri Putra A'],
            ['nama_kamar' => 'Kamar B', 'deskripsi' => 'Asrama Santri Putra B'],
            ['nama_kamar' => 'Kamar C', 'deskripsi' => 'Asrama Santri Putri A'],
        ];

        foreach ($kamars as $kamar) {
            Kamar::firstOrCreate(['nama_kamar' => $kamar['nama_kamar']], $kamar);
        }

        // Seed Santris
        $santriNames = [
            ['nama' => 'Ahmad Fadli', 'nis' => '001'],
            ['nama' => 'Muhammad Rizki', 'nis' => '002'],
            ['nama' => 'Budi Santoso', 'nis' => '003'],
            ['nama' => 'Hendra Kusuma', 'nis' => '004'],
            ['nama' => 'Rani Wijaya', 'nis' => '005'],
        ];

        $kamarA = Kamar::first();
        
        foreach ($santriNames as $santri) {
            $payload = [
                'nama' => $santri['nama'],
                'kamar_id' => $kamarA->id,
                'kelas' => 'Kelas 1 Aliyah',
            ];

            if (Schema::hasColumn('santris', 'no_hp_wali')) {
                $payload['no_hp_wali'] = '08123456789';
            }

            if (Schema::hasColumn('santris', 'status')) {
                $payload['status'] = 'Aktif';
            }

            Santri::updateOrCreate(['nis' => $santri['nis']], $payload);
        }

        // Seed sample PresensiSholat data (untuk rating calculation)
        $santris = Santri::all();
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        foreach ($santris as $santri) {
            foreach ($dates as $date) {
                foreach (['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $waktu) {
                    $statuses = ['Jamaah', 'Jamaah', 'Jamaah', 'Masbuq', 'Izin']; // Weighted toward Jamaah
                    $status = $statuses[array_rand($statuses)];

                    PresensiSholat::updateOrCreate(
                        ['santri_id' => $santri->id, 'tanggal' => $date, 'waktu_sholat' => $waktu],
                        ['status' => $status, 'catatan' => null]
                    );
                }
            }
        }
    }
}

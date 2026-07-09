<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // standard default password
        ]);

        $paketData = [
            ['nama_paket' => 'Paket Hemat - 7Mbps', 'harga' => 100000.00, 'deskripsi' => 'Kecepatan 7 Mbps'],
            ['nama_paket' => 'Paket Mulus - 12Mbps', 'harga' => 115000.00, 'deskripsi' => 'Kecepatan 12 Mbps'],
            ['nama_paket' => 'Paket Santuy - 15Mbps', 'harga' => 125000.00, 'deskripsi' => 'Kecepatan 15 Mbps'],
            ['nama_paket' => 'Paket Seru - 25Mbps', 'harga' => 155000.00, 'deskripsi' => 'Kecepatan 25 Mbps'],
            ['nama_paket' => 'Paket Ngebut - 35Mbps', 'harga' => 220000.00, 'deskripsi' => 'Kecepatan 35 Mbps'],
            ['nama_paket' => 'Paket Gasspoll - 50Mbps', 'harga' => 250000.00, 'deskripsi' => 'Kecepatan 50 Mbps'],
            ['nama_paket' => 'Paket Sultan - 100Mbps', 'harga' => 350000.00, 'deskripsi' => 'Kecepatan 100 Mbps'],
        ];

        foreach ($paketData as $paket) {
            \App\Models\PaketHarga::create($paket);
        }
    }
}

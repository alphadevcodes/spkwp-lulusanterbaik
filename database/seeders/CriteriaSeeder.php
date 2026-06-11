<?php

namespace Database\Seeders;
use App\Models\Criteria;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        Criteria::insert([
            [
                'code' => 'C1',
                'name' => 'Rata-Rata Rapor',
                'category' => 'utama',
                'attribute' => 'benefit',
                'weight' => 0.25,
            ],
            [
                'code' => 'C2',
                'name' => 'Rata-Rata Ujian Sekolah',
                'category' => 'utama',
                'attribute' => 'benefit',
                'weight' => 0.30,
            ],
            [
                'code' => 'C3',
                'name' => 'Keaktifan Ekskul',
                'category' => 'utama',
                'attribute' => 'benefit',
                'weight' => 0.15,
            ],
            [
                'code' => 'C4',
                'name' => 'Prestasi',
                'category' => 'tambahan',
                'attribute' => 'benefit',
                'weight' => 0.15,
            ],
            [
                'code' => 'C5',
                'name' => 'Skor Kredit Prestasi',
                'category' => 'tambahan',
                'attribute' => 'benefit',
                'weight' => 0.10,
            ],
            [
                'code' => 'C6',
                'name' => 'Pelanggaran',
                'category' => 'tambahan',
                'attribute' => 'cost',
                'weight' => 0.05,
            ],
        ]);
    }
}
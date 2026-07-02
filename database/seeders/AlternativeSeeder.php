<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\AlternativeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlternativeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $students = [
                ['A4', 'Siswa 4'],
                ['A5', 'Siswa 5'],
                ['A6', 'Siswa 6'],
                ['A7', 'Siswa 7'],
                ['A8', 'Siswa 8'],
                ['A9', 'Siswa 9'],
                ['A10', 'Siswa 10'],
                ['A11', 'Siswa 11'],
                ['A12', 'Siswa 12'],
                ['A13', 'Siswa 13'],
                ['A14', 'Siswa 14'],
                ['A15', 'Siswa 15'],
                ['A16', 'Siswa 16'],
                ['A17', 'Siswa 17'],
                ['A18', 'Siswa 18'],
                ['A19', 'Siswa 19'],
                ['A20', 'Siswa 20'],
                ['A21', 'Siswa 21'],
                ['A22', 'Siswa 22'],
                ['A23', 'Siswa 23'],
                ['A24', 'Siswa 24'],
                ['A25', 'Siswa 25'],
                ['A26', 'Siswa 26'],
            ];

            foreach ($students as $student) {
                Alternative::updateOrCreate(
                    ['code' => $student[0]],
                    ['student_name' => $student[1]]
                );
            }
        });
    }
}

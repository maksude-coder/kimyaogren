<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Turkish grade names/descriptions for the two Programs dd-grades.md's
     * Sample Data section documents explicitly. Any other seeded Program
     * gets a generic "{number}. Sınıf" name for its range instead of
     * fabricated curriculum descriptions this seeder has no basis for.
     */
    private const NAMED_GRADES = [
        'HS_CHEM_TR' => [
            9 => ['name' => '9. Sınıf', 'description' => 'Lise 1. yıl - Temel Kimya Kavramları'],
            10 => ['name' => '10. Sınıf', 'description' => 'Lise 2. yıl - Kimyasal Bağlar ve Reaksiyonlar'],
            11 => ['name' => '11. Sınıf', 'description' => 'Lise 3. yıl - Organik Kimya ve Enerji'],
            12 => ['name' => '12. Sınıf', 'description' => 'Lise 4. yıl - YKS Hazırlığı ve İleri Konular'],
        ],
        'MS_SCI_TR' => [
            5 => ['name' => '5. Sınıf', 'description' => 'Ortaokul 1. yıl - Fen Bilimlerine Giriş'],
            6 => ['name' => '6. Sınıf', 'description' => 'Ortaokul 2. yıl - Madde ve Değişim'],
            7 => ['name' => '7. Sınıf', 'description' => 'Ortaokul 3. yıl - Kuvvet ve Enerji'],
            8 => ['name' => '8. Sınıf', 'description' => 'Ortaokul 4. yıl - LGS Hazırlığı'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::query()->orderBy('display_order')->each(function (Program $program): void {
            $named = self::NAMED_GRADES[$program->code] ?? null;

            foreach (range($program->min_grade, $program->max_grade) as $index => $number) {
                Grade::factory()->for($program)->create([
                    'number' => $number,
                    'name' => $named[$number]['name'] ?? "{$number}. Sınıf",
                    'description' => $named[$number]['description'] ?? null,
                    'order' => $index + 1,
                    'is_active' => $program->is_active,
                ]);
            }
        });
    }
}

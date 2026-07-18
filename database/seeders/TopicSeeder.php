<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TopicStatus;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Real MEB chemistry topics for HS_CHEM_TR's Unit 9.1 and 9.2, per
     * dd-topics.md's Sample Data section. Any other Unit gets a small set
     * of generic placeholder topics instead of fabricated curriculum content
     * this seeder has no basis for.
     */
    private const NAMED_TOPICS = [
        'HS_CHEM_TR' => [
            '9.1' => [
                ['name' => 'Matter Classification', 'description' => 'Elements, compounds, mixtures', 'difficulty' => 2],
                ['name' => 'Physical vs Chemical Changes', 'description' => 'Identifying change types', 'difficulty' => 2],
                ['name' => 'Separation Techniques', 'description' => 'Filtration, distillation, chromatography', 'difficulty' => 3],
                ['name' => 'Density and Buoyancy', 'description' => 'Calculations and Archimedes principle', 'difficulty' => 3],
            ],
            '9.2' => [
                ['name' => 'Atomic Models', 'description' => 'Dalton, Thomson, Rutherford, Bohr', 'difficulty' => 3],
                ['name' => 'Subatomic Particles', 'description' => 'Protons, neutrons, electrons', 'difficulty' => 2],
                ['name' => 'Isotopes and Ions', 'description' => 'Atomic number, mass number, charge', 'difficulty' => 3],
                ['name' => 'Electron Configuration', 'description' => 'Shells, subshells, orbitals', 'difficulty' => 4],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::with('grade.program')->orderBy('grade_id')->orderBy('code')->get()->each(function (Unit $unit): void {
            $named = self::NAMED_TOPICS[$unit->grade->program->code][$unit->code] ?? null;

            if ($named) {
                foreach ($named as $index => $topic) {
                    Topic::factory()->for($unit)->named($topic['name'])->create([
                        'description' => $topic['description'],
                        'order' => $index + 1,
                        'difficulty' => $topic['difficulty'],
                        'status' => TopicStatus::Published->value,
                        'is_active' => $unit->is_active,
                    ]);
                }

                return;
            }

            // Generic fallback: two placeholder topics per Unit.
            foreach ([1, 2] as $sequence) {
                Topic::factory()->for($unit)->named("Topic {$sequence} ({$unit->code})")->create([
                    'description' => null,
                    'order' => $sequence,
                    'difficulty' => 3,
                    'status' => TopicStatus::Published->value,
                    'is_active' => $unit->is_active,
                ]);
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Real MEB chemistry units for HS_CHEM_TR's Grade 9 and Grade 10, per
     * dd-units.md's Sample Data section. Any other Grade gets a small set
     * of generic placeholder units instead of fabricated curriculum content
     * this seeder has no basis for.
     */
    private const NAMED_UNITS = [
        'HS_CHEM_TR' => [
            9 => [
                ['code' => '9.1', 'name' => 'Matter and Properties', 'description' => 'Atoms, elements, compounds, mixtures'],
                ['code' => '9.2', 'name' => 'Atomic Structure', 'description' => 'Electrons, protons, neutrons, isotopes'],
                ['code' => '9.3', 'name' => 'Periodic Table', 'description' => 'Groups, periods, trends'],
                ['code' => '9.4', 'name' => 'Chemical Bonds', 'description' => 'Ionic, covalent, metallic bonds'],
            ],
            10 => [
                ['code' => '10.1', 'name' => 'Hydrocarbons', 'description' => 'Alkanes, alkenes, alkynes'],
                ['code' => '10.2', 'name' => 'Alcohols and Ethers', 'description' => 'Properties and reactions'],
                ['code' => '10.3', 'name' => 'Aldehydes and Ketones', 'description' => 'Carbonyl chemistry'],
                ['code' => '10.4', 'name' => 'Carboxylic Acids', 'description' => 'Acids, esters, derivatives'],
                ['code' => '10.5', 'name' => 'Polymers', 'description' => 'Natural and synthetic polymers'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grade::with('program')->orderBy('program_id')->orderBy('number')->get()->each(function (Grade $grade): void {
            $named = self::NAMED_UNITS[$grade->program->code][$grade->number] ?? null;

            if ($named) {
                foreach ($named as $index => $unit) {
                    Unit::factory()->for($grade)->code($unit['code'])->create([
                        'name' => $unit['name'],
                        'description' => $unit['description'],
                        'order' => $index + 1,
                        'is_active' => $grade->is_active,
                    ]);
                }

                return;
            }

            // Generic fallback: two placeholder units per Grade.
            foreach ([1, 2] as $sequence) {
                Unit::factory()->for($grade)->code("{$grade->number}.{$sequence}")->create([
                    'name' => "Unit {$sequence}",
                    'description' => null,
                    'order' => $sequence,
                    'is_active' => $grade->is_active,
                ]);
            }
        });
    }
}

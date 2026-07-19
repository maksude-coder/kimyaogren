<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LearningObjective;
use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LearningObjectiveSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Real MEB learning objectives for HS_CHEM_TR's "Atomic Models" and
     * "Subatomic Particles" Topics (Unit 9.2), reused directly from
     * dd-learning-objectives.md's own Sample Data section. Any other Topic
     * gets two generic placeholder objectives instead of fabricated
     * curriculum content this seeder has no basis for.
     */
    private const NAMED_OBJECTIVES = [
        'Atomic Models' => [
            ['code' => 'KIM.9.2.1.1', 'description' => 'Atom modellerini (Dalton, Thomson, Rutherford, Bohr) tarihsel süreçte açıklar.', 'bloom_level' => 2, 'difficulty' => 2],
            ['code' => 'KIM.9.2.1.2', 'description' => 'Bohr atom modelinin postulatlarını ve kısıtlamalarını açıklar.', 'bloom_level' => 2, 'difficulty' => 3],
            ['code' => 'KIM.9.2.1.3', 'description' => 'Atom numarası, kütle numarası ve izotop kavramlarını ilişkilendirerek izotop sembollerini yazar.', 'bloom_level' => 3, 'difficulty' => 3],
            ['code' => 'KIM.9.2.1.4', 'description' => 'Bohr modeline göre elektron geçişlerinde enerji emilimi/verilmesi hesaplar.', 'bloom_level' => 3, 'difficulty' => 4],
            ['code' => 'KIM.9.2.1.5', 'description' => 'Kuantum mekanik atom modelinin temel özelliklerini (orbital, kuantum sayısı) açıklar.', 'bloom_level' => 2, 'difficulty' => 4],
        ],
        'Subatomic Particles' => [
            ['code' => 'KIM.9.2.2.1', 'description' => 'Proton, nötron ve elektronun kütle, yük ve konum özelliklerini karşılaştırır.', 'bloom_level' => 2, 'difficulty' => 2],
            ['code' => 'KIM.9.2.2.2', 'description' => 'Atom numarası (Z), kütle numarası (A) ve nükleon sayısı ilişkisini kurar.', 'bloom_level' => 3, 'difficulty' => 3],
            ['code' => 'KIM.9.2.2.3', 'description' => 'İyon oluşumunu elektron kaybı/kazancı ile açıklar ve iyon sembollerini yazar.', 'bloom_level' => 3, 'difficulty' => 3],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Topic::orderBy('unit_id')->orderBy('order')->get()->each(function (Topic $topic): void {
            $named = self::NAMED_OBJECTIVES[$topic->name] ?? null;

            if ($named) {
                foreach ($named as $index => $objective) {
                    LearningObjective::factory()->for($topic)->code($objective['code'])->create([
                        'description' => $objective['description'],
                        'bloom_level' => $objective['bloom_level'],
                        'difficulty' => $objective['difficulty'],
                        'order' => $index + 1,
                        'is_active' => $topic->is_active,
                    ]);
                }

                return;
            }

            // Generic fallback: two placeholder objectives per Topic.
            // `code` is built explicitly from the sequence index rather
            // than left to the factory's random default, since two random
            // draws for the same Topic could otherwise collide (DEBT-008's
            // uniqueness is scoped per-Topic, not global, but still must
            // not collide with itself within one Topic).
            $unitSequence = explode('.', $topic->unit->code)[1] ?? '1';

            foreach ([1, 2] as $sequence) {
                LearningObjective::factory()->for($topic)->code(
                    "KIM.{$topic->unit->grade->number}.{$unitSequence}.{$topic->order}.{$sequence}"
                )->create([
                    'order' => $sequence,
                    'is_active' => $topic->is_active,
                ]);
            }
        });
    }
}

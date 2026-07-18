<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::factory()->published()->turkish()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'code' => 'HS_CHEM_TR',
            'name' => 'High School Chemistry',
            'slug' => 'high-school-chemistry',
            'description' => 'Comprehensive chemistry curriculum for Turkish high schools (grades 9-12) aligned with MEB standards.',
            'short_description' => 'Core chemistry for grades 9-12',
            'primary_language' => 'tr',
            'supported_languages' => ['tr', 'en'],
            'min_grade' => 9,
            'max_grade' => 12,
            'status' => 2, // Published
            'is_active' => true,
            'display_order' => 10,
            'version' => 1,
            'meta_title' => 'High School Chemistry | KimyaÖğren',
            'meta_description' => 'Official MEB chemistry curriculum for Turkish high schools grades 9-12.',
            'meta_keywords' => 'chemistry,high school,MEB,YKS,Turkey',
            'settings' => [
                'pacing' => [
                    'weeks_per_unit' => 4,
                    'allow_self_paced' => true,
                    'enforce_sequential' => false,
                ],
                'assessment' => [
                    'passing_score' => 70,
                    'allow_retakes' => true,
                    'max_retakes' => 3,
                ],
                'features' => [
                    'spaced_repetition' => true,
                    'gamification' => false,
                    'parent_portal' => false,
                    'offline_mode' => true,
                ],
                'grading' => [
                    'scale' => 'percentage',
                    'show_ranking' => false,
                    'weight' => [
                        'practice' => 40,
                        'quiz' => 30,
                        'exam' => 30,
                    ],
                ],
                'notifications' => [
                    'due_reminders' => true,
                    'weekly_summary' => true,
                    'achievement_alerts' => false,
                ],
            ],
        ]);

        Program::factory()->published()->turkish()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'code' => 'HS_PHYSICS_TR',
            'name' => 'High School Physics',
            'slug' => 'high-school-physics',
            'description' => 'Complete physics curriculum covering mechanics, thermodynamics, electromagnetism, and modern physics for Turkish high schools.',
            'short_description' => 'Physics for grades 9-12',
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
            'min_grade' => 9,
            'max_grade' => 12,
            'status' => 2,
            'is_active' => true,
            'display_order' => 20,
            'version' => 1,
            'meta_title' => 'High School Physics | KimyaÖğren',
            'meta_description' => 'Official MEB physics curriculum for Turkish high schools grades 9-12.',
            'meta_keywords' => 'physics,high school,MEB,YKS,Turkey',
        ]);

        Program::factory()->published()->turkish()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440002',
            'code' => 'HS_BIOLOGY_TR',
            'name' => 'High School Biology',
            'slug' => 'high-school-biology',
            'description' => 'Biology curriculum covering cell biology, genetics, evolution, ecology, and human physiology for Turkish high schools.',
            'short_description' => 'Biology for grades 9-12',
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
            'min_grade' => 9,
            'max_grade' => 12,
            'status' => 1, // Draft
            'is_active' => true,
            'display_order' => 30,
            'version' => 1,
        ]);

        Program::factory()->published()->english()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440003',
            'code' => 'IB_CHEM_EN',
            'name' => 'IB Chemistry',
            'slug' => 'ib-chemistry',
            'description' => 'International Baccalaureate Chemistry program covering both Standard Level (SL) and Higher Level (HL) topics.',
            'short_description' => 'IB Chemistry SL/HL',
            'primary_language' => 'en',
            'supported_languages' => ['en', 'tr'],
            'min_grade' => 11,
            'max_grade' => 12,
            'status' => 2,
            'is_active' => true,
            'display_order' => 40,
            'version' => 2,
            'meta_title' => 'IB Chemistry | KimyaÖğren',
            'meta_description' => 'International Baccalaureate Chemistry SL/HL program.',
            'meta_keywords' => 'chemistry,IB,international,SL,HL',
        ]);

        Program::factory()->draft()->english()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440004',
            'code' => 'AP_CHEM_EN',
            'name' => 'AP Chemistry',
            'slug' => 'ap-chemistry',
            'description' => 'Advanced Placement Chemistry course covering atomic structure, bonding, states of matter, reactions, and thermodynamics.',
            'short_description' => 'AP Chemistry for college preparation',
            'primary_language' => 'en',
            'supported_languages' => ['en'],
            'min_grade' => 11,
            'max_grade' => 12,
            'status' => 1,
            'is_active' => false,
            'display_order' => 50,
            'version' => 1,
        ]);

        Program::factory()->published()->turkish()->middleSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440005',
            'code' => 'MS_SCI_TR',
            'name' => 'Middle School Science',
            'slug' => 'middle-school-science',
            'description' => 'Integrated science curriculum for Turkish middle schools (grades 5-8) covering physics, chemistry, biology, and earth science.',
            'short_description' => 'Integrated science for grades 5-8',
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
            'min_grade' => 5,
            'max_grade' => 8,
            'status' => 2,
            'is_active' => true,
            'display_order' => 60,
            'version' => 1,
        ]);

        Program::factory()->archived()->turkish()->highSchool()->create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440006',
            'code' => 'OLD_CHEM_TR',
            'name' => 'Old Chemistry Curriculum (2013)',
            'slug' => 'old-chemistry-curriculum-2013',
            'description' => 'Previous version of chemistry curriculum (2013-2023), archived for historical reference.',
            'short_description' => 'Archived 2013 chemistry curriculum',
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
            'min_grade' => 9,
            'max_grade' => 12,
            'status' => 3, // Archived
            'is_active' => false,
            'display_order' => 100,
            'version' => 3,
        ]);
    }
}

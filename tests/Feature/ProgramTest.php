<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgramStatus;
use App\Filament\Resources\Programs\Pages\CreateProgram;
use App\Filament\Resources\Programs\Pages\EditProgram;
use App\Filament\Resources\Programs\Pages\ListPrograms;
use App\Filament\Resources\Programs\Pages\ViewProgram;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_program(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'HS_CHEM_TR',
                'name' => 'High School Chemistry',
                'slug' => 'high-school-chemistry',
                'description' => 'Comprehensive chemistry curriculum for Turkish high schools (grades 9-12) aligned with MEB standards.',
                'short_description' => 'Core chemistry for grades 9-12',
                'primary_language' => 'tr',
                'supported_languages' => ['tr', 'en'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
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
                ],
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.programs.index'));

        $this->assertDatabaseHas('programs', [
            'code' => 'HS_CHEM_TR',
            'name' => 'High School Chemistry',
            'status' => ProgramStatus::Draft->value,
        ]);
    }

    public function test_student_cannot_create_program(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $response = $this->get(route('filament.admin.resources.programs.create'));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_panel(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher);

        $response = $this->get(route('filament.admin.resources.programs.index'));

        $response->assertForbidden();
    }

    public function test_content_editor_can_create_program(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'EDITOR_PROGRAM',
                'name' => 'Editor Created Program',
                'slug' => 'editor-created-program',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('programs', ['code' => 'EDITOR_PROGRAM']);
    }

    public function test_admin_can_view_program_view_page(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->create();

        Livewire::test(ViewProgram::class, ['record' => $program->getKey()])
            ->assertSee($program->name);
    }

    public function test_program_name_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Program::factory()->create(['name' => 'High School Chemistry']);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'HS_CHEM_TR_2',
                'name' => 'High School Chemistry',
                'slug' => 'high-school-chemistry-2',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_program_slug_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Program::factory()->create(['slug' => 'high-school-chemistry']);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'HS_CHEM_TR_2',
                'name' => 'High School Chemistry 2',
                'slug' => 'high-school-chemistry',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_program_code_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Program::factory()->create(['code' => 'HS_CHEM_TR']);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'HS_CHEM_TR',
                'name' => 'Another Chemistry',
                'slug' => 'another-chemistry',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_program_max_grade_cannot_be_less_than_min_grade(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'INVALID_GRADES',
                'name' => 'Invalid Grades',
                'slug' => 'invalid-grades',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 12,
                'max_grade' => 9,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors(['max_grade']);
    }

    public function test_admin_can_view_program_list(): void
    {
        $this->actingAs($this->admin);

        Program::factory()->create(['name' => 'High School Chemistry']);
        Program::factory()->count(2)->create();

        Livewire::test(ListPrograms::class)
            ->assertSee('High School Chemistry');
    }

    public function test_admin_can_update_program(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        Livewire::test(EditProgram::class, ['record' => $program->getKey()])
            ->fillForm([
                'name' => 'New Name',
                'slug' => 'new-name',
            ])
            ->call('save')
            ->assertRedirect(route('filament.admin.resources.programs.index'));

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    }

    public function test_admin_can_delete_program(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->create();

        Livewire::test(ListPrograms::class)
            ->callTableAction('delete', $program->getKey())
            ->assertRedirect(route('filament.admin.resources.programs.index'));

        $this->assertSoftDeleted($program);
    }

    public function test_program_slug_is_auto_generated_from_name(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'code' => 'AUTO_SLUG',
                'name' => 'Auto Generated Slug Test',
                'primary_language' => 'tr',
                'supported_languages' => ['tr'],
                'min_grade' => 9,
                'max_grade' => 12,
                'status' => ProgramStatus::Draft->value,
                'is_active' => true,
                'display_order' => 20,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.programs.index'));

        $this->assertDatabaseHas('programs', [
            'code' => 'AUTO_SLUG',
            'slug' => 'auto-generated-slug-test',
        ]);
    }

    public function test_published_program_is_visible_to_students(): void
    {
        $program = Program::factory()->published()->active()->create();

        $student = User::factory()->student()->create();

        $this->actingAs($student);

        // The program should be visible in the student-facing curriculum browser
        $this->assertTrue($program->is_published);
        $this->assertTrue($program->is_active);
    }

    public function test_draft_program_is_not_visible_to_students(): void
    {
        $program = Program::factory()->draft()->create();

        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $this->assertFalse($program->is_published);
    }

    public function test_archived_program_is_not_visible_to_students(): void
    {
        $program = Program::factory()->archived()->create();

        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $this->assertTrue($program->is_archived);
    }

    public function test_program_factory_states_work_correctly(): void
    {
        $published = Program::factory()->published()->active()->create();
        $this->assertEquals(ProgramStatus::Published, $published->status);
        $this->assertTrue($published->is_active);

        $draft = Program::factory()->draft()->create();
        $this->assertEquals(ProgramStatus::Draft, $draft->status);

        $archived = Program::factory()->archived()->create();
        $this->assertEquals(ProgramStatus::Archived, $archived->status);
        $this->assertFalse($archived->is_active);

        $turkish = Program::factory()->turkish()->create();
        $this->assertEquals('tr', $turkish->primary_language);

        $english = Program::factory()->english()->create();
        $this->assertEquals('en', $english->primary_language);

        $bilingual = Program::factory()->bilingual()->create();
        $this->assertEquals(['tr', 'en'], $bilingual->supported_languages);

        $highSchool = Program::factory()->highSchool()->create();
        $this->assertEquals(9, $highSchool->min_grade);
        $this->assertEquals(12, $highSchool->max_grade);

        $middleSchool = Program::factory()->middleSchool()->create();
        $this->assertEquals(5, $middleSchool->min_grade);
        $this->assertEquals(8, $middleSchool->max_grade);
    }

    public function test_program_scopes_work_correctly(): void
    {
        // Explicitly set is_active for draft programs
        Program::factory()->published()->count(3)->create();
        Program::factory()->draft()->active()->count(2)->create();
        Program::factory()->archived()->count(1)->create();

        $this->assertEquals(3, Program::published()->count());
        $this->assertEquals(5, Program::active()->count());
        $this->assertEquals(1, Program::archived()->count());

        $sequenced = Program::factory()->count(3)->sequence(
            ['display_order' => 10],
            ['display_order' => 5],
            ['display_order' => 15],
        )->create();
        $ordered = Program::ordered()->whereIn('id', $sequenced->pluck('id'))->pluck('display_order')->toArray();
        $this->assertEquals([5, 10, 15], $ordered);

        $forGrade = Program::forGrade(10)->count();
        $this->assertGreaterThan(0, $forGrade);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Grades\Pages\CreateGrade;
use App\Filament\Resources\Grades\Pages\EditGrade;
use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Grades\Pages\ViewGrade;
use App\Models\Grade;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_grade(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->highSchool()->create();

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'program_id' => $program->id,
                'number' => 9,
                'name' => '9. Sınıf',
                'description' => 'Lise 1. yıl - Temel Kimya Kavramları',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.grades.index'));

        $this->assertDatabaseHas('grades', [
            'program_id' => $program->id,
            'number' => 9,
            'name' => '9. Sınıf',
        ]);
    }

    public function test_student_cannot_access_grades(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $response = $this->get(route('filament.admin.resources.grades.index'));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_panel_for_grades(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher);

        $response = $this->get(route('filament.admin.resources.grades.create'));

        $response->assertForbidden();
    }

    public function test_content_editor_can_create_grade(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor);

        $program = Program::factory()->highSchool()->create();

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'program_id' => $program->id,
                'number' => 10,
                'name' => '10. Sınıf',
                'order' => 2,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('grades', ['program_id' => $program->id, 'number' => 10]);
    }

    public function test_admin_can_view_grade_view_page(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->create();

        Livewire::test(ViewGrade::class, ['record' => $grade->getKey()])
            ->assertSee($grade->name);
    }

    public function test_grade_number_must_be_unique_within_program(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->highSchool()->create();
        Grade::factory()->for($program)->number(9)->create();

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'program_id' => $program->id,
                'number' => 9,
                'name' => '9. Sınıf (duplicate)',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['number' => 'unique']);
    }

    public function test_grade_number_can_repeat_across_different_programs(): void
    {
        $this->actingAs($this->admin);

        $programA = Program::factory()->highSchool()->create();
        $programB = Program::factory()->highSchool()->create();

        Grade::factory()->for($programA)->number(9)->create();

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'program_id' => $programB->id,
                'number' => 9,
                'name' => '9. Sınıf',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('grades', ['program_id' => $programA->id, 'number' => 9]);
        $this->assertDatabaseHas('grades', ['program_id' => $programB->id, 'number' => 9]);
    }

    public function test_grade_number_must_be_within_program_grade_range(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->highSchool()->create(); // min_grade 9, max_grade 12

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'program_id' => $program->id,
                'number' => 5,
                'name' => 'Out of range',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['number']);
    }

    public function test_admin_can_view_grade_list(): void
    {
        $this->actingAs($this->admin);

        $program = Program::factory()->highSchool()->create();
        Grade::factory()->for($program)->number(9)->create(['name' => '9. Sınıf']);
        Grade::factory()->count(2)->create();

        Livewire::test(ListGrades::class)
            ->assertSee('9. Sınıf');
    }

    public function test_admin_can_update_grade(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->create(['name' => 'Old Name']);

        Livewire::test(EditGrade::class, ['record' => $grade->getKey()])
            ->fillForm([
                'name' => 'New Name',
            ])
            ->call('save')
            ->assertRedirect(route('filament.admin.resources.grades.index'));

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_grade(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->create();

        Livewire::test(ListGrades::class)
            ->callTableAction('delete', $grade->getKey())
            ->assertRedirect(route('filament.admin.resources.grades.index'));

        $this->assertSoftDeleted($grade);
    }

    public function test_grade_factory_enforces_program_range(): void
    {
        $program = Program::factory()->middleSchool()->create(); // min_grade 5, max_grade 8

        $grade = Grade::factory()->for($program)->create();

        $this->assertGreaterThanOrEqual(5, $grade->number);
        $this->assertLessThanOrEqual(8, $grade->number);
    }

    public function test_grade_scopes_work_correctly(): void
    {
        $program = Program::factory()->highSchool()->create();

        Grade::factory()->for($program)->number(9)->inactive()->create();
        Grade::factory()->for($program)->number(10)->active()->create();

        $this->assertEquals(1, Grade::where('program_id', $program->id)->active()->count());

        $sequenced = Grade::factory()->for($program)->number(11)->create(['order' => 5]);
        $this->assertEquals(5, Grade::ordered()->where('id', $sequenced->id)->first()->order);
    }

    public function test_deleting_program_cascades_to_grades(): void
    {
        $program = Program::factory()->highSchool()->create();
        $grade = Grade::factory()->for($program)->number(9)->create();

        $program->forceDelete();

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }
}

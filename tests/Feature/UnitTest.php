<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Units\Pages\CreateUnit;
use App\Filament\Resources\Units\Pages\EditUnit;
use App\Filament\Resources\Units\Pages\ListUnits;
use App\Filament\Resources\Units\Pages\ViewUnit;
use App\Models\Grade;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_unit(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->number(9)->create();

        Livewire::test(CreateUnit::class)
            ->fillForm([
                'grade_id' => $grade->id,
                'code' => '9.1',
                'name' => 'Matter and Properties',
                'description' => 'Atoms, elements, compounds, mixtures',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.units.index'));

        $this->assertDatabaseHas('units', [
            'grade_id' => $grade->id,
            'code' => '9.1',
            'name' => 'Matter and Properties',
        ]);
    }

    public function test_student_cannot_access_units(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $response = $this->get(route('filament.admin.resources.units.index'));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_panel_for_units(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher);

        $response = $this->get(route('filament.admin.resources.units.create'));

        $response->assertForbidden();
    }

    public function test_content_editor_can_create_unit(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor);

        $grade = Grade::factory()->number(10)->create();

        Livewire::test(CreateUnit::class)
            ->fillForm([
                'grade_id' => $grade->id,
                'code' => '10.1',
                'name' => 'Hydrocarbons',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('units', ['grade_id' => $grade->id, 'code' => '10.1']);
    }

    public function test_admin_can_view_unit_view_page(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->create();

        Livewire::test(ViewUnit::class, ['record' => $unit->getKey()])
            ->assertSee($unit->name);
    }

    public function test_unit_code_must_be_unique_within_grade(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->number(9)->create();
        Unit::factory()->for($grade)->code('9.1')->create();

        Livewire::test(CreateUnit::class)
            ->fillForm([
                'grade_id' => $grade->id,
                'code' => '9.1',
                'name' => 'Duplicate Unit',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_unit_code_can_repeat_across_different_grades(): void
    {
        $this->actingAs($this->admin);

        $gradeA = Grade::factory()->number(9)->create();
        $gradeB = Grade::factory()->number(10)->create();

        Unit::factory()->for($gradeA)->code('X.1')->create();

        Livewire::test(CreateUnit::class)
            ->fillForm([
                'grade_id' => $gradeB->id,
                'code' => 'X.1',
                'name' => 'Same Code, Different Grade',
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('units', ['grade_id' => $gradeA->id, 'code' => 'X.1']);
        $this->assertDatabaseHas('units', ['grade_id' => $gradeB->id, 'code' => 'X.1']);
    }

    public function test_admin_can_view_unit_list(): void
    {
        $this->actingAs($this->admin);

        $grade = Grade::factory()->number(9)->create();
        Unit::factory()->for($grade)->code('9.1')->create(['name' => 'Matter and Properties']);
        Unit::factory()->count(2)->create();

        Livewire::test(ListUnits::class)
            ->assertSee('Matter and Properties');
    }

    public function test_admin_can_update_unit(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->create(['name' => 'Old Name']);

        Livewire::test(EditUnit::class, ['record' => $unit->getKey()])
            ->fillForm([
                'name' => 'New Name',
            ])
            ->call('save')
            ->assertRedirect(route('filament.admin.resources.units.index'));

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_unit(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->create();

        Livewire::test(ListUnits::class)
            ->callTableAction('delete', $unit->getKey())
            ->assertRedirect(route('filament.admin.resources.units.index'));

        $this->assertSoftDeleted($unit);
    }

    public function test_unit_factory_derives_code_from_grade_number(): void
    {
        $grade = Grade::factory()->number(11)->create();

        $unit = Unit::factory()->for($grade)->create();

        $this->assertStringStartsWith('11.', $unit->code);
    }

    public function test_unit_scopes_work_correctly(): void
    {
        $grade = Grade::factory()->number(9)->create();

        Unit::factory()->for($grade)->code('9.1')->inactive()->create();
        Unit::factory()->for($grade)->code('9.2')->active()->create();

        $this->assertEquals(1, Unit::where('grade_id', $grade->id)->active()->count());

        $sequenced = Unit::factory()->for($grade)->code('9.3')->create(['order' => 5]);
        $this->assertEquals(5, Unit::ordered()->where('id', $sequenced->id)->first()->order);
    }

    public function test_deleting_grade_cascades_to_units(): void
    {
        $grade = Grade::factory()->number(9)->create();
        $unit = Unit::factory()->for($grade)->code('9.1')->create();

        $grade->forceDelete();

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_soft_deleting_grade_does_not_cascade_to_units(): void
    {
        $grade = Grade::factory()->number(9)->create();
        $unit = Unit::factory()->for($grade)->code('9.1')->create();

        $grade->delete();

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'deleted_at' => null]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BloomLevel;
use App\Filament\Resources\LearningObjectives\Pages\CreateLearningObjective;
use App\Filament\Resources\LearningObjectives\Pages\EditLearningObjective;
use App\Filament\Resources\LearningObjectives\Pages\ListLearningObjectives;
use App\Filament\Resources\LearningObjectives\Pages\ViewLearningObjective;
use App\Models\LearningObjective;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class LearningObjectiveTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_learning_objective(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();

        Livewire::test(CreateLearningObjective::class)
            ->fillForm([
                'topic_id' => $topic->id,
                'code' => 'KIM.9.2.1.1',
                'description' => 'Atom modellerini tarihsel süreçte açıklar.',
                'bloom_level' => BloomLevel::Understand->value,
                'difficulty' => 2,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.learning-objectives.index'));

        $this->assertDatabaseHas('learning_objectives', [
            'topic_id' => $topic->id,
            'code' => 'KIM.9.2.1.1',
        ]);
    }

    public function test_student_cannot_access_learning_objectives(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $response = $this->get(route('filament.admin.resources.learning-objectives.index'));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_panel_for_learning_objectives(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher);

        $response = $this->get(route('filament.admin.resources.learning-objectives.create'));

        $response->assertForbidden();
    }

    public function test_content_editor_can_create_learning_objective(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor);

        $topic = Topic::factory()->create();

        Livewire::test(CreateLearningObjective::class)
            ->fillForm([
                'topic_id' => $topic->id,
                'code' => 'KIM.9.1.1.1',
                'description' => 'Test objective description.',
                'bloom_level' => BloomLevel::Remember->value,
                'difficulty' => 1,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('learning_objectives', ['topic_id' => $topic->id, 'code' => 'KIM.9.1.1.1']);
    }

    public function test_admin_can_view_learning_objective_view_page(): void
    {
        $this->actingAs($this->admin);

        $objective = LearningObjective::factory()->create();

        Livewire::test(ViewLearningObjective::class, ['record' => $objective->getKey()])
            ->assertSee($objective->code);
    }

    public function test_learning_objective_code_must_be_unique_within_topic(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();
        LearningObjective::factory()->for($topic)->code('KIM.9.1.1.1')->create();

        Livewire::test(CreateLearningObjective::class)
            ->fillForm([
                'topic_id' => $topic->id,
                'code' => 'KIM.9.1.1.1',
                'description' => 'Duplicate code test.',
                'bloom_level' => BloomLevel::Remember->value,
                'difficulty' => 1,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_learning_objective_code_can_repeat_across_different_topics(): void
    {
        $this->actingAs($this->admin);

        $topicA = Topic::factory()->create();
        $topicB = Topic::factory()->create();

        LearningObjective::factory()->for($topicA)->code('KIM.9.1.1.1')->create();

        Livewire::test(CreateLearningObjective::class)
            ->fillForm([
                'topic_id' => $topicB->id,
                'code' => 'KIM.9.1.1.1',
                'description' => 'Same code, different topic.',
                'bloom_level' => BloomLevel::Remember->value,
                'difficulty' => 1,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('learning_objectives', ['topic_id' => $topicA->id, 'code' => 'KIM.9.1.1.1']);
        $this->assertDatabaseHas('learning_objectives', ['topic_id' => $topicB->id, 'code' => 'KIM.9.1.1.1']);
    }

    public function test_learning_objective_code_must_match_meb_format(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();

        Livewire::test(CreateLearningObjective::class)
            ->fillForm([
                'topic_id' => $topic->id,
                'code' => 'not-a-valid-code',
                'description' => 'Invalid code format test.',
                'bloom_level' => BloomLevel::Remember->value,
                'difficulty' => 1,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'regex']);
    }

    public function test_admin_can_view_learning_objective_list(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();
        LearningObjective::factory()->for($topic)->code('KIM.9.1.1.1')->create(['description' => 'Findable Description Text']);
        LearningObjective::factory()->count(2)->create();

        Livewire::test(ListLearningObjectives::class)
            ->assertSee('Findable Description Text');
    }

    public function test_admin_can_update_learning_objective(): void
    {
        $this->actingAs($this->admin);

        $objective = LearningObjective::factory()->create(['description' => 'Old Description']);

        Livewire::test(EditLearningObjective::class, ['record' => $objective->getKey()])
            ->fillForm([
                'description' => 'New Description',
            ])
            ->call('save')
            ->assertRedirect(route('filament.admin.resources.learning-objectives.index'));

        $this->assertDatabaseHas('learning_objectives', [
            'id' => $objective->id,
            'description' => 'New Description',
        ]);
    }

    public function test_admin_can_delete_learning_objective(): void
    {
        $this->actingAs($this->admin);

        $objective = LearningObjective::factory()->create();

        Livewire::test(ListLearningObjectives::class)
            ->callTableAction('delete', $objective->getKey())
            ->assertRedirect(route('filament.admin.resources.learning-objectives.index'));

        $this->assertSoftDeleted($objective);
    }

    public function test_learning_objective_scopes_work_correctly(): void
    {
        $topic = Topic::factory()->create();

        LearningObjective::factory()->for($topic)->code('KIM.9.1.1.1')->inactive()->create();
        LearningObjective::factory()->for($topic)->code('KIM.9.1.1.2')->active()->create();

        $this->assertEquals(1, LearningObjective::where('topic_id', $topic->id)->active()->count());

        $sequenced = LearningObjective::factory()->for($topic)->code('KIM.9.1.1.3')->create(['order' => 5]);
        $this->assertEquals(5, LearningObjective::ordered()->where('id', $sequenced->id)->first()->order);
    }

    public function test_deleting_topic_cascades_to_learning_objectives(): void
    {
        $topic = Topic::factory()->create();
        $objective = LearningObjective::factory()->for($topic)->code('KIM.9.1.1.1')->create();

        $topic->forceDelete();

        $this->assertDatabaseMissing('learning_objectives', ['id' => $objective->id]);
    }

    public function test_soft_deleting_topic_does_not_cascade_to_learning_objectives(): void
    {
        $topic = Topic::factory()->create();
        $objective = LearningObjective::factory()->for($topic)->code('KIM.9.1.1.1')->create();

        $topic->delete();

        $this->assertDatabaseHas('learning_objectives', ['id' => $objective->id, 'deleted_at' => null]);
    }
}

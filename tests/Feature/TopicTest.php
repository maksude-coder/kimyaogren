<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TopicStatus;
use App\Filament\Resources\Topics\Pages\CreateTopic;
use App\Filament\Resources\Topics\Pages\EditTopic;
use App\Filament\Resources\Topics\Pages\ListTopics;
use App\Filament\Resources\Topics\Pages\ViewTopic;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_topic(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->code('9.1')->create();

        Livewire::test(CreateTopic::class)
            ->fillForm([
                'unit_id' => $unit->id,
                'name' => 'Matter Classification',
                'slug' => 'matter-classification',
                'description' => 'Elements, compounds, mixtures',
                'order' => 1,
                'difficulty' => 2,
                'status' => TopicStatus::Published->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.topics.index'));

        $this->assertDatabaseHas('topics', [
            'unit_id' => $unit->id,
            'slug' => 'matter-classification',
            'name' => 'Matter Classification',
        ]);
    }

    public function test_student_cannot_access_topics(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student);

        $response = $this->get(route('filament.admin.resources.topics.index'));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_panel_for_topics(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher);

        $response = $this->get(route('filament.admin.resources.topics.create'));

        $response->assertForbidden();
    }

    public function test_content_editor_can_create_topic(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor);

        $unit = Unit::factory()->code('10.1')->create();

        Livewire::test(CreateTopic::class)
            ->fillForm([
                'unit_id' => $unit->id,
                'name' => 'Hydrocarbon Basics',
                'slug' => 'hydrocarbon-basics',
                'order' => 1,
                'difficulty' => 3,
                'status' => TopicStatus::Draft->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('topics', ['unit_id' => $unit->id, 'slug' => 'hydrocarbon-basics']);
    }

    public function test_admin_can_view_topic_view_page(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();

        Livewire::test(ViewTopic::class, ['record' => $topic->getKey()])
            ->assertSee($topic->name);
    }

    public function test_topic_slug_must_be_unique_within_unit(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->code('9.1')->create();
        Topic::factory()->for($unit)->slug('atomic-models')->create();

        Livewire::test(CreateTopic::class)
            ->fillForm([
                'unit_id' => $unit->id,
                'name' => 'Atomic Models Duplicate',
                'slug' => 'atomic-models',
                'order' => 1,
                'difficulty' => 3,
                'status' => TopicStatus::Draft->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_topic_slug_can_repeat_across_different_units(): void
    {
        $this->actingAs($this->admin);

        $unitA = Unit::factory()->code('9.1')->create();
        $unitB = Unit::factory()->code('9.2')->create();

        Topic::factory()->for($unitA)->slug('introduction')->create();

        Livewire::test(CreateTopic::class)
            ->fillForm([
                'unit_id' => $unitB->id,
                'name' => 'Introduction',
                'slug' => 'introduction',
                'order' => 1,
                'difficulty' => 3,
                'status' => TopicStatus::Draft->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('topics', ['unit_id' => $unitA->id, 'slug' => 'introduction']);
        $this->assertDatabaseHas('topics', ['unit_id' => $unitB->id, 'slug' => 'introduction']);
    }

    public function test_admin_can_view_topic_list(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->code('9.1')->create();
        Topic::factory()->for($unit)->named('Matter Classification')->create();
        Topic::factory()->count(2)->create();

        Livewire::test(ListTopics::class)
            ->assertSee('Matter Classification');
    }

    public function test_admin_can_update_topic(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->named('Old Name')->create();

        Livewire::test(EditTopic::class, ['record' => $topic->getKey()])
            ->fillForm([
                'name' => 'New Name',
            ])
            ->call('save')
            ->assertRedirect(route('filament.admin.resources.topics.index'));

        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_topic(): void
    {
        $this->actingAs($this->admin);

        $topic = Topic::factory()->create();

        Livewire::test(ListTopics::class)
            ->callTableAction('delete', $topic->getKey())
            ->assertRedirect(route('filament.admin.resources.topics.index'));

        $this->assertSoftDeleted($topic);
    }

    public function test_topic_slug_is_auto_generated_from_name(): void
    {
        $this->actingAs($this->admin);

        $unit = Unit::factory()->code('9.1')->create();

        Livewire::test(CreateTopic::class)
            ->fillForm([
                'unit_id' => $unit->id,
                'name' => 'Auto Generated Slug Test',
                'order' => 1,
                'difficulty' => 3,
                'status' => TopicStatus::Draft->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertRedirect(route('filament.admin.resources.topics.index'));

        $this->assertDatabaseHas('topics', [
            'unit_id' => $unit->id,
            'slug' => 'auto-generated-slug-test',
        ]);
    }

    public function test_topic_scopes_work_correctly(): void
    {
        $unit = Unit::factory()->code('9.1')->create();

        Topic::factory()->for($unit)->slug('published-active')->published()->create();
        Topic::factory()->for($unit)->slug('draft-topic')->draft()->create();
        Topic::factory()->for($unit)->slug('published-inactive')->create(['status' => TopicStatus::Published->value, 'is_active' => false]);

        $this->assertEquals(1, Topic::where('unit_id', $unit->id)->published()->count());

        $sequenced = Topic::factory()->for($unit)->slug('ordered-topic')->create(['order' => 5]);
        $this->assertEquals(5, Topic::ordered()->where('id', $sequenced->id)->first()->order);
    }

    public function test_deleting_unit_cascades_to_topics(): void
    {
        $unit = Unit::factory()->code('9.1')->create();
        $topic = Topic::factory()->for($unit)->slug('cascade-test')->create();

        $unit->forceDelete();

        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }

    public function test_soft_deleting_unit_does_not_cascade_to_topics(): void
    {
        $unit = Unit::factory()->code('9.1')->create();
        $topic = Topic::factory()->for($unit)->slug('soft-delete-test')->create();

        $unit->delete();

        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'deleted_at' => null]);
    }
}

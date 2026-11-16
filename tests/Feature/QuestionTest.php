<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Filament\Resources\Questions\Pages\ViewQuestion;
use App\Models\LearningObjective;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->topic = Topic::factory()->create();
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function multipleChoiceInput(array $override = []): array
    {
        return [
            'topic_id' => $this->topic->id,
            'type' => QuestionType::MultipleChoice->value,
            'content' => '<p>0,2 mol H<sub>2</sub>SO<sub>4</sub> için kaç gram NaOH gerekir?</p>',
            'explanation' => '<ol><li><p>Denklemi yaz</p></li><li><p>Mol oranı 1 : 2</p></li></ol>',
            'difficulty' => 3,
            'bloom_level' => 3,
            'is_active' => true,
            'options' => [
                'A' => ['option_text' => '<p>4 g</p>', 'feedback' => '0,1 mol NaOH hesaplanmış olabilir.'],
                'B' => ['option_text' => '<p>8 g</p>', 'feedback' => 'Katsayı 2 atlanmış.'],
                'C' => ['option_text' => '<p>12 g</p>', 'feedback' => null],
                'D' => ['option_text' => '<p>16 g</p>', 'feedback' => null],
                'E' => ['option_text' => '<p>20 g</p>', 'feedback' => null],
            ],
            'correct_option' => 'D',
            ...$override,
        ];
    }

    // Access

    public function test_admin_can_view_question_list(): void
    {
        $questions = Question::factory()->for($this->topic)->count(3)->create();

        $this->actingAs($this->admin);

        Livewire::test(ListQuestions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($questions);
    }

    public function test_student_and_teacher_cannot_access_questions(): void
    {
        foreach ([User::factory()->student()->create(), User::factory()->teacher()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('filament.admin.resources.questions.index'))
                ->assertForbidden();
        }
    }

    public function test_only_admin_may_force_delete(): void
    {
        $question = Question::factory()->for($this->topic)->create();
        $editor = User::factory()->contentEditor()->create();

        $this->assertTrue($editor->can('delete', $question));
        $this->assertFalse($editor->can('forceDelete', $question));
        $this->assertTrue($this->admin->can('forceDelete', $question));
    }

    // Create

    public function test_content_editor_can_create_a_multiple_choice_question(): void
    {
        $editor = User::factory()->contentEditor()->create();
        $this->actingAs($editor);

        Livewire::test(CreateQuestion::class)
            ->fillForm($this->multipleChoiceInput())
            ->call('create')
            ->assertHasNoFormErrors();

        $question = Question::query()->sole();

        $this->assertSame(QuestionType::MultipleChoice, $question->type);
        $this->assertSame(QuestionStatus::Draft, $question->status);
        $this->assertSame($this->topic->id, $question->topic_id);
        $this->assertStringContainsString('H<sub>2</sub>SO<sub>4</sub>', $question->content);
        $this->assertSame($editor->id, $question->created_by_id);
        $this->assertSame($editor->id, $question->updated_by_id);

        $options = $question->options;
        $this->assertSame([1, 2, 3, 4, 5], $options->pluck('order')->all());
        $this->assertSame([false, false, false, true, false], $options->pluck('is_correct')->all());
        $this->assertStringContainsString('16 g', $question->correctOption->option_text);
        $this->assertSame('D', $question->correctOption->letter());
        $this->assertSame('Katsayı 2 atlanmış.', $options[1]->feedback);
        $this->assertNull($options[2]->feedback);
        $this->assertTrue($question->hasValidOptions());
    }

    public function test_admin_can_create_a_true_false_question(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateQuestion::class)
            ->fillForm([
                'topic_id' => $this->topic->id,
                'type' => QuestionType::TrueFalse->value,
                'content' => '<p>Asitler mavi turnusolu kırmızıya çevirir.</p>',
                'difficulty' => 1,
                'bloom_level' => 1,
                'correct_answer' => 'true',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $question = Question::query()->sole();

        $this->assertSame(QuestionType::TrueFalse, $question->type);
        $this->assertSame(['Doğru', 'Yanlış'], $question->options->pluck('option_text')->all());
        $this->assertSame([true, false], $question->options->pluck('is_correct')->all());
        $this->assertTrue($question->hasValidOptions());
    }

    public function test_multiple_choice_requires_all_five_options_and_a_correct_one(): void
    {
        $this->actingAs($this->admin);

        $input = $this->multipleChoiceInput(['correct_option' => null]);
        $input['options']['E']['option_text'] = null;

        Livewire::test(CreateQuestion::class)
            ->fillForm($input)
            ->call('create')
            ->assertHasFormErrors(['options.E.option_text', 'correct_option' => 'required']);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_required_fields_and_true_false_answer_are_validated(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateQuestion::class)
            ->fillForm(['topic_id' => null, 'type' => QuestionType::TrueFalse->value, 'content' => null])
            ->call('create')
            ->assertHasFormErrors(['topic_id' => 'required', 'content', 'correct_answer' => 'required']);
    }

    public function test_unsupported_types_are_not_offered(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateQuestion::class)
            ->fillForm($this->multipleChoiceInput(['type' => QuestionType::Essay->value]))
            ->call('create')
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_learning_objective_must_belong_to_the_topic(): void
    {
        $own = LearningObjective::factory()->for($this->topic)->create();
        $foreign = LearningObjective::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(CreateQuestion::class)
            ->fillForm($this->multipleChoiceInput(['learning_objective_id' => $foreign->id]))
            ->call('create')
            ->assertHasFormErrors(['learning_objective_id']);

        Livewire::test(CreateQuestion::class)
            ->fillForm($this->multipleChoiceInput(['learning_objective_id' => $own->id]))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($own->id, Question::query()->sole()->learning_objective_id);
    }

    public function test_status_cannot_be_set_through_the_form(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateQuestion::class)
            ->fillForm($this->multipleChoiceInput(['status' => QuestionStatus::Approved->value]))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(QuestionStatus::Draft, Question::query()->sole()->status);
    }

    // Edit / view

    public function test_edit_form_is_filled_with_the_options(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();
        $question->options[2]->update(['feedback' => 'Neden C?']);

        $this->actingAs($this->admin);

        Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->assertOk()
            ->assertFormSet([
                'correct_option' => 'A',
                'options.C.feedback' => 'Neden C?',
            ]);

        Livewire::test(ViewQuestion::class, ['record' => $question->getRouteKey()])->assertOk();
    }

    public function test_updating_keeps_option_ids_and_records_the_editor(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();
        $ids = $question->options->pluck('id')->all();

        $this->actingAs($this->admin);

        Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->fillForm($this->multipleChoiceInput(['correct_option' => 'B']))
            ->call('save')
            ->assertHasNoFormErrors();

        $question->refresh();

        $this->assertSame($ids, $question->options->pluck('id')->all());
        $this->assertSame('B', $question->correctOption->letter());
        $this->assertSame(1, $question->options->where('is_correct', true)->count());
        $this->assertSame($this->admin->id, $question->updated_by_id);
    }

    public function test_changing_type_replaces_the_option_set(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->fillForm(['type' => QuestionType::TrueFalse->value, 'correct_answer' => 'false'])
            ->call('save')
            ->assertHasNoFormErrors();

        $question->refresh();

        $this->assertSame(['Doğru', 'Yanlış'], $question->options->pluck('option_text')->all());
        $this->assertSame('Yanlış', $question->correctOption->option_text);
        $this->assertDatabaseCount('question_options', 2);
    }

    // Workflow

    public function test_question_moves_through_the_review_workflow(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();

        $this->actingAs($this->admin);

        $page = Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->assertActionVisible('submitForReview')
            ->assertActionHidden('approve')
            ->assertActionHidden('deprecate')
            ->callAction('submitForReview');

        $this->assertSame(QuestionStatus::Review, $question->refresh()->status);

        $page->assertActionVisible('requestChanges')->callAction('approve');
        $this->assertSame(QuestionStatus::Approved, $question->refresh()->status);

        $page->assertActionHidden('submitForReview')->callAction('deprecate');
        $this->assertSame(QuestionStatus::Deprecated, $question->refresh()->status);

        $page->assertActionHidden('approve')->assertActionHidden('deprecate');
    }

    public function test_review_can_send_a_question_back_to_draft(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->inReview()->create();

        $this->actingAs($this->admin);

        Livewire::test(ViewQuestion::class, ['record' => $question->getRouteKey()])
            ->callAction('requestChanges');

        $this->assertSame(QuestionStatus::Draft, $question->refresh()->status);
    }

    public function test_question_without_a_solution_cannot_be_approved(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->withoutExplanation()->inReview()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->callAction('approve')
            ->assertNotified('A question needs a step-by-step solution before it can be approved.');

        $this->assertSame(QuestionStatus::Review, $question->refresh()->status);
    }

    public function test_question_with_incomplete_options_cannot_be_approved(): void
    {
        $question = Question::factory()->for($this->topic)->inReview()->create();

        $this->expectException(DomainException::class);

        $question->approve();
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $draft = Question::factory()->for($this->topic)->withOptions()->create();
        $deprecated = Question::factory()->for($this->topic)->withOptions()->deprecated()->create();

        foreach ([fn () => $draft->approve(), fn () => $draft->deprecate(), fn () => $deprecated->submitForReview()] as $transition) {
            try {
                $transition();
                $this->fail('The transition should have been rejected.');
            } catch (DomainException) {
                // expected
            }
        }

        $this->assertSame(QuestionStatus::Draft, $draft->refresh()->status);
        $this->assertSame(QuestionStatus::Deprecated, $deprecated->refresh()->status);
    }

    // Domain

    public function test_approved_scope_returns_only_approved_and_active_questions(): void
    {
        $visible = Question::factory()->for($this->topic)->approved()->create();
        Question::factory()->for($this->topic)->approved()->inactive()->create();
        Question::factory()->for($this->topic)->inReview()->create();
        Question::factory()->for($this->topic)->create();

        $this->assertSame([$visible->id], Question::approved()->pluck('id')->all());
        $this->assertSame(4, $this->topic->questions()->count());
    }

    public function test_sync_options_removes_surplus_rows(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();

        $question->syncOptions([
            ['option_text' => 'Doğru', 'is_correct' => false],
            ['option_text' => 'Yanlış', 'is_correct' => true],
        ]);

        $this->assertSame([1, 2], $question->options->pluck('order')->all());
        $this->assertFalse($question->hasValidOptions());
        $this->assertDatabaseCount('question_options', 2);
    }

    public function test_option_order_is_unique_within_a_question(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();

        $this->expectException(QueryException::class);

        $question->options()->create(['option_text' => 'Fazla', 'order' => 1]);
    }

    public function test_admin_can_soft_delete_and_force_delete_cascades_to_options(): void
    {
        $question = Question::factory()->for($this->topic)->withOptions()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($question);
        $this->assertDatabaseCount('question_options', 5);

        $question->forceDelete();

        $this->assertDatabaseCount('question_options', 0);
    }

    public function test_topic_with_questions_cannot_be_force_deleted(): void
    {
        Question::factory()->for($this->topic)->create();

        try {
            $this->topic->forceDelete();
            $this->fail('Force-deleting a topic that has questions should be blocked.');
        } catch (QueryException) {
            $this->assertDatabaseHas('topics', ['id' => $this->topic->id]);
            $this->assertDatabaseCount('questions', 1);
        }
    }

    public function test_deleting_a_learning_objective_keeps_the_question(): void
    {
        $objective = LearningObjective::factory()->for($this->topic)->create();
        $question = Question::factory()->for($this->topic)->create(['learning_objective_id' => $objective->id]);

        $objective->forceDelete();

        $this->assertNull($question->refresh()->learning_objective_id);
    }
}

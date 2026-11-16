<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Practice;

use App\Enums\PracticeSessionType;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\PracticeSession;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 1]);
        $grade = Grade::factory()->for($program)->number(10)->active()->create();
        $unit = Unit::factory()->for($grade)->code('10.3')->active()->create();

        $this->topic = Topic::factory()->for($unit)->published()->create(['name' => 'Asit Baz Tepkimeleri', 'slug' => 'asit-baz']);
        $this->student = User::factory()->create();
    }

    /**
     * @return Collection<int, Question>
     */
    private function approvedQuestions(int $count, ?Topic $topic = null): Collection
    {
        return Question::factory()->for($topic ?? $this->topic)->approved()->withOptions()->count($count)->create();
    }

    private function topicUrl(): string
    {
        return route('curriculum.topic', ['program' => 'lise-kimya', 'grade' => 10, 'unit' => '10.3', 'topic' => 'asit-baz']);
    }

    private function start(int $size = 10, bool $timed = false): PracticeSession
    {
        return PracticeSession::startForTopic($this->student, $this->topic, $size, $timed);
    }

    // Topic page panel

    public function test_topic_page_says_questions_are_being_prepared_when_none_is_approved(): void
    {
        Question::factory()->for($this->topic)->withOptions()->create();

        $this->get($this->topicUrl())
            ->assertOk()
            ->assertSee('Bu konunun soruları hazırlanıyor.')
            ->assertDontSee('Süre tut');
    }

    public function test_guest_is_sent_to_login_and_back_to_the_topic(): void
    {
        $this->approvedQuestions(3);

        $this->get($this->topicUrl())
            ->assertOk()
            ->assertSee(route('practice.intent', $this->topic->id))
            ->assertDontSee('Süre tut');

        $this->get(route('practice.intent', $this->topic->id))->assertRedirect(route('login'));
        $this->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10])->assertRedirect(route('login'));

        $this->actingAs($this->student)
            ->get(route('practice.intent', $this->topic->id))
            ->assertRedirect($this->topicUrl());
    }

    public function test_student_sees_only_the_sizes_the_topic_can_fill(): void
    {
        $this->approvedQuestions(12);

        $this->actingAs($this->student)
            ->get($this->topicUrl())
            ->assertOk()
            ->assertSee('Süre tut (soru başına 1,5 dk)')
            ->assertSee('value="10"', false)
            ->assertSee('Tümü (12)')
            ->assertDontSee('value="40"', false);
    }

    public function test_size_choices(): void
    {
        $this->assertSame([], PracticeSession::sizeChoices(0));
        $this->assertSame([['size' => 10, 'count' => 3]], PracticeSession::sizeChoices(3));
        $this->assertSame([['size' => 10, 'count' => 10]], PracticeSession::sizeChoices(10));
        $this->assertSame([['size' => 10, 'count' => 10], ['size' => 20, 'count' => 15]], PracticeSession::sizeChoices(15));
        $this->assertSame([10, 20, 40], array_column(PracticeSession::sizeChoices(90), 'count'));
    }

    // Starting

    public function test_student_starts_a_session_with_what_the_topic_has(): void
    {
        $questions = $this->approvedQuestions(3);
        Question::factory()->for($this->topic)->withOptions()->create();
        Question::factory()->for($this->topic)->approved()->inactive()->withOptions()->create();

        $response = $this->actingAs($this->student)
            ->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10]);

        $session = PracticeSession::query()->sole();

        $response->assertRedirect(route('practice.show', $session->id));

        $this->assertSame($this->student->id, $session->user_id);
        $this->assertSame($this->topic->id, $session->topic_id);
        $this->assertSame(PracticeSessionType::Topic, $session->type);
        $this->assertSame(3, $session->question_count);
        $this->assertNull($session->time_limit_seconds);
        $this->assertNull($session->completed_at);
        $this->assertEqualsCanonicalizing($questions->modelKeys(), $session->questionIds());
    }

    public function test_size_limits_the_session_and_timed_sets_the_limit(): void
    {
        $this->approvedQuestions(12);

        $this->actingAs($this->student)
            ->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10, 'timed' => '1']);

        $session = PracticeSession::query()->sole();

        $this->assertSame(10, $session->question_count);
        $this->assertSame(900, $session->time_limit_seconds);
    }

    public function test_starting_again_continues_the_open_session(): void
    {
        $this->approvedQuestions(3);
        $session = $this->start();

        $this->actingAs($this->student)
            ->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10])
            ->assertRedirect(route('practice.show', $session->id));

        $this->assertDatabaseCount('practice_sessions', 1);

        $this->actingAs($this->student)->get($this->topicUrl())->assertSee('Kaldığın yerden devam et');
    }

    public function test_unanswered_questions_come_first_then_previously_wrong_ones(): void
    {
        [$right, $wrong, $new] = $this->approvedQuestions(3)->all();

        QuestionAttempt::factory()->for($this->student)->for($right)->create(['answered_at' => now()->subDay()]);
        QuestionAttempt::factory()->for($this->student)->for($wrong)->create(['answered_at' => now()->subDays(2)]);
        QuestionAttempt::factory()->for($this->student)->for($wrong)->wrong()->create(['answered_at' => now()->subDay()]);

        $this->assertSame([$new->id, $wrong->id, $right->id], $this->start()->questionIds());
    }

    public function test_invalid_start_requests_are_rejected(): void
    {
        $this->approvedQuestions(2);
        $draftTopic = Topic::factory()->for($this->topic->unit)->draft()->create();
        $this->approvedQuestions(2, $draftTopic);

        $this->actingAs($this->student);

        $this->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 7])->assertSessionHasErrors('size');
        $this->post(route('practice.store'), ['topic_id' => $draftTopic->id, 'size' => 10])->assertNotFound();
        $this->post(route('practice.store'), ['topic_id' => 999999, 'size' => 10])->assertNotFound();

        $this->assertDatabaseCount('practice_sessions', 0);
    }

    public function test_topic_without_approved_questions_cannot_be_started(): void
    {
        $this->actingAs($this->student)
            ->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10])
            ->assertRedirect($this->topicUrl())
            ->assertSessionHas('status', 'Bu konunun soruları hazırlanıyor.');

        $this->expectException(DomainException::class);

        $this->start();
    }

    // Solving

    public function test_question_page_hides_the_answer_until_it_is_checked(): void
    {
        $objective = LearningObjective::factory()->for($this->topic)->create(['code' => 'KIM.10.3.2.3']);
        $question = Question::factory()->for($this->topic)->approved()->create([
            'learning_objective_id' => $objective->id,
            'content' => '<p onclick="x()">H<sub>2</sub>SO<sub>4</sub> kaç gram?</p><script>alert(1)</script>',
            'explanation' => '<ol><li>Gizli çözüm adımı</li></ol>',
        ]);
        $question->syncOptions([
            ['option_text' => '<p>4 g</p>', 'is_correct' => false, 'feedback' => 'Gizli geri bildirim'],
            ['option_text' => '<p>8 g</p>', 'is_correct' => false],
            ['option_text' => '<p>12 g</p>', 'is_correct' => false],
            ['option_text' => '<p>16 g</p>', 'is_correct' => true],
            ['option_text' => '<p>20 g</p>', 'is_correct' => false],
        ]);
        $session = $this->start();

        $this->actingAs($this->student)
            ->get(route('practice.show', $session->id))
            ->assertOk()
            ->assertSee('Soru 1 / 1')
            ->assertSee('KIM.10.3.2.3')
            ->assertSee('H<sub>2</sub>SO<sub>4</sub> kaç gram?', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('alert(1)', false)
            ->assertSee('Kontrol et')
            ->assertDontSee('Gizli çözüm adımı')
            ->assertDontSee('Gizli geri bildirim')
            ->assertDontSee('Doğru cevap');
    }

    public function test_wrong_answer_shows_the_solution_and_the_option_feedback(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $question->update(['explanation' => '<ol><li>Mol oranı 1 : 2</li></ol>']);
        $question->options[1]->update(['feedback' => 'Katsayı 2 atlanmış.']);
        $session = $this->start();
        $this->travel(25)->seconds();

        $this->actingAs($this->student)->get(route('practice.show', [$session->id, 1]));
        $this->travel(40)->seconds();

        $this->actingAs($this->student)
            ->post(route('practice.answer', [$session->id, 1]), ['option' => $question->options[1]->id])
            ->assertRedirect(route('practice.show', [$session->id, 1]));

        $attempt = QuestionAttempt::query()->sole();

        $this->assertSame($this->student->id, $attempt->user_id);
        $this->assertSame($question->id, $attempt->question_id);
        $this->assertSame($session->id, $attempt->practice_session_id);
        $this->assertSame($question->options[1]->id, $attempt->selected_option_id);
        $this->assertFalse($attempt->is_correct);
        $this->assertSame(40, $attempt->time_spent_seconds);
        $this->assertSame(1, $attempt->attempt_number);
        $this->assertNull($attempt->quality);

        $this->actingAs($this->student)
            ->get(route('practice.show', [$session->id, 1]))
            ->assertOk()
            ->assertSee('Yanlış. Doğru cevap: A · Option 1')
            ->assertSee('Senin cevabın')
            ->assertSee('Mol oranı 1 : 2')
            ->assertSee('Katsayı 2 atlanmış.')
            ->assertDontSee('Kontrol et')
            ->assertSee('Oturumu bitir');
    }

    public function test_option_feedback_is_rendered_through_the_allow_list(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $question->options[1]->update(['feedback' => '<p onclick="x()">H<sub>2</sub>O 18 g/mol</p><script>alert(1)</script>']);
        $session = $this->start();
        $session->answer($question, $question->options[1]);

        $this->actingAs($this->student)
            ->get(route('practice.show', [$session->id, 1]))
            ->assertOk()
            ->assertSee('<p>H<sub>2</sub>O 18 g/mol</p>', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('alert(1)', false);

        $this->actingAs($this->student)->post(route('practice.finish', $session->id));

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertSee('<p>H<sub>2</sub>O 18 g/mol</p>', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('alert(1)', false);
    }

    public function test_correct_answer_is_recorded_and_cannot_be_changed(): void
    {
        $question = $this->approvedQuestions(2)->first();
        $session = $this->start();
        $number = array_search($question->id, $session->questionIds(), true) + 1;

        $this->actingAs($this->student)
            ->post(route('practice.answer', [$session->id, $number]), ['option' => $question->options[0]->id]);
        $this->actingAs($this->student)
            ->post(route('practice.answer', [$session->id, $number]), ['option' => $question->options[3]->id]);

        $attempt = QuestionAttempt::query()->sole();

        $this->assertTrue($attempt->is_correct);
        $this->assertSame($question->options[0]->id, $attempt->selected_option_id);

        $this->actingAs($this->student)
            ->get(route('practice.show', [$session->id, $number]))
            ->assertSee('Doğru.')
            ->assertSee('Sonraki soru');
    }

    public function test_an_option_of_another_question_or_no_option_is_rejected(): void
    {
        [$first, $second] = $this->approvedQuestions(2)->all();
        $session = $this->start();
        $number = array_search($first->id, $session->questionIds(), true) + 1;

        $this->actingAs($this->student);

        $this->post(route('practice.answer', [$session->id, $number]), ['option' => $second->options[0]->id])
            ->assertSessionHasErrors(['option' => 'Bir şık seç ya da soruyu boş bırak.']);
        $this->post(route('practice.answer', [$session->id, $number]), [])->assertSessionHasErrors('option');
        $this->post(route('practice.answer', [$session->id, 9]), ['option' => $first->options[0]->id])->assertNotFound();

        $this->assertDatabaseCount('question_attempts', 0);
    }

    public function test_default_page_is_the_first_unanswered_question_and_blank_moves_on(): void
    {
        $this->approvedQuestions(3);
        $session = $this->start();
        $ids = $session->questionIds();

        $session->answer(Question::find($ids[0]), Question::find($ids[0])->options[0]);

        $this->actingAs($this->student)
            ->get(route('practice.show', $session->id))
            ->assertOk()
            ->assertSee('Soru 2 / 3')
            ->assertSee(route('practice.show', [$session->id, 3]))
            ->assertSee('Boş bırak')
            ->assertSee('1 / 3 soru cevaplandı');

        $this->assertSame(2, $session->firstUnansweredNumber());
    }

    public function test_student_can_flag_and_unflag_a_question(): void
    {
        $this->approvedQuestions(2);
        $session = $this->start();

        $this->actingAs($this->student)
            ->post(route('practice.flag', [$session->id, 2]))
            ->assertRedirect(route('practice.show', [$session->id, 2]));

        $this->assertSame([$session->questionIds()[1]], $session->refresh()->flaggedIds());

        $this->actingAs($this->student)->get(route('practice.show', [$session->id, 2]))->assertSee('İşareti kaldır');
        $this->actingAs($this->student)->post(route('practice.flag', [$session->id, 2]));

        $this->assertSame([], $session->refresh()->flaggedIds());
    }

    public function test_another_student_cannot_reach_the_session(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $session = $this->start();

        $this->actingAs(User::factory()->create());

        $this->get(route('practice.show', $session->id))->assertNotFound();
        $this->get(route('practice.summary', $session->id))->assertNotFound();
        $this->post(route('practice.answer', [$session->id, 1]), ['option' => $question->options[0]->id])->assertNotFound();
        $this->post(route('practice.flag', [$session->id, 1]))->assertNotFound();
        $this->post(route('practice.finish', $session->id))->assertNotFound();

        $this->assertDatabaseCount('question_attempts', 0);
        $this->assertNull($session->refresh()->completed_at);
    }

    public function test_practice_requires_login(): void
    {
        $this->approvedQuestions(1);
        $session = $this->start();

        $this->get(route('practice.show', $session->id))->assertRedirect(route('login'));
        $this->get(route('practice.summary', $session->id))->assertRedirect(route('login'));
        $this->post(route('practice.finish', $session->id))->assertRedirect(route('login'));
    }

    // Completion and summary

    public function test_finishing_stores_the_totals_and_shows_the_summary(): void
    {
        $objective = LearningObjective::factory()->for($this->topic)->create(['code' => 'KIM.10.3.2.1', 'description' => 'Nötralleşme tepkimeleri']);
        $this->approvedQuestions(4)->each->update(['learning_objective_id' => $objective->id]);
        $session = $this->start();
        $questions = $session->questions();
        $questions[1]->options[2]->update(['feedback' => 'Bu şık neden çıktı']);

        $session->answer($questions[0], $questions[0]->options[0]);
        $session->answer($questions[1], $questions[1]->options[2]);
        $session->answer($questions[2], $questions[2]->options[0]);
        $this->travel(300)->seconds();

        $this->actingAs($this->student)
            ->post(route('practice.finish', $session->id))
            ->assertRedirect(route('practice.summary', $session->id));

        $session->refresh();

        $this->assertNotNull($session->completed_at);
        $this->assertSame(2, $session->correct_count);
        $this->assertSame(1, $session->skipped_count);
        $this->assertSame(1, $session->wrongCount());
        $this->assertSame('50.00', $session->score);
        $this->assertSame(300, $session->durationSeconds());

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertSee('Oturum tamamlandı')
            ->assertSeeInOrder(['5:00', '1:15', 'hedef 1:30'])
            ->assertSee('KIM.10.3.2.1')
            ->assertSee('Nötralleşme tepkimeleri')
            ->assertSee('2/4')
            ->assertSee('Yanlışları gözden geçir')
            ->assertSee('C seçildi')
            ->assertSee('Boş bırakıldı')
            ->assertSee('Bu şık neden çıktı')
            ->assertSee('Diğer 2 soru doğru.')
            ->assertSee($this->topicUrl());
    }

    public function test_completed_session_is_read_only(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $session = $this->start();
        $session->complete();

        $this->actingAs($this->student);

        $this->get(route('practice.show', $session->id))->assertRedirect(route('practice.summary', $session->id));
        $this->post(route('practice.answer', [$session->id, 1]), ['option' => $question->options[0]->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('question_attempts', 0);

        try {
            $session->answer($question, $question->options[0]);
            $this->fail('A completed session should not accept answers.');
        } catch (DomainException) {
            $this->assertSame(1, $session->refresh()->skipped_count);
        }
    }

    public function test_summary_of_an_open_session_goes_back_to_the_questions(): void
    {
        $this->approvedQuestions(1);
        $session = $this->start();

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertRedirect(route('practice.show', $session->id));
    }

    public function test_all_correct_summary_has_no_review_list(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $session = $this->start();
        $session->answer($question, $question->options[0]);
        $session->complete();

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertDontSee('Yanlışları gözden geçir')
            ->assertSee('Konuya dön')
            ->assertSee('Kazanım belirtilmemiş');
    }

    // Timer

    public function test_timed_session_shows_the_countdown(): void
    {
        $this->approvedQuestions(2);
        $session = $this->start(timed: true);
        $this->travel(30)->seconds();

        $this->actingAs($this->student)
            ->get(route('practice.show', $session->id))
            ->assertOk()
            ->assertSee('data-seconds="150"', false)
            ->assertSee('az kaldı');
    }

    public function test_untimed_session_has_no_countdown(): void
    {
        $this->approvedQuestions(2);

        $this->actingAs($this->student)
            ->get(route('practice.show', $this->start()->id))
            ->assertOk()
            ->assertDontSee('data-countdown', false);
    }

    public function test_session_ends_when_the_time_is_up(): void
    {
        $questions = $this->approvedQuestions(2);
        $session = $this->start(timed: true);
        $session->answer($questions[0], $questions[0]->options[0]);
        $this->travel(181)->seconds();

        $this->actingAs($this->student)
            ->post(route('practice.answer', [$session->id, 2]), ['option' => $questions[1]->options[0]->id])
            ->assertRedirect(route('practice.summary', $session->id));

        $session->refresh();

        $this->assertTrue($session->completed_at->equalTo($session->started_at->copy()->addSeconds(180)));
        $this->assertSame(1, $session->correct_count);
        $this->assertSame(1, $session->skipped_count);
        $this->assertDatabaseCount('question_attempts', 1);

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertSee('süre doldu');
    }

    public function test_starting_after_an_expired_session_opens_a_new_one(): void
    {
        $this->approvedQuestions(2);
        $expired = $this->start(timed: true);
        $this->travel(200)->seconds();

        $new = $this->start();

        $this->assertNotSame($expired->id, $new->id);
        $this->assertNotNull($expired->refresh()->completed_at);
    }

    // Integrity

    public function test_answered_question_cannot_be_force_deleted_and_user_deletion_cascades(): void
    {
        $question = $this->approvedQuestions(1)->first();
        $session = $this->start();
        $session->answer($question, $question->options[0]);

        try {
            $question->forceDelete();
            $this->fail('Force-deleting an answered question should be blocked.');
        } catch (QueryException) {
            $this->assertDatabaseCount('questions', 1);
        }

        $this->student->delete();

        $this->assertDatabaseCount('practice_sessions', 0);
        $this->assertDatabaseCount('question_attempts', 0);
    }
}

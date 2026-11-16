<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Progress;

use App\Enums\MasteryLevel;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\PracticeSession;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Topic;
use App\Models\TopicMastery;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProgressTest extends TestCase
{
    use RefreshDatabase;

    protected Program $program;

    protected Grade $grade;

    protected Unit $unit;

    protected Topic $topic;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 1]);
        $this->grade = Grade::factory()->for($this->program)->number(10)->active()->create();
        $this->unit = Unit::factory()->for($this->grade)->code('10.3')->active()->create();
        $this->topic = Topic::factory()->for($this->unit)->published()->create(['name' => 'Asit Baz Tepkimeleri', 'slug' => 'asit-baz']);
        $this->student = User::factory()->create(['grade_id' => $this->grade->id]);
    }

    /**
     * Give the student standalone answers in a topic, oldest first, one minute apart.
     *
     * @param  list<bool>  $results
     */
    private function answers(array $results, ?Topic $topic = null, ?User $user = null, ?LearningObjective $objective = null, ?Carbon $from = null): void
    {
        $topic ??= $this->topic;
        $user ??= $this->student;
        $from ??= Carbon::now()->subMinutes(count($results));

        foreach ($results as $index => $isCorrect) {
            QuestionAttempt::factory()->create([
                'user_id' => $user->id,
                'question_id' => Question::factory()->for($topic)->approved()->create(['learning_objective_id' => $objective?->id])->id,
                'is_correct' => $isCorrect,
                'answered_at' => $from->copy()->addMinutes($index),
            ]);
        }

        TopicMastery::refreshFor($user->id, $topic->id);
    }

    private function mastery(?Topic $topic = null): ?TopicMastery
    {
        return TopicMastery::query()->where('user_id', $this->student->id)->where('topic_id', ($topic ?? $this->topic)->id)->first();
    }

    // Mastery calculation

    public function test_answering_in_a_session_creates_and_updates_the_mastery(): void
    {
        $questions = Question::factory()->for($this->topic)->approved()->withOptions()->count(6)->create();
        $session = PracticeSession::startForTopic($this->student, $this->topic, 10, false);

        $this->assertNull($this->mastery());

        // Option 0 is the correct one (QuestionFactory::withOptions).
        foreach ($session->questions()->take(4) as $question) {
            $session->answer($question, $question->options[0]);
        }

        $mastery = $this->mastery();
        $this->assertSame(MasteryLevel::InsufficientData, $mastery->mastery_level);
        $this->assertSame(4, $mastery->total_attempts);
        $this->assertSame(4, $mastery->correct_attempts);
        $this->assertNull($mastery->displayPercentage());
        $this->assertNotNull($mastery->last_practiced_at);

        $fifth = $session->questions()[4];
        $session->answer($fifth, $fifth->options[1]);

        $mastery = $this->mastery();
        $this->assertSame('80.00', $mastery->mastery_percentage);
        $this->assertSame(MasteryLevel::Mastered, $mastery->mastery_level);
        $this->assertSame(80, $mastery->displayPercentage());

        // Answering the same question again changes nothing.
        $session->answer($fifth, $fifth->options[0]);
        $this->assertSame(5, $this->mastery()->total_attempts);
        $this->assertSame(1, TopicMastery::query()->count());
        $this->assertCount(6, $questions);
    }

    public function test_mastery_looks_at_the_last_twenty_answers_only(): void
    {
        // Five old wrong answers, then twenty answers of which nine are correct.
        $this->answers([
            ...array_fill(0, 5, false),
            ...array_fill(0, 11, false),
            ...array_fill(0, 9, true),
        ]);

        $mastery = $this->mastery();

        $this->assertSame('45.00', $mastery->mastery_percentage);
        $this->assertSame(MasteryLevel::Weak, $mastery->mastery_level);
        $this->assertSame(25, $mastery->total_attempts);
        $this->assertSame(9, $mastery->correct_attempts);
    }

    public function test_mastery_is_per_student_and_per_topic(): void
    {
        $otherTopic = Topic::factory()->for($this->unit)->published()->create();
        $otherStudent = User::factory()->create();

        $this->answers(array_fill(0, 5, true));
        $this->answers(array_fill(0, 5, false), $otherTopic);
        $this->answers(array_fill(0, 5, false), null, $otherStudent);

        $this->assertSame(MasteryLevel::Mastered, $this->mastery()->mastery_level);
        $this->assertSame(MasteryLevel::Weak, $this->mastery($otherTopic)->mastery_level);
        $this->assertSame(3, TopicMastery::query()->count());
    }

    public function test_answers_to_deleted_questions_still_count(): void
    {
        $this->answers(array_fill(0, 5, true));
        Question::query()->where('topic_id', $this->topic->id)->first()->delete();

        $this->assertSame(5, TopicMastery::refreshFor($this->student->id, $this->topic->id)->total_attempts);
    }

    public function test_refresh_stores_nothing_without_answers(): void
    {
        $this->assertNull(TopicMastery::refreshFor($this->student->id, $this->topic->id));
        $this->assertSame(0, TopicMastery::query()->count());
    }

    public function test_spaced_repetition_columns_keep_their_defaults(): void
    {
        $this->answers(array_fill(0, 5, true));

        $mastery = $this->mastery()->fresh();

        $this->assertNull($mastery->next_review_at);
        $this->assertSame('2.50', $mastery->ease_factor);
        $this->assertSame(0, $mastery->interval_days);
        $this->assertSame(0, $mastery->repetitions);
        $this->assertFalse($mastery->is_due);
    }

    public function test_mastery_rows_are_removed_with_the_student(): void
    {
        $this->answers(array_fill(0, 5, true));

        $this->student->forceDelete();

        $this->assertSame(0, TopicMastery::query()->count());
    }

    // Session summary

    public function test_session_keeps_the_mastery_before_and_after(): void
    {
        // 2 of 5 correct before the session: 40%.
        $this->answers([true, true, false, false, false]);

        Question::factory()->for($this->topic)->approved()->withOptions()->count(5)->create();
        $session = PracticeSession::startForTopic($this->student, $this->topic, 10, false);

        $this->assertSame(['before' => 40, 'after' => null], $session->masteryChange());

        foreach ($session->questions()->filter(fn (Question $question): bool => $question->options->isNotEmpty())->take(5) as $question) {
            $session->answer($question, $question->options[0]);
        }

        $session->complete();

        // 7 of 10 correct after it: 70%.
        $this->assertSame(['before' => 40, 'after' => 70], $session->fresh()->masteryChange());

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertSeeInOrder(['Konu ustalığı', '%40', '%70', 'son 20 sorudaki doğruluk']);
    }

    public function test_summary_hides_mastery_without_enough_data(): void
    {
        Question::factory()->for($this->topic)->approved()->withOptions()->count(2)->create();
        $session = PracticeSession::startForTopic($this->student, $this->topic, 10, false);
        $session->answer($session->questions()[0], $session->questions()[0]->options[0]);
        $session->complete();

        $this->assertSame(['before' => null, 'after' => null], $session->fresh()->masteryChange());

        $this->actingAs($this->student)
            ->get(route('practice.summary', $session->id))
            ->assertOk()
            ->assertDontSee('Konu ustalığı');
    }

    // Topic page

    public function test_topic_page_shows_the_students_mastery(): void
    {
        $url = route('curriculum.topic', ['program' => 'lise-kimya', 'grade' => 10, 'unit' => '10.3', 'topic' => 'asit-baz']);

        $this->actingAs($this->student)->get($url)->assertOk()->assertDontSee('Ustalık');

        $this->answers([true, true]);
        $this->actingAs($this->student)->get($url)->assertSeeInOrder(['Ustalık', 'yeterli veri yok (2/5 soru)']);

        $this->answers([true, true, false]);
        $this->actingAs($this->student)->get($url)->assertSeeInOrder(['Ustalık', '%80', 'Usta']);

        $this->app['auth']->forgetGuards();
        $this->get($url)->assertOk()->assertDontSee('Ustalık');
    }

    // Progress page

    public function test_progress_page_requires_sign_in(): void
    {
        $this->get(route('progress.index'))->assertRedirect(route('login'));
    }

    public function test_menu_lists_progress_for_signed_in_students_only(): void
    {
        $this->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]))
            ->assertOk()
            ->assertDontSee(route('progress.index'));

        $this->actingAs($this->student)
            ->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]))
            ->assertOk()
            ->assertSee(route('progress.index'));
    }

    public function test_progress_page_has_an_empty_state(): void
    {
        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSee('Henüz soru çözmedin.')
            ->assertSee('Müfredata git')
            ->assertDontSee('Konu ustalığı');
    }

    public function test_progress_page_shows_totals_and_topic_mastery(): void
    {
        $second = Topic::factory()->for($this->unit)->published()->create(['name' => 'Tuzlar', 'order' => 99]);
        Topic::factory()->for($this->unit)->published()->create(['name' => 'Hiç Çalışılmamış Konu']);
        Topic::factory()->for($this->unit)->draft()->create();

        // 17 of 20 correct: 85%.
        $this->answers([...array_fill(0, 3, false), ...array_fill(0, 17, true)]);
        $this->answers([true, false], $second);

        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSeeInOrder(['çözülen soru', '22', 'genel doğruluk', '%82', 'konu başlandı / 3', '2'])
            ->assertSeeInOrder(['Asit Baz Tepkimeleri', '%85', 'Usta', 'Tuzlar', 'Yeterli veri yok (2/5 soru)'])
            ->assertSee(route('curriculum.topic', ['program' => 'lise-kimya', 'grade' => 10, 'unit' => '10.3', 'topic' => 'asit-baz']))
            ->assertDontSee('Hiç Çalışılmamış Konu');
    }

    public function test_topic_mastery_is_listed_one_grade_at_a_time(): void
    {
        $grade11 = Grade::factory()->for($this->program)->number(11)->active()->create();
        $unit11 = Unit::factory()->for($grade11)->code('11.2')->active()->create();
        $topic11 = Topic::factory()->for($unit11)->published()->create(['name' => 'Gazlar']);

        $this->answers(array_fill(0, 5, true));
        $this->answers(array_fill(0, 5, false), $topic11);

        // The student's own grade opens first.
        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertSee('Asit Baz Tepkimeleri')
            ->assertDontSee('Gazlar')
            ->assertSee(route('progress.index', ['sinif' => 11]));

        $this->actingAs($this->student)
            ->get(route('progress.index', ['sinif' => 11]))
            ->assertSeeInOrder(['Gazlar', '%0', 'Zayıf'])
            ->assertDontSee('Asit Baz Tepkimeleri');

        // An unknown grade falls back to the student's own.
        $this->actingAs($this->student)
            ->get(route('progress.index', ['sinif' => 99]))
            ->assertOk()
            ->assertSee('Asit Baz Tepkimeleri');
    }

    public function test_grade_without_answers_says_so(): void
    {
        Grade::factory()->for($this->program)->number(11)->active()->create();
        $this->answers([true]);

        $this->actingAs($this->student)
            ->get(route('progress.index', ['sinif' => 11]))
            ->assertOk()
            ->assertSee('11. sınıf konularında henüz soru çözmedin.');
    }

    public function test_weak_objectives_need_five_answers_and_less_than_half_correct(): void
    {
        $weak = LearningObjective::factory()->for($this->topic)->active()->create(['code' => 'KIM.10.3.2.3', 'description' => 'Nötralleşmede madde miktarı']);
        $weaker = LearningObjective::factory()->for($this->topic)->active()->create(['code' => 'KIM.10.3.2.4', 'description' => 'Tuzların sınıflandırılması']);
        $half = LearningObjective::factory()->for($this->topic)->active()->create(['code' => 'KIM.10.3.2.5', 'description' => 'Yarı yarıya doğru']);
        $few = LearningObjective::factory()->for($this->topic)->active()->create(['code' => 'KIM.10.3.2.6', 'description' => 'Az soru çözülmüş']);

        $this->answers([true, true, false, false, false, false], null, null, $weak);
        $this->answers([false, false, false, false, false], null, null, $weaker);
        $this->answers([true, true, true, false, false, false], null, null, $half);
        $this->answers([false, false, false, false], null, null, $few);

        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSeeInOrder(['Zayıf kazanımlar', 'KIM.10.3.2.4', '0/5', 'KIM.10.3.2.3', '2/6', 'Çalış'])
            ->assertDontSee('KIM.10.3.2.5')
            ->assertDontSee('KIM.10.3.2.6');
    }

    public function test_weak_objective_recovers_with_recent_correct_answers(): void
    {
        $objective = LearningObjective::factory()->for($this->topic)->active()->create(['code' => 'KIM.10.3.2.3']);

        $this->answers([...array_fill(0, 15, false), ...array_fill(0, 15, true)], null, null, $objective);

        // 15 of the last 20 are correct, although only half of all answers are.
        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSee('Şu an zayıf görünen bir kazanımın yok.')
            ->assertDontSee('KIM.10.3.2.3');
    }

    public function test_calendar_counts_days_in_turkish_time(): void
    {
        // Monday 16 November 2026, 12:00 in Istanbul.
        $this->travelTo(Carbon::parse('2026-11-16 09:00:00', 'UTC'));

        $this->answers([true], null, null, null, Carbon::parse('2026-11-16 08:00:00', 'UTC'));
        // 22:30 UTC on the 14th is 01:30 on the 15th in Istanbul.
        $this->answers([true, true], null, null, null, Carbon::parse('2026-11-14 22:30:00', 'UTC'));
        // First day of the window: 20 October, 00:30 in Istanbul.
        $this->answers([true], null, null, null, Carbon::parse('2026-10-19 21:30:00', 'UTC'));
        // One day too old.
        $this->answers([true], null, null, null, Carbon::parse('2026-10-19 20:30:00', 'UTC'));

        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSeeInOrder(['Süreklilik', '3 / 28', 'gün çalıştın'])
            // The window starts on a Tuesday and ends today.
            ->assertSeeInOrder(['Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz', 'Pzt']);
    }

    public function test_progress_page_shows_only_the_students_own_data(): void
    {
        $other = User::factory()->create(['grade_id' => $this->grade->id]);
        $this->answers(array_fill(0, 5, true), null, $other);

        $this->actingAs($this->student)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSee('Henüz soru çözmedin.');
    }

    public function test_progress_page_works_without_a_published_program(): void
    {
        $this->program->delete();

        $this->actingAs($this->student)->get(route('progress.index'))->assertOk();
    }
}

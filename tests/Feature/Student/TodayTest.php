<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Models\Grade;
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
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TodayTest extends TestCase
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

        // Monday 16 November 2026, 09:00 in Turkey.
        Carbon::setTestNow(Carbon::parse('2026-11-16 09:00', 'Europe/Istanbul'));

        $this->program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 1]);
        $this->grade = Grade::factory()->for($this->program)->number(10)->active()->create(['order' => 10]);
        $this->unit = Unit::factory()->for($this->grade)->code('10.3')->active()->create(['name' => 'Asitler ve Bazlar']);
        $this->topic = Topic::factory()->for($this->unit)->published()->create(['name' => 'Asit Baz Tepkimeleri', 'slug' => 'asit-baz', 'order' => 0]);
        $this->student = User::factory()->create(['name' => 'Elif Yılmaz', 'grade_id' => $this->grade->id]);
    }

    /**
     * Give the student standalone answers in a topic, oldest first, one minute apart.
     *
     * @param  list<bool>  $results
     */
    private function answers(array $results, ?Topic $topic = null, ?Carbon $from = null): void
    {
        $topic ??= $this->topic;
        $from ??= Carbon::now()->subMinutes(count($results));

        foreach ($results as $index => $isCorrect) {
            QuestionAttempt::factory()->create([
                'user_id' => $this->student->id,
                'question_id' => Question::factory()->for($topic)->approved()->create()->id,
                'is_correct' => $isCorrect,
                'answered_at' => $from->copy()->addMinutes($index),
            ]);
        }

        TopicMastery::refreshFor($this->student->id, $topic->id);
    }

    private function topic(string $name, int $order = 5, ?Unit $unit = null): Topic
    {
        return Topic::factory()->for($unit ?? $this->unit)->published()->create(['name' => $name, 'order' => $order]);
    }

    /** The hidden field of the form that starts the recommended session. */
    private function startsTopic(Topic $topic): string
    {
        return 'name="topic_id" value="'.$topic->id.'"';
    }

    private function today(): TestResponse
    {
        return $this->actingAs($this->student)->get(route('today'))->assertOk();
    }

    // Access and navigation

    public function test_today_requires_sign_in(): void
    {
        $this->get(route('today'))->assertRedirect(route('login'));
    }

    public function test_menu_and_logo_open_today_for_signed_in_students_only(): void
    {
        $url = route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]);

        $this->get($url)->assertOk()->assertDontSee(route('today'));

        $this->actingAs($this->student)->get($url)
            ->assertOk()
            ->assertSee('Bugün')
            ->assertSee('href="'.route('today').'"', false);
    }

    // New student (rule 1)

    public function test_new_student_chooses_where_to_start(): void
    {
        Grade::factory()->for($this->program)->number(9)->active()->create(['order' => 9]);
        Grade::factory()->for($this->program)->number(12)->inactive()->create(['order' => 12]);

        $this->today()
            ->assertSee('Hoş geldin, Elif')
            ->assertSee('Nereden başlamak istersin?')
            ->assertSee('10. Sınıf ile başla')
            ->assertSee(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]))
            ->assertSee(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 9]))
            ->assertDontSee('12. Sınıf')
            ->assertDontSee('BUGÜNÜN ÖNERİSİ')
            ->assertDontSee('Bu hafta')
            ->assertDontSee('Kaldığın yer');
    }

    public function test_new_graduate_starts_with_the_first_grade(): void
    {
        Grade::factory()->for($this->program)->number(9)->active()->create(['order' => 9]);
        $this->student = User::factory()->graduate()->create(['name' => 'Deniz Kaya']);

        $this->today()
            ->assertSee('Hoş geldin, Deniz')
            ->assertSee('9. Sınıf ile başla');
    }

    public function test_new_student_still_sees_an_unfinished_session(): void
    {
        Question::factory()->for($this->topic)->approved()->withOptions()->count(3)->create();
        $session = PracticeSession::startForTopic($this->student, $this->topic, 10, false);

        $this->today()
            ->assertSee('Hoş geldin, Elif')
            ->assertSeeInOrder(['Kaldığın yer', '10. Sınıf', 'Asitler ve Bazlar', 'Asit Baz Tepkimeleri', '0/3 soru', 'Devam et'])
            ->assertSee(route('practice.show', $session->id));
    }

    // Recommendation (rule 2)

    public function test_weak_topic_is_recommended_first(): void
    {
        $developing = $this->topic('Tuzlar');
        $weaker = $this->topic('Mol Kavramı');

        // 40%, 60% and 20%.
        $this->answers([true, true, false, false, false]);
        $this->answers([true, true, true, false, false], $developing);
        $this->answers([true, false, false, false, false], $weaker);

        $this->today()
            ->assertSee('Günaydın, Elif')
            ->assertSeeInOrder(['BUGÜNÜN ÖNERİSİ · ≈ 8 DK', '10. Sınıf · Asitler ve Bazlar', 'Mol Kavramı', 'Son 5 soruda %20 doğruluk. 10 soruluk kısa bir çalışma öneriyoruz.', 'Çalışmaya başla'])
            ->assertSee($this->startsTopic($weaker), false)
            ->assertSee('name="size" value="10"', false);
    }

    public function test_weak_topic_sentence_counts_at_most_twenty_answers(): void
    {
        // 25 answers; 9 of the last 20 are correct.
        $this->answers([...array_fill(0, 16, false), ...array_fill(0, 9, true)]);

        $this->today()
            ->assertSee('≈ 15 DK')
            ->assertSee('Son 20 soruda %45 doğruluk.');
    }

    public function test_developing_topic_is_recommended_when_none_is_weak(): void
    {
        $developing = $this->topic('Tuzlar');

        $this->answers(array_fill(0, 5, true));
        $this->answers([true, true, true, false, false], $developing);

        $this->today()
            ->assertSee('Son 5 soruda %60 doğruluk. Yetkin düzeyi için %65 gerekiyor.')
            ->assertSee($this->startsTopic($developing), false)
            ->assertDontSee('Zayıf konular');
    }

    public function test_topic_without_enough_data_is_recommended_next(): void
    {
        $two = $this->topic('Tuzlar');
        $three = $this->topic('Mol Kavramı');

        $this->answers(array_fill(0, 5, true));
        $this->answers([true, false], $two);
        $this->answers([true, false, true], $three);

        $this->today()
            ->assertSee('Bu konuda 3 soru çözdün. Ustalığının görünmesi için 5 soru gerekiyor.')
            ->assertSee($this->startsTopic($three), false);
    }

    public function test_next_topic_of_the_grade_is_recommended_in_curriculum_order(): void
    {
        $grade11 = Grade::factory()->for($this->program)->number(11)->active()->create(['order' => 11]);
        $unit11 = Unit::factory()->for($grade11)->code('11.1')->active()->create();

        $withoutQuestions = $this->topic('Sorusu Olmayan Konu', 1);
        $next = $this->topic('Tuzlar', 2);
        $later = $this->topic('Mol Kavramı', 3);
        $otherGrade = $this->topic('Modern Atom Teorisi', 0, $unit11);
        $draft = Topic::factory()->for($this->unit)->draft()->create(['order' => 1]);

        Question::factory()->for($withoutQuestions)->inReview()->create();
        Question::factory()->for($withoutQuestions)->approved()->inactive()->create();
        Question::factory()->for($draft)->approved()->create();

        foreach ([$next, $later, $otherGrade] as $topic) {
            Question::factory()->for($topic)->approved()->count(2)->create();
        }

        $this->answers(array_fill(0, 5, true));

        $this->today()
            ->assertSeeInOrder(['BUGÜNÜN ÖNERİSİ · ≈ 3 DK', 'Tuzlar', 'Bu konuda henüz soru çözmedin.'])
            ->assertSee($this->startsTopic($next), false);
    }

    public function test_least_recently_practised_topic_is_recommended_for_review(): void
    {
        $recent = $this->topic('Tuzlar');

        $this->answers(array_fill(0, 5, true), null, Carbon::parse('2026-11-10 10:00', 'UTC'));
        $this->answers(array_fill(0, 5, true), $recent);

        $this->today()
            ->assertSee('Bu konuyu en son 10 Kasım tarihinde çalıştın.')
            ->assertSee($this->startsTopic($this->topic), false);
    }

    public function test_no_recommendation_without_approved_questions(): void
    {
        QuestionAttempt::factory()->create([
            'user_id' => $this->student->id,
            'question_id' => Question::factory()->for($this->topic)->deprecated()->create()->id,
            'answered_at' => Carbon::now()->subHour(),
        ]);

        $this->today()
            ->assertSeeInOrder(['BUGÜNÜN ÖNERİSİ', 'Sorular hazırlanıyor.', 'Müfredata git'])
            ->assertDontSee('Çalışmaya başla');
    }

    // Starting and continuing (rules 3, 4 and 6)

    public function test_recommended_session_has_ten_questions_or_fewer(): void
    {
        $this->answers([true, true, false, false, false]);

        $this->actingAs($this->student)
            ->post(route('practice.store'), ['topic_id' => $this->topic->id, 'size' => 10])
            ->assertRedirect();

        $session = PracticeSession::query()->sole();

        $this->assertSame(5, $session->question_count);
        $this->assertFalse($session->isTimed());
    }

    public function test_recommendation_continues_the_unfinished_session_of_its_topic(): void
    {
        $this->answers([true, true, false, false, false]);
        $session = PracticeSession::startForTopic($this->student, $this->topic, 10, false);

        $this->today()
            ->assertSee('Kaldığın yerden devam et')
            ->assertSee(route('practice.show', $session->id))
            ->assertDontSee('Çalışmaya başla')
            ->assertDontSee('Kaldığın yer ·');
    }

    public function test_unfinished_session_of_another_topic_is_shown(): void
    {
        $other = $this->topic('Tuzlar');
        Question::factory()->for($other)->approved()->withOptions()->count(3)->create();

        $this->answers([true, true, false, false, false]);

        $session = PracticeSession::startForTopic($this->student, $other, 10, false);
        $question = $session->questions()[0];
        $session->answer($question, $question->options[0]);

        $this->today()
            ->assertSee('Çalışmaya başla')
            ->assertSeeInOrder(['Kaldığın yer · 10. Sınıf · Asitler ve Bazlar', 'Tuzlar', '1/3 soru', 'Devam et'])
            ->assertSee(route('practice.show', $session->id));
    }

    public function test_session_that_ran_out_of_time_is_not_shown(): void
    {
        $other = $this->topic('Tuzlar');
        Question::factory()->for($other)->approved()->withOptions()->count(3)->create();

        $this->answers([true, true, false, false, false]);
        $session = PracticeSession::startForTopic($this->student, $other, 10, true);

        Carbon::setTestNow(Carbon::now()->addHours(2));

        $this->today()->assertDontSee('Kaldığın yer');

        $this->assertTrue($session->fresh()->isCompleted());
    }

    // Weak topics (rule 7)

    public function test_three_weakest_topics_are_listed(): void
    {
        $twenty = $this->topic('Yüzde Yirmi');
        $thirty = $this->topic('Yüzde Otuz');
        $forty = $this->topic('Yüzde Kırk');

        $this->answers(array_fill(0, 5, false));
        $this->answers([true, false, false, false, false], $twenty);
        $this->answers([true, true, true, ...array_fill(0, 7, false)], $thirty);
        $this->answers([true, true, false, false, false], $forty);

        $this->today()
            ->assertSeeInOrder([
                'Zayıf konular', 'ustalık < %50',
                'Asit Baz Tepkimeleri', '10. Sınıf · 5 soru', '%0',
                'Yüzde Yirmi', '%20',
                'Yüzde Otuz', '10. Sınıf · 10 soru', '%30',
                'Bu hafta',
            ])
            ->assertSee(route('curriculum.topic', ['program' => 'lise-kimya', 'grade' => 10, 'unit' => '10.3', 'topic' => 'asit-baz']))
            ->assertDontSee('Yüzde Kırk');
    }

    // This week (rule 8)

    public function test_week_counts_the_last_seven_days_in_turkish_time(): void
    {
        // 10 November starts at 21:00 UTC on the 9th.
        $this->answers([true], null, Carbon::parse('2026-11-09 20:59', 'UTC'));
        $this->answers([true], null, Carbon::parse('2026-11-09 21:30', 'UTC'));
        $this->answers([true, false]);

        $this->today()
            ->assertSeeInOrder(['Bu hafta', 'çözülen soru', '3', 'doğruluk', '%67', 'gün çalıştın', '2/7', 'Bugün dahil son 7 gün.'])
            ->assertSee(route('progress.index'));
    }

    public function test_week_without_answers_has_no_accuracy(): void
    {
        $this->answers(array_fill(0, 5, true), null, Carbon::parse('2026-10-01 10:00', 'UTC'));

        $this->today()->assertSeeInOrder(['Bu hafta', 'çözülen soru', '0', 'doğruluk', '—', 'gün çalıştın', '0/7']);
    }

    // Greeting (rule 9)

    public function test_greeting_and_date_follow_turkish_time(): void
    {
        $this->answers(array_fill(0, 5, true), null, Carbon::parse('2026-10-01 10:00', 'UTC'));

        $greetings = [
            '2026-11-16 05:00' => 'Günaydın, Elif',
            '2026-11-16 12:00' => 'İyi günler, Elif',
            '2026-11-16 18:00' => 'İyi akşamlar, Elif',
            '2026-11-16 22:00' => 'İyi geceler, Elif',
            '2026-11-16 04:59' => 'İyi geceler, Elif',
        ];

        foreach ($greetings as $time => $greeting) {
            Carbon::setTestNow(Carbon::parse($time, 'Europe/Istanbul'));

            $this->today()->assertSee($greeting)->assertSee('16 Kasım Pazartesi');
        }

        // Still Sunday in UTC, already Monday in Turkey.
        Carbon::setTestNow(Carbon::parse('2026-11-15 21:30', 'UTC'));

        $this->today()->assertSee('İyi geceler, Elif')->assertSee('16 Kasım Pazartesi');
    }
}

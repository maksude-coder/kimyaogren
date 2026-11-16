<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\BloomLevel;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Program;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumBrowserTest extends TestCase
{
    use RefreshDatabase;

    protected Program $program;

    protected Grade $grade;

    protected Unit $unit;

    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->program = Program::factory()->highSchool()->published()->create([
            'slug' => 'lise-kimya',
            'name' => 'Lise Kimya MEB',
            'display_order' => 1,
        ]);
        $this->grade = Grade::factory()->for($this->program)->number(10)->active()->create(['order' => 1]);
        $this->unit = Unit::factory()->for($this->grade)->code('10.3')->active()->create([
            'name' => 'Asitler, Bazlar ve Tuzlar',
            'order' => 1,
        ]);
        $this->topic = Topic::factory()->for($this->unit)->published()->create([
            'name' => 'Asitlerin ve Bazların Tepkimeleri',
            'slug' => 'asit-baz-tepkimeleri',
            'difficulty' => 3,
            'order' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function topicRoute(?Topic $topic = null, ?Unit $unit = null): array
    {
        return [
            'program' => $this->program->slug,
            'grade' => $this->grade->number,
            'unit' => ($unit ?? $this->unit)->code,
            'topic' => ($topic ?? $this->topic)->slug,
        ];
    }

    public function test_index_redirects_to_first_grade_of_first_published_program(): void
    {
        Program::factory()->highSchool()->draft()->create(['display_order' => 0]);

        $this->get(route('curriculum.index'))
            ->assertRedirect(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]));
    }

    public function test_index_shows_empty_state_when_nothing_is_published(): void
    {
        $this->program->update(['status' => 1]);

        $this->get(route('curriculum.index'))
            ->assertOk()
            ->assertSee('Müfredat henüz yayımlanmadı');
    }

    public function test_grade_page_lists_active_units_and_published_topics(): void
    {
        Unit::factory()->for($this->grade)->code('10.4')->inactive()->create(['name' => 'Gizli Ünite']);
        Topic::factory()->for($this->unit)->draft()->create(['name' => 'Taslak Konu']);

        $this->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]))
            ->assertOk()
            ->assertSee('Lise Kimya MEB')
            ->assertSee('Asitler, Bazlar ve Tuzlar')
            ->assertSee('Asitlerin ve Bazların Tepkimeleri')
            ->assertSee('Ab')
            ->assertDontSee('Gizli Ünite')
            ->assertDontSee('Taslak Konu');
    }

    public function test_grade_page_selects_unit_from_query_string(): void
    {
        $other = Unit::factory()->for($this->grade)->code('10.2')->active()->create(['name' => 'Karışımlar', 'order' => 0]);
        Topic::factory()->for($other)->published()->create(['name' => 'Karışımların Sınıflandırılması']);

        $url = route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]);

        $this->get($url)->assertSee('Karışımların Sınıflandırılması')->assertDontSee('Asitlerin ve Bazların Tepkimeleri');
        $this->get($url.'?unite=10.3')->assertSee('Asitlerin ve Bazların Tepkimeleri')->assertDontSee('Karışımların Sınıflandırılması');
    }

    public function test_grade_page_shows_empty_state_for_grade_without_units(): void
    {
        Grade::factory()->for($this->program)->number(11)->active()->create();

        $this->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 11]))
            ->assertOk()
            ->assertSee('Bu sınıfta henüz yayımlanmış ünite yok.');
    }

    public function test_grade_page_returns_404_for_unpublished_program_or_inactive_grade(): void
    {
        $draft = Program::factory()->highSchool()->draft()->create();
        Grade::factory()->for($draft)->number(10)->active()->create();
        Grade::factory()->for($this->program)->number(12)->inactive()->create();

        $this->get(route('curriculum.grade', ['program' => $draft->slug, 'grade' => 10]))->assertNotFound();
        $this->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 12]))->assertNotFound();
        $this->get(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 9]))->assertNotFound();
    }

    public function test_topic_page_shows_active_objectives_with_turkish_bloom_labels(): void
    {
        LearningObjective::factory()->for($this->topic)->code('KIM.10.3.2.1')->active()->create([
            'description' => 'Nötralleşme tepkimelerini açıklar.',
            'bloom_level' => BloomLevel::Understand,
        ]);
        LearningObjective::factory()->for($this->topic)->code('KIM.10.3.2.9')->inactive()->create([
            'description' => 'Pasif kazanım.',
        ]);

        $this->get(route('curriculum.topic', $this->topicRoute()))
            ->assertOk()
            ->assertSee('Asitlerin ve Bazların Tepkimeleri')
            ->assertSee('KIM.10.3.2.1')
            ->assertSee('Nötralleşme tepkimelerini açıklar.')
            ->assertSee('Anlama')
            ->assertSee('Zorluk 3/5')
            ->assertDontSee('Pasif kazanım.');
    }

    public function test_topic_page_returns_404_for_unpublished_topic(): void
    {
        $draft = Topic::factory()->for($this->unit)->draft()->create();

        $this->get(route('curriculum.topic', $this->topicRoute($draft)))->assertNotFound();
    }

    public function test_topic_page_returns_404_for_inactive_unit(): void
    {
        $this->unit->update(['is_active' => false]);

        $this->get(route('curriculum.topic', $this->topicRoute()))->assertNotFound();
    }

    public function test_topic_is_resolved_within_its_own_unit(): void
    {
        $otherUnit = Unit::factory()->for($this->grade)->code('10.1')->active()->create();

        $this->get(route('curriculum.topic', $this->topicRoute(unit: $otherUnit)))->assertNotFound();
    }

    public function test_student_pages_are_turkish(): void
    {
        $this->get(route('curriculum.topic', $this->topicRoute()))->assertSee('<html lang="tr">', false);
    }
}

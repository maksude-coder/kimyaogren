<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Models\Grade;
use App\Models\Program;
use App\Models\TheoryArticle;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LegacyContentTest extends TestCase
{
    use RefreshDatabase;

    protected Program $program;

    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 0]);
        $grade = Grade::factory()->for($this->program)->number(10)->active()->create();
        $unit = Unit::factory()->for($grade)->code('10.3')->active()->create(['name' => 'Asitler, Bazlar ve Tuzlar']);
        $this->topic = Topic::factory()->for($unit)->published()->create(['name' => 'Asitlerin ve Bazların Tepkimeleri']);
    }

    /**
     * The test client trims trailing slashes; legacy URLs need them verbatim.
     */
    private function getExact(string $uri): TestResponse
    {
        return TestResponse::fromBaseResponse($this->app->make(Kernel::class)->handle(Request::create($uri)));
    }

    private function article(string $slug = 'notrallesme-tepkimeleri', string $html = '<p>Asit ile baz tuz ve su oluşturur.</p>'): TheoryArticle
    {
        return TheoryArticle::factory()->for($this->topic)->published()->legacy($slug)->withContent($html)
            ->create(['title' => 'Nötralleşme Tepkimeleri']);
    }

    public function test_article_is_served_at_its_wordpress_url(): void
    {
        $this->article();

        $this->getExact('/notrallesme-tepkimeleri/')
            ->assertOk()
            ->assertSee('Nötralleşme Tepkimeleri')
            ->assertSee('Asit ile baz tuz ve su oluşturur.', false)
            ->assertSee('Asitlerin ve Bazların Tepkimeleri')
            ->assertSee('<link rel="canonical" href="'.url('notrallesme-tepkimeleri').'/">', false);
    }

    public function test_url_without_trailing_slash_redirects_permanently(): void
    {
        $this->article();

        $this->get('/notrallesme-tepkimeleri')->assertRedirect(url('notrallesme-tepkimeleri').'/')->assertStatus(301);
    }

    public function test_unknown_or_unpublished_articles_are_not_found(): void
    {
        TheoryArticle::factory()->for($this->topic)->draft()->legacy('taslak-yazi')->withContent()->create();

        $this->getExact('/taslak-yazi/')->assertNotFound();
        $this->getExact('/boyle-bir-yazi-yok/')->assertNotFound();
        $this->get('/a/b/c/')->assertNotFound();
    }

    public function test_article_is_hidden_when_its_topic_or_program_is_not_visible(): void
    {
        $this->article();

        $this->topic->update(['status' => 1]);
        $this->getExact('/notrallesme-tepkimeleri/')->assertNotFound();

        $this->topic->update(['status' => 2]);
        $this->program->update(['status' => 1]);
        $this->getExact('/notrallesme-tepkimeleri/')->assertNotFound();
    }

    public function test_category_archive_redirects_to_grade_page(): void
    {
        $this->get('/category/10-sinif-konulari/')
            ->assertStatus(301)
            ->assertRedirect(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]));

        $this->get('/category/genel-konular/')->assertStatus(301)->assertRedirect(route('curriculum.index'));
    }

    public function test_tag_archive_redirects_to_curriculum(): void
    {
        $this->get('/tag/asit/')->assertStatus(301)->assertRedirect(route('curriculum.index'));
    }

    public function test_fallback_does_not_shadow_application_routes(): void
    {
        $this->get('/mufredat/lise-kimya/10')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_topic_page_links_to_articles_in_order(): void
    {
        $this->article();
        TheoryArticle::factory()->for($this->topic)->published()->legacy('asitlerin-aktif-metallerle-tepkimeleri')->withContent()
            ->create(['title' => 'Asitlerin Aktif Metallerle Tepkimeleri', 'order' => 0]);

        $this->get(route('curriculum.topic', ['program' => 'lise-kimya', 'grade' => 10, 'unit' => '10.3', 'topic' => $this->topic->slug]))
            ->assertOk()
            ->assertSeeInOrder(['Asitlerin Aktif Metallerle Tepkimeleri', 'Nötralleşme Tepkimeleri'])
            ->assertSee(url('notrallesme-tepkimeleri').'/', false);
    }
}

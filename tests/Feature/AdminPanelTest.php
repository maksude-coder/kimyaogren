<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Full-page requests render the panel navigation, which Livewire component
 * tests do not: `ProgramResource::getNavigationBadge()` returned an int from
 * a `?string` method and crashed every panel page (DEBT-014).
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_with_the_navigation(): void
    {
        Program::factory()->published()->create();
        $question = Question::factory()->withOptions()->create();

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk();
        $this->get(route('filament.admin.resources.programs.index'))->assertOk();
        $this->get(route('filament.admin.resources.questions.index'))->assertOk()->assertSee('Assessment');
        $this->get(route('filament.admin.resources.questions.create'))->assertOk();
        $this->get(route('filament.admin.resources.questions.edit', $question))->assertOk();
        $this->get(route('filament.admin.resources.questions.view', $question))->assertOk();
    }
}

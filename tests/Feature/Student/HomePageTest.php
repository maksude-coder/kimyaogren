<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_landing_page_renders_with_sample_question(): void
    {
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kimyayı ezberleme,', false)
            ->assertSee('data-sample-question', false)
            ->assertSee('KIM.10.3.2.3')
            ->assertSee(route('curriculum.index'));
    }
}

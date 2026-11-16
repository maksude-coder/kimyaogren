<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_and_kvkk_pages_render_and_link_to_each_other(): void
    {
        $this->withoutVite();

        $this->get('/kullanim-kosullari')
            ->assertOk()
            ->assertSee('Kullanım koşulları')
            ->assertSee(route('legal.kvkk'));

        $this->get('/kvkk-aydinlatma-metni')
            ->assertOk()
            ->assertSee('KVKK aydınlatma metni')
            ->assertSee(route('legal.terms'));
    }

    public function test_landing_page_links_the_legal_pages(): void
    {
        $this->withoutVite();

        $this->get(route('home'))
            ->assertSee(route('legal.terms'))
            ->assertSee(route('legal.kvkk'));
    }
}

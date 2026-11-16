<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\QuestionHtml;
use PHPUnit\Framework\TestCase;

class QuestionHtmlTest extends TestCase
{
    public function test_formatting_tags_are_kept(): void
    {
        $html = '<p>H<sub>2</sub>SO<sub>4</sub> ve Na<sup>+</sup> <strong>önemli</strong> <em>not</em></p><ol><li>Adım</li></ol><ul><li>Madde</li></ul>';

        $this->assertSame($html, QuestionHtml::clean($html));
    }

    public function test_attributes_are_removed_from_allowed_tags(): void
    {
        $this->assertSame(
            '<p>Metin</p><ol><li>Adım</li></ol>',
            QuestionHtml::clean('<p onclick="x()" style="color:red" class="a">Metin</p><ol start="3"><li data-x="1">Adım</li></ol>'),
        );
    }

    public function test_other_tags_are_stripped_and_scripts_dropped_with_their_content(): void
    {
        $this->assertSame(
            '<p>Güvenli bağlantı</p>',
            QuestionHtml::clean('<p>Güvenli <a href="javascript:alert(1)">bağlantı</a></p><script>alert(1)</script><style>p{}</style><img src=x onerror=alert(1)><iframe src="x"></iframe>'),
        );
    }

    public function test_empty_input_gives_an_empty_string(): void
    {
        $this->assertSame('', QuestionHtml::clean(null));
        $this->assertSame('', QuestionHtml::clean("  \n"));
    }

    public function test_text_strips_tags_and_decodes_entities(): void
    {
        $this->assertSame('16 g & H2O', QuestionHtml::text('<p>16 g &amp; H<sub>2</sub>O</p>'));
        $this->assertSame('', QuestionHtml::text(null));
    }
}

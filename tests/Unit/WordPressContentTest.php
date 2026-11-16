<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\WordPressContent;
use PHPUnit\Framework\TestCase;

class WordPressContentTest extends TestCase
{
    public function test_upload_images_become_root_relative_and_lazy(): void
    {
        $html = WordPressContent::sanitize('<p><img class="alignnone wp-image-1" src="http://kimyaogren.com/wp-content/uploads/2022/05/a.jpg" srcset="x 300w" alt="" width="10" height="5" /></p>');

        $this->assertStringContainsString('src="/wp-content/uploads/2022/05/a.jpg"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringNotContainsString('srcset', $html);
        $this->assertStringNotContainsString('class=', $html);
        $this->assertSame(['/wp-content/uploads/2022/05/a.jpg'], WordPressContent::imagePaths($html));
    }

    public function test_only_youtube_iframes_survive(): void
    {
        $html = WordPressContent::sanitize(
            '<p><iframe src="https://www.youtube.com/embed/abc?list=x" width="900" height="506" frameborder="0" allowfullscreen="allowfullscreen"></iframe></p>'
            .'<p><iframe src="https://evil.example/embed"></iframe></p>'
        );

        $this->assertStringContainsString('https://www.youtube.com/embed/abc?list=x', $html);
        $this->assertStringNotContainsString('evil.example', $html);
        $this->assertStringNotContainsString('frameborder', $html);
    }

    public function test_scripts_handlers_and_unknown_tags_are_removed(): void
    {
        $html = WordPressContent::sanitize('<p onclick="x()">Asit <span style="color:red">ve</span> baz<script>alert(1)</script></p><div><strong>Nötr</strong></div><p>&nbsp;</p>');

        $this->assertSame('<p>Asit ve baz</p><strong>Nötr</strong>', $html);
    }

    public function test_turkish_text_is_preserved(): void
    {
        $this->assertSame('<p>Çözünürlük, İyonlaşma, ğüşıö</p>', WordPressContent::sanitize('<p>Çözünürlük, İyonlaşma, ğüşıö</p>'));
    }

    public function test_upload_path_rejects_foreign_hosts(): void
    {
        $this->assertSame('/wp-content/uploads/2021/10/x y.jpg', WordPressContent::uploadPath('https://www.kimyaogren.com/wp-content/uploads/2021/10/x%20y.jpg'));
        $this->assertNull(WordPressContent::uploadPath('https://example.com/wp-content/uploads/a.jpg'));
        $this->assertNull(WordPressContent::uploadPath(null));
    }

    public function test_excerpt_and_word_count(): void
    {
        $this->assertSame('Nötralleşme tepkimeleri asit ve baz.', WordPressContent::excerpt('<p>Nötralleşme tepkimeleri asit ve baz. [&hellip;]</p>'));
        $this->assertSame(4, WordPressContent::wordCount('<p>Bir iki</p><p>üç&nbsp;dört</p>'));
    }
}

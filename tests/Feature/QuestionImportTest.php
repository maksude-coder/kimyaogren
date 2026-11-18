<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\YksSession;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Program;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\User;
use App\Support\QuestionCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use RefreshDatabase;

    protected Program $program;

    protected Topic $topic;

    protected Topic $tfTopic;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->program = Program::factory()->highSchool()->published()->create(['code' => 'KIM_MEB_TR', 'display_order' => 0]);
        $grade = Grade::factory()->for($this->program)->number(10)->active()->create();
        $unit = Unit::factory()->for($grade)->code('10.3')->active()->create();
        $this->topic = Topic::factory()->for($unit)->published()->create(['name' => 'Asitlerin ve Bazların Tepkimeleri', 'slug' => 'asitlerin-ve-bazlarin-tepkimeleri']);
        $this->tfTopic = Topic::factory()->for($unit)->draft()->create(['name' => 'Asitler ve Bazlar', 'slug' => 'asitler-ve-bazlar']);
    }

    protected function tearDown(): void
    {
        array_map('unlink', array_filter($this->files, 'is_file'));

        parent::tearDown();
    }

    /**
     * @param  list<array<string, string>>  $rows  column => value, other columns empty
     */
    private function csv(array $rows, string $delimiter = ';', ?array $headers = null): string
    {
        $headers ??= QuestionCsvImporter::HEADERS;
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers, $delimiter, '"', '');

        foreach ($rows as $row) {
            fputcsv($stream, array_map(fn (string $header) => $row[$header] ?? '', $headers), $delimiter, '"', '');
        }

        rewind($stream);

        return (string) stream_get_contents($stream);
    }

    private function file(string $csv): string
    {
        $path = tempnam(sys_get_temp_dir(), 'qcsv');
        file_put_contents($path, $csv);

        return $this->files[] = $path;
    }

    /**
     * @return array<string, string>
     */
    private function mcRow(array $override = []): array
    {
        return [
            'sinif' => '10', 'unite' => '10.3', 'konu' => 'Asitlerin ve Bazların Tepkimeleri',
            'soru' => '0,2 mol H<sub>2</sub>SO<sub>4</sub> için kaç gram NaOH gerekir?',
            'a' => '4 g', 'b' => '8 g', 'c' => '12 g', 'd' => '16 g', 'e' => '20 g', 'dogru' => 'D',
            'cozum' => "Denklemi yaz.\nMol oranı 1 : 2.",
            'b_geribildirim' => 'Katsayı 2 atlanmış.',
            ...$override,
        ];
    }

    private function importer(): QuestionCsvImporter
    {
        return new QuestionCsvImporter($this->program);
    }

    public function test_multiple_choice_row_is_imported_as_draft_with_options(): void
    {
        $result = $this->importer()->import($this->file($this->csv([$this->mcRow(['zorluk' => '4', 'bloom' => '3', 'yks_yili' => '2023', 'yks_oturumu' => 'ayt', 'kaynak' => 'YKS 2023 AYT', 'etiketler' => 'mol, nötralleşme'])])));

        $this->assertFalse($result->failed(), implode("\n", $result->errors));
        $this->assertSame(1, $result->imported);

        $question = Question::with('options')->sole();
        $this->assertSame($this->topic->id, $question->topic_id);
        $this->assertSame(QuestionStatus::Draft, $question->status);
        $this->assertSame(QuestionType::MultipleChoice, $question->type);
        $this->assertSame(QuestionDifficulty::Hard, $question->difficulty);
        $this->assertSame(BloomLevel::Apply, $question->bloom_level);
        $this->assertSame(YksSession::Ayt, $question->yks_session);
        $this->assertSame(['mol', 'nötralleşme'], $question->tags);
        $this->assertSame('<p>0,2 mol H<sub>2</sub>SO<sub>4</sub> için kaç gram NaOH gerekir?</p>', $question->content);
        $this->assertSame('<p>Denklemi yaz.<br>'."\n".'Mol oranı 1 : 2.</p>', $question->explanation);
        $this->assertTrue($question->hasValidOptions());
        $this->assertSame('D', $question->correctOption->letter());
        $this->assertSame('<p>Katsayı 2 atlanmış.</p>', $question->options[1]->feedback);
    }

    public function test_true_false_row_by_topic_slug_into_a_draft_topic(): void
    {
        $result = $this->importer()->import($this->file($this->csv([[
            'sinif' => '10', 'unite' => '10.3', 'konu' => 'asitler-ve-bazlar', 'tip' => 'Doğru/Yanlış',
            'soru' => 'Asitler mavi turnusolü kırmızıya çevirir.', 'dogru' => 'Yanlış',
        ]], ',')));

        $this->assertFalse($result->failed(), implode("\n", $result->errors));

        $question = Question::sole();
        $this->assertSame($this->tfTopic->id, $question->topic_id);
        $this->assertSame(QuestionType::TrueFalse, $question->type);
        $this->assertSame(['Doğru', 'Yanlış'], $question->options()->pluck('option_text')->all());
        $this->assertSame('Yanlış', $question->correctOption->option_text);
    }

    public function test_any_invalid_row_imports_nothing_and_reports_spreadsheet_rows(): void
    {
        $result = $this->importer()->import($this->file($this->csv([
            $this->mcRow(),
            $this->mcRow(['soru' => 'İkinci soru', 'unite' => '10.9']),
            $this->mcRow(['soru' => 'Üçüncü soru', 'dogru' => 'F', 'e' => '']),
            $this->mcRow(['soru' => 'Dördüncü soru', 'd_geribildirim' => 'Doğru şıkka geri bildirim']),
        ])));

        $this->assertTrue($result->failed());
        $this->assertSame(0, Question::count());
        $this->assertContains('Satır 3: "10.9" ünitesi 10. sınıfta yok.', $result->errors);
        $this->assertContains('Satır 4: "dogru" çoktan seçmeli soruda A, B, C, D ya da E olmalı.', $result->errors);
        $this->assertContains('Satır 4: "e" şıkkı boş olamaz (çoktan seçmeli soruda beş şık gerekir).', $result->errors);
        $this->assertContains('Satır 5: Doğru şık (D) için geri bildirim yazılmaz; geri bildirim yanlış şıklar içindir.', $result->errors);
    }

    public function test_objective_must_belong_to_the_topic(): void
    {
        LearningObjective::factory()->for($this->topic)->code('KIM.10.3.2.3')->create();

        $ok = $this->importer()->import($this->file($this->csv([$this->mcRow(['kazanim' => 'KIM.10.3.2.3'])])));
        $this->assertFalse($ok->failed(), implode("\n", $ok->errors));
        $this->assertNotNull(Question::sole()->learning_objective_id);

        $bad = $this->importer()->import($this->file($this->csv([$this->mcRow(['soru' => 'Başka', 'kazanim' => 'KIM.9.9.9.9'])])));
        $this->assertSame(['Satır 2: "KIM.9.9.9.9" kazanımı bu konuda yok.'], $bad->errors);
    }

    public function test_duplicates_in_file_and_bank_are_skipped(): void
    {
        $file = $this->file($this->csv([$this->mcRow(), $this->mcRow(['soru' => '0,2 mol  H<sub>2</sub>SO<sub>4</sub>  için kaç gram NaOH gerekir?'])]));

        $first = $this->importer()->import($file);
        $this->assertSame(1, $first->imported);
        $this->assertSame(['Satır 3: bu soru konuda zaten var, atlandı.'], $first->skipped);

        $second = $this->importer()->import($file);
        $this->assertSame(0, $second->imported);
        $this->assertCount(2, $second->skipped);
        $this->assertSame(1, Question::count());
    }

    public function test_html_outside_the_allow_list_is_removed(): void
    {
        $this->importer()->import($this->file($this->csv([$this->mcRow(['soru' => '<script>alert(1)</script><span onclick="x()">Soru</span> <b>kalın</b>'])])));

        $this->assertSame('<p>Soru <b>kalın</b></p>', Question::sole()->content);
    }

    public function test_unknown_or_missing_columns_are_rejected(): void
    {
        $unknown = $this->importer()->import($this->file($this->csv([$this->mcRow()], ';', [...QuestionCsvImporter::HEADERS, 'cevap'])));
        $this->assertSame(['Tanınmayan sütun: cevap. Şablondaki sütun adlarını kullan.'], $unknown->errors);

        $missing = $this->importer()->import($this->file("sinif;unite;konu;soru\n10;10.3;x;y\n"));
        $this->assertSame(['Eksik zorunlu sütun: dogru.'], $missing->errors);
    }

    public function test_turkish_headers_bom_and_windows_1254_are_accepted(): void
    {
        $csv = "\u{FEFF}Sınıf;Ünite;Konu;Soru;A;B;C;D;E;Doğru;Çözüm\n10;10.3;Asitlerin ve Bazların Tepkimeleri;Soru;1;2;3;4;5;A;Çözüm\n";
        $this->assertFalse($this->importer()->import($this->file($csv))->failed());

        $legacy = mb_convert_encoding("sinif;unite;konu;soru;a;b;c;d;e;dogru\n10;10.3;Asitlerin ve Bazların Tepkimeleri;Şişe sorusu;1;2;3;4;5;B\n", 'Windows-1254', 'UTF-8');
        $this->assertFalse($this->importer()->import($this->file($legacy))->failed());
        $this->assertTrue(Question::where('content', '<p>Şişe sorusu</p>')->exists());
    }

    public function test_template_imports_cleanly(): void
    {
        $result = $this->importer()->import($this->file(QuestionCsvImporter::template()), submitForReview: true);

        $this->assertFalse($result->failed(), implode("\n", $result->errors));
        $this->assertSame(2, $result->imported);
        $this->assertSame(2, Question::where('status', QuestionStatus::Review)->count());
    }

    public function test_row_limit(): void
    {
        $rows = array_map(fn (int $i) => $this->mcRow(['soru' => "Soru {$i}"]), range(1, QuestionCsvImporter::MAX_ROWS + 1));

        $result = $this->importer()->import($this->file($this->csv($rows)));

        $this->assertStringContainsString('en fazla 1000 soru', $result->errors[0]);
        $this->assertSame(0, Question::count());
    }

    public function test_command_imports_into_the_first_published_program(): void
    {
        $this->artisan('questions:import', ['file' => $this->file($this->csv([$this->mcRow()])), '--review' => true])
            ->assertSuccessful();

        $this->assertSame(QuestionStatus::Review, Question::sole()->status);

        $this->artisan('questions:import', ['file' => $this->file($this->csv([$this->mcRow(['sinif' => '9'])]))])
            ->expectsOutputToContain('"9". sınıf bu programda yok.')
            ->assertFailed();
    }

    public function test_admin_imports_from_the_question_list(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(ListQuestions::class)
            ->assertActionVisible('importCsv')
            ->assertActionVisible('downloadTemplate')
            ->callAction('importCsv', data: [
                'program_id' => $this->program->id,
                'file' => UploadedFile::fake()->createWithContent('sorular.csv', $this->csv([$this->mcRow()])),
                'submit_for_review' => false,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified('1 soru içe aktarıldı.');

        $this->assertSame($admin->id, Question::sole()->created_by_id);
    }

    public function test_failed_import_keeps_the_modal_open_and_writes_nothing(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ListQuestions::class)
            ->callAction('importCsv', data: [
                'program_id' => $this->program->id,
                'file' => UploadedFile::fake()->createWithContent('sorular.csv', $this->csv([$this->mcRow(['dogru' => ''])])),
            ])
            ->assertNotified('1 hata bulundu; hiçbir soru içe aktarılmadı.')
            ->assertActionMounted('importCsv');

        $this->assertSame(0, Question::count());
    }

    public function test_students_cannot_see_the_import_actions(): void
    {
        $this->actingAs(User::factory()->student()->create());

        $this->get(route('filament.admin.resources.questions.index'))->assertForbidden();
    }
}

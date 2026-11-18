<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\YksSession;
use App\Models\LearningObjective;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bulk question import from a CSV file (Sprint 3.5 plan § 2–3).
 *
 * Every row is validated before anything is written; one error anywhere
 * means nothing is imported. Questions enter the review workflow as Draft
 * (or Review) — an import never approves.
 */
final class QuestionCsvImporter
{
    public const MAX_ROWS = 1000;

    public const MAX_BYTES = 2 * 1024 * 1024;

    /** Template headers, in order. */
    public const HEADERS = [
        'sinif', 'unite', 'konu', 'kazanim', 'tip', 'zorluk', 'bloom', 'soru',
        'a', 'b', 'c', 'd', 'e', 'dogru', 'cozum',
        'a_geribildirim', 'b_geribildirim', 'c_geribildirim', 'd_geribildirim', 'e_geribildirim',
        'yks_yili', 'yks_oturumu', 'kaynak', 'etiketler',
    ];

    private const REQUIRED = ['sinif', 'unite', 'konu', 'soru', 'dogru'];

    private const TRUE_FALSE = ['Doğru', 'Yanlış'];

    /** @var array<int, array<string, array{unit: Unit, topics: array<string, Topic>}>>|null grade => unit code => unit + topics by key */
    private ?array $tree = null;

    /** @var array<int, array<string, true>> topic id => normalised stems already in the bank */
    private array $existingStems = [];

    public function __construct(private readonly Program $program) {}

    public function import(string $path, bool $submitForReview = false, ?int $userId = null): QuestionImportResult
    {
        $result = new QuestionImportResult;

        if (! is_file($path) || filesize($path) > self::MAX_BYTES) {
            $result->errors[] = 'Dosya bulunamadı ya da 2 MB sınırını aşıyor.';

            return $result;
        }

        [$header, $records] = $this->read($path);

        if ($header === []) {
            $result->errors[] = 'Dosya boş.';

            return $result;
        }

        if ($error = $this->checkHeader($header)) {
            $result->errors[] = $error;

            return $result;
        }

        if (count($records) > self::MAX_ROWS) {
            $result->errors[] = 'Bir dosyada en fazla '.self::MAX_ROWS.' soru olabilir; bu dosyada '.count($records).' satır var.';

            return $result;
        }

        if ($records === []) {
            $result->errors[] = 'Dosyada başlık satırından başka satır yok.';

            return $result;
        }

        $pending = [];
        $seen = [];

        foreach ($records as $rowNumber => $cells) {
            $row = $this->combine($header, $cells);
            $errors = [];
            $question = $this->validateRow($row, $errors);

            if ($errors !== []) {
                foreach ($errors as $message) {
                    $result->errors[] = "Satır {$rowNumber}: {$message}";
                }

                continue;
            }

            $key = $question['topic']->id.'|'.$this->stemKey($question['content']);

            if (isset($seen[$key]) || $this->existsInBank($question['topic'], $question['content'])) {
                $result->skipped[] = "Satır {$rowNumber}: bu soru konuda zaten var, atlandı.";

                continue;
            }

            $seen[$key] = true;
            $pending[] = $question;
        }

        if ($result->errors !== []) {
            return $result;
        }

        DB::transaction(function () use ($pending, $submitForReview, $userId, $result): void {
            foreach ($pending as $data) {
                $this->create($data, $submitForReview ? QuestionStatus::Review : QuestionStatus::Draft, $userId);
                $result->imported++;
            }
        });

        return $result;
    }

    /**
     * A ready-to-fill template: headers and two example rows (`;`, UTF-8 with BOM for Excel).
     */
    public static function template(): string
    {
        $rows = [
            self::HEADERS,
            [
                '10', '10.3', 'Asitlerin ve Bazların Tepkimeleri', '', 'coktan_secmeli', '3', '3',
                '0,2 mol H<sub>2</sub>SO<sub>4</sub> içeren sulu çözeltiyi tamamen nötralleştirmek için kaç gram NaOH gerekir? (NaOH: 40 g/mol)',
                '4 g', '8 g', '12 g', '16 g', '20 g', 'D',
                '<ol><li>H<sub>2</sub>SO<sub>4</sub> + 2NaOH → Na<sub>2</sub>SO<sub>4</sub> + 2H<sub>2</sub>O</li><li>Mol oranı 1 : 2 → 0,2 × 2 = 0,4 mol NaOH</li><li>m = n × M = 0,4 × 40 = 16 g</li></ol>',
                '0,2 mol asidin kütlesi ile karıştırılıp 0,1 mol NaOH hesaplanmış olabilir.',
                'Katsayı 2 atlanıp mol oranı 1 : 1 alınırsa bu sonuca ulaşılır.',
                'Mol sayısı 0,3 alınmış; denklemdeki katsayıları yeniden kontrol et.',
                '',
                'Mol oranı 1 : 2,5 gibi alınmış; denkleştirilmiş denklemden oranı oku.',
                '', 'AYT', '', 'nötralleşme, mol',
            ],
            [
                '10', '10.3', 'Asitler ve Bazlar', '', 'dogru_yanlis', '1', '1',
                'Asitlerin sulu çözeltileri mavi turnusol kâğıdını kırmızıya çevirir.',
                '', '', '', '', '', 'Doğru',
                'Asitler mavi turnusol kâğıdını kırmızıya, bazlar kırmızı turnusol kâğıdını maviye çevirir.',
                '', '', '', '', '', '', '', '', 'indikatör',
            ],
        ];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\u{FEFF}");

        foreach ($rows as $row) {
            fputcsv($stream, $row, ';', '"', '');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return (string) $csv;
    }

    /**
     * @return array{0: list<string>, 1: array<int, list<string>>} header keys, records keyed by spreadsheet row number
     */
    private function read(string $path): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\x{FEFF}/u', '', $content) ?? $content;

        if (! mb_check_encoding($content, 'UTF-8')) {
            // Older Excel versions save Turkish CSV as Windows-1254.
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1254');
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = [];
        $records = [];
        $rowNumber = 0;

        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $rowNumber++;

            if ($cells === [null] || implode('', array_map('trim', array_map('strval', $cells))) === '') {
                continue;
            }

            if ($header === []) {
                $header = array_map(fn ($cell) => self::key((string) $cell), $cells);

                continue;
            }

            $records[$rowNumber] = array_map(fn ($cell) => trim((string) $cell), $cells);
        }

        fclose($stream);

        return [$header, $records];
    }

    /**
     * @param  list<string>  $header
     */
    private function checkHeader(array $header): ?string
    {
        $known = array_map([self::class, 'key'], self::HEADERS);
        $unknown = array_diff(array_filter($header), $known);
        $missing = array_diff(array_map([self::class, 'key'], self::REQUIRED), $header);

        if ($unknown !== []) {
            return 'Tanınmayan sütun: '.implode(', ', $unknown).'. Şablondaki sütun adlarını kullan.';
        }

        if ($missing !== []) {
            return 'Eksik zorunlu sütun: '.implode(', ', $missing).'.';
        }

        return null;
    }

    /**
     * @param  list<string>  $header
     * @param  list<string>  $cells
     * @return array<string, string>
     */
    private function combine(array $header, array $cells): array
    {
        $row = [];

        foreach ($header as $index => $key) {
            if ($key !== '') {
                $row[$key] = $cells[$index] ?? '';
            }
        }

        return $row;
    }

    /**
     * @param  array<string, string>  $row
     * @param  list<string>  $errors
     * @return array<string, mixed>|null
     */
    private function validateRow(array $row, array &$errors): ?array
    {
        $get = fn (string $column): string => $row[self::key($column)] ?? '';

        $topic = $this->resolveTopic($get('sinif'), $get('unite'), $get('konu'), $errors);

        $type = $this->type($get('tip'));
        if ($type === null) {
            $errors[] = "\"tip\" çoktan_seçmeli ya da doğru_yanlış olmalı (\"{$get('tip')}\").";
        }

        $difficulty = $this->enumNumber($get('zorluk'), QuestionDifficulty::class, QuestionDifficulty::Medium, 'zorluk', '1–5', $errors);
        $bloom = $this->enumNumber($get('bloom'), BloomLevel::class, BloomLevel::Understand, 'bloom', '1–6', $errors);

        $content = $this->html($get('soru'));
        if ($content === null) {
            $errors[] = '"soru" boş olamaz.';
        }

        $objectiveId = null;
        if ($topic && $get('kazanim') !== '') {
            $objectiveId = LearningObjective::query()->where('topic_id', $topic->id)->where('code', $get('kazanim'))->value('id');
            if ($objectiveId === null) {
                $errors[] = "\"{$get('kazanim')}\" kazanımı bu konuda yok.";
            }
        }

        $options = $type ? $this->options($type, $get, $errors) : null;

        $year = null;
        if ($get('yks_yili') !== '') {
            $year = filter_var($get('yks_yili'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1999, 'max_range' => (int) date('Y')]]);
            if ($year === false) {
                $errors[] = '"yks_yili" 1999 ile '.date('Y').' arasında bir yıl olmalı.';
            }
        }

        $session = null;
        if ($get('yks_oturumu') !== '') {
            $session = match (mb_strtoupper(Str::ascii($get('yks_oturumu')))) {
                'TYT' => YksSession::Tyt,
                'AYT' => YksSession::Ayt,
                default => null,
            };
            if ($session === null) {
                $errors[] = '"yks_oturumu" TYT ya da AYT olmalı.';
            }
        }

        if (mb_strlen($get('kaynak')) > 100) {
            $errors[] = '"kaynak" en fazla 100 karakter olabilir.';
        }

        if ($errors !== []) {
            return null;
        }

        $tags = array_values(array_filter(array_map('trim', explode(',', $get('etiketler')))));

        return [
            'topic' => $topic,
            'learning_objective_id' => $objectiveId,
            'type' => $type,
            'difficulty' => $difficulty,
            'bloom_level' => $bloom,
            'content' => $content,
            'explanation' => $this->html($get('cozum')),
            'options' => $options,
            'yks_year' => $year,
            'yks_session' => $session,
            'source' => $get('kaynak') ?: null,
            'tags' => $tags ?: null,
        ];
    }

    /**
     * @param  list<string>  $errors
     */
    private function resolveTopic(string $grade, string $unit, string $topic, array &$errors): ?Topic
    {
        $tree = $this->tree();

        if (! ctype_digit($grade) || ! isset($tree[(int) $grade])) {
            $errors[] = "\"{$grade}\". sınıf bu programda yok.";

            return null;
        }

        $units = $tree[(int) $grade];

        if (! isset($units[$unit])) {
            $errors[] = "\"{$unit}\" ünitesi {$grade}. sınıfta yok.";

            return null;
        }

        $found = $units[$unit]['topics'][Str::slug($topic)] ?? $units[$unit]['topics'][$this->nameKey($topic)] ?? null;

        if ($found === null) {
            $errors[] = "\"{$topic}\" konusu {$unit} ünitesinde yok.";
        }

        return $found;
    }

    /**
     * @return array<int, array<string, array{unit: Unit, topics: array<string, Topic>}>>
     */
    private function tree(): array
    {
        if ($this->tree !== null) {
            return $this->tree;
        }

        $this->tree = [];

        foreach ($this->program->grades()->with('units.topics')->get() as $grade) {
            foreach ($grade->units as $unit) {
                $topics = [];

                foreach ($unit->topics as $topic) {
                    $topics[$topic->slug] = $topic;
                    $topics[$this->nameKey($topic->name)] = $topic;
                }

                $this->tree[$grade->number][$unit->code] = ['unit' => $unit, 'topics' => $topics];
            }
        }

        return $this->tree;
    }

    /**
     * @param  callable(string): string  $get
     * @param  list<string>  $errors
     * @return list<array{option_text: string, is_correct: bool, feedback: string|null}>|null
     */
    private function options(QuestionType $type, callable $get, array &$errors): ?array
    {
        $letters = array_map('strtolower', QuestionOption::LETTERS);
        $answer = $get('dogru');

        if ($type === QuestionType::TrueFalse) {
            foreach ($letters as $letter) {
                if ($get($letter) !== '' || $get("{$letter}_geribildirim") !== '') {
                    $errors[] = 'Doğru/yanlış sorusunda a–e şık sütunları boş kalmalı.';

                    break;
                }
            }

            $correct = match (self::key($answer)) {
                'dogru', 'd', 'true', 'evet' => 0,
                'yanlis', 'y', 'false', 'hayir' => 1,
                default => null,
            };

            if ($correct === null) {
                $errors[] = '"dogru" doğru/yanlış sorusunda "Doğru" ya da "Yanlış" olmalı.';

                return null;
            }

            return array_map(fn (int $index) => [
                'option_text' => self::TRUE_FALSE[$index],
                'is_correct' => $index === $correct,
                'feedback' => null,
            ], [0, 1]);
        }

        $correctLetter = mb_strtoupper(trim($answer));

        if (! in_array($correctLetter, QuestionOption::LETTERS, true)) {
            $errors[] = '"dogru" çoktan seçmeli soruda A, B, C, D ya da E olmalı.';
        }

        $rows = [];

        foreach (QuestionOption::LETTERS as $letter) {
            $text = $this->html($get(strtolower($letter)));
            $feedback = $this->html($get(strtolower($letter).'_geribildirim'));

            if ($text === null) {
                $errors[] = '"'.strtolower($letter).'" şıkkı boş olamaz (çoktan seçmeli soruda beş şık gerekir).';
            }

            if ($feedback !== null && $letter === $correctLetter) {
                $errors[] = "Doğru şık ({$letter}) için geri bildirim yazılmaz; geri bildirim yanlış şıklar içindir.";
            }

            $rows[] = ['option_text' => (string) $text, 'is_correct' => $letter === $correctLetter, 'feedback' => $feedback];
        }

        return $rows;
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     * @param  list<string>  $errors
     */
    private function enumNumber(string $value, string $enum, \BackedEnum $default, string $column, string $range, array &$errors): ?\BackedEnum
    {
        if ($value === '') {
            return $default;
        }

        $case = ctype_digit($value) ? $enum::tryFrom((int) $value) : null;

        if ($case === null) {
            $errors[] = "\"{$column}\" {$range} arasında bir sayı olmalı (\"{$value}\").";
        }

        return $case;
    }

    private function type(string $value): ?QuestionType
    {
        return match (self::key($value)) {
            '', 'cs', 'coktansecmeli', 'multiplechoice' => QuestionType::MultipleChoice,
            'dy', 'dogruyanlis', 'truefalse' => QuestionType::TrueFalse,
            default => null,
        };
    }

    /**
     * Cell text → question HTML: the allow-list for HTML cells, paragraphs for plain text.
     */
    private function html(string $value): ?string
    {
        $value = trim(str_replace("\r\n", "\n", $value));

        if ($value === '') {
            return null;
        }

        if (preg_match('#</?[a-z][a-z0-9]*\b[^>]*>#i', $value) === 1) {
            $html = QuestionHtml::clean($value);

            return preg_match('#^<(p|ol|ul)>#', $html) === 1 ? $html : "<p>{$html}</p>";
        }

        $paragraphs = preg_split('/\n{2,}/', $value) ?: [$value];

        return implode('', array_map(fn (string $paragraph) => '<p>'.nl2br(e(trim($paragraph)), false).'</p>', $paragraphs));
    }

    private function existsInBank(Topic $topic, string $content): bool
    {
        if (! isset($this->existingStems[$topic->id])) {
            $this->existingStems[$topic->id] = Question::query()->where('topic_id', $topic->id)->pluck('content')
                ->mapWithKeys(fn (string $stem) => [$this->stemKey($stem) => true])
                ->all();
        }

        return isset($this->existingStems[$topic->id][$this->stemKey($content)]);
    }

    private function stemKey(string $html): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', QuestionHtml::text($html)) ?? '');
    }

    private function nameKey(string $name): string
    {
        return 'name:'.mb_strtolower(trim($name));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function create(array $data, QuestionStatus $status, ?int $userId): void
    {
        $question = new Question([
            'topic_id' => $data['topic']->id,
            'learning_objective_id' => $data['learning_objective_id'],
            'type' => $data['type'],
            'content' => $data['content'],
            'explanation' => $data['explanation'],
            'difficulty' => $data['difficulty'],
            'bloom_level' => $data['bloom_level'],
            'is_active' => true,
            'yks_year' => $data['yks_year'],
            'yks_session' => $data['yks_session'],
            'source' => $data['source'],
            'tags' => $data['tags'],
            'created_by_id' => $userId,
            'updated_by_id' => $userId,
        ]);
        $question->status = $status;
        $question->save();
        $question->syncOptions($data['options']);
    }

    /**
     * Header / keyword normalisation: "Çözüm" → "cozum", "A Geri Bildirim" → "ageribildirim".
     */
    public static function key(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii(trim($value)))) ?? '';
    }
}

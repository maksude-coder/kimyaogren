<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Schemas;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\YksSession;
use App\Models\LearningObjective;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Question form (Sprint 3.1 plan § 2). Multiple choice shows five fixed
 * option groups A–E and one "correct option" choice; true/false shows only
 * the correct answer. The option rows themselves are written by
 * `Question::syncOptions()` from `optionRows()`.
 */
class QuestionForm
{
    /** Form keys that are not `questions` columns. */
    public const OPTION_KEYS = ['options', 'correct_option', 'correct_answer'];

    private const TRUE_FALSE = ['true' => 'Doğru', 'false' => 'Yanlış'];

    /** Formulas need real sub/superscript; nothing else is allowed in a question. */
    private const INLINE_TOOLBAR = [['bold', 'italic', 'subscript', 'superscript'], ['undo', 'redo']];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Question')
                    ->schema([
                        Select::make('topic_id')
                            ->label('Topic')
                            ->relationship('topic', 'name', fn (Builder $query) => $query->with('unit.grade'))
                            ->getOptionLabelFromRecordUsing(fn (Topic $record) => "{$record->unit->grade->name} — {$record->unit->name} — {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('learning_objective_id', null)),

                        Select::make('learning_objective_id')
                            ->label('Learning Objective')
                            ->options(fn (Get $get) => LearningObjective::query()
                                ->where('topic_id', $get('topic_id'))
                                ->ordered()
                                ->get()
                                ->mapWithKeys(fn (LearningObjective $objective) => [$objective->id => "{$objective->code} — {$objective->description}"]))
                            ->rule(fn (Get $get): Exists => Rule::exists('learning_objectives', 'id')->where('topic_id', $get('topic_id')))
                            ->searchable()
                            ->helperText('Optional. Only objectives of the selected topic are listed.'),

                        Select::make('type')
                            ->label('Type')
                            ->options(QuestionType::supportedOptions())
                            ->default(QuestionType::MultipleChoice->value)
                            ->required()
                            ->native(false)
                            ->live(),

                        Select::make('difficulty')
                            ->label('Difficulty')
                            ->options(QuestionDifficulty::options())
                            ->default(QuestionDifficulty::Medium->value)
                            ->required()
                            ->native(false),

                        Select::make('bloom_level')
                            ->label('Bloom Level')
                            ->options(BloomLevel::options())
                            ->default(BloomLevel::Understand->value)
                            ->required()
                            ->native(false),

                        RichEditor::make('content')
                            ->label('Question')
                            ->toolbarButtons(self::INLINE_TOOLBAR)
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        RichEditor::make('explanation')
                            ->label('Step-by-step solution')
                            ->toolbarButtons([['bold', 'italic', 'subscript', 'superscript'], ['orderedList', 'bulletList'], ['undo', 'redo']])
                            ->maxLength(65535)
                            ->helperText('Write each step as a numbered list item. Required before the question can be approved.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Options')
                    ->description('Exactly five options (A–E), one of them correct.')
                    ->visible(fn (Get $get): bool => self::type($get) === QuestionType::MultipleChoice)
                    ->schema([
                        ...array_map(fn (string $letter): Fieldset => Fieldset::make("Option {$letter}")
                            ->statePath("options.{$letter}")
                            ->schema([
                                RichEditor::make('option_text')
                                    ->label('Text')
                                    ->toolbarButtons(self::INLINE_TOOLBAR)
                                    ->required()
                                    ->maxLength(65535),

                                Textarea::make('feedback')
                                    ->label('Feedback')
                                    ->rows(3)
                                    ->maxLength(65535)
                                    ->helperText('Why would a student arrive at this option? Shown when they pick it and it is wrong.'),
                            ]), QuestionOption::LETTERS),

                        Radio::make('correct_option')
                            ->label('Correct option')
                            ->options(array_combine(QuestionOption::LETTERS, QuestionOption::LETTERS))
                            ->inline()
                            ->required(),
                    ]),

                Section::make('Answer')
                    ->visible(fn (Get $get): bool => self::type($get) === QuestionType::TrueFalse)
                    ->schema([
                        Radio::make('correct_answer')
                            ->label('Correct answer')
                            ->options(self::TRUE_FALSE)
                            ->inline()
                            ->required(),
                    ]),

                Section::make('Status & Source')
                    ->schema([
                        Placeholder::make('status_label')
                            ->label('Status')
                            ->content(fn (?Question $record): string => ($record?->status ?? QuestionStatus::Draft)->label())
                            ->helperText('Changes through the review actions at the top of the page.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive questions are never shown to students'),

                        TextInput::make('yks_year')
                            ->label('YKS Year')
                            ->numeric()
                            ->minValue(1999)
                            ->maxValue((int) date('Y'))
                            ->step(1),

                        Select::make('yks_session')
                            ->label('YKS Session')
                            ->options(YksSession::options())
                            ->native(false),

                        TextInput::make('source')
                            ->label('Source')
                            ->maxLength(100)
                            ->helperText('e.g. "YKS 2023 AYT"'),

                        TagsInput::make('tags')
                            ->label('Tags'),
                    ])->columns(2),

                Section::make('Audit')
                    ->schema([
                        Placeholder::make('created_by')
                            ->label('Created By')
                            ->content(fn ($record) => $record?->creator?->name ?? '—'),

                        Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(fn ($record) => $record?->updater?->name ?? '—'),

                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i') ?? '—'),

                        Placeholder::make('updated_at')
                            ->label('Updated At')
                            ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i') ?? '—'),
                    ])->columns(2)->collapsible()->collapsed(),
            ]);
    }

    /**
     * Turn the submitted form data into option rows for `Question::syncOptions()`.
     *
     * @param  array<string, mixed>  $data
     * @return list<array{option_text: string, is_correct: bool, feedback: string|null}>
     */
    public static function optionRows(array $data): array
    {
        $type = $data['type'] instanceof QuestionType ? $data['type'] : QuestionType::from($data['type']);

        if ($type === QuestionType::TrueFalse) {
            return array_map(
                fn (string $key, string $text): array => [
                    'option_text' => $text,
                    'is_correct' => $data['correct_answer'] === $key,
                    'feedback' => null,
                ],
                array_keys(self::TRUE_FALSE),
                self::TRUE_FALSE,
            );
        }

        return array_map(
            fn (string $letter): array => [
                'option_text' => $data['options'][$letter]['option_text'],
                'is_correct' => $data['correct_option'] === $letter,
                'feedback' => filled($data['options'][$letter]['feedback'] ?? null) ? $data['options'][$letter]['feedback'] : null,
            ],
            QuestionOption::LETTERS,
        );
    }

    /**
     * The option part of the form state for an existing question.
     *
     * @return array<string, mixed>
     */
    public static function optionState(Question $question): array
    {
        $options = $question->options()->get();
        $correct = $options->firstWhere('is_correct', true);

        if ($question->type === QuestionType::TrueFalse) {
            return ['correct_answer' => match ($correct?->order) {
                1 => 'true',
                2 => 'false',
                default => null,
            }];
        }

        return [
            'options' => $options
                ->filter(fn (QuestionOption $option): bool => $option->letter() !== null)
                ->mapWithKeys(fn (QuestionOption $option): array => [$option->letter() => [
                    'option_text' => $option->option_text,
                    'feedback' => $option->feedback,
                ]])
                ->all(),
            'correct_option' => $correct?->letter(),
        ];
    }

    private static function type(Get $get): ?QuestionType
    {
        $type = $get('type');

        return $type instanceof QuestionType ? $type : QuestionType::tryFrom((string) $type);
    }
}

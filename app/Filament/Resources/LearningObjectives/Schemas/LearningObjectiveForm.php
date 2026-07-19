<?php

declare(strict_types=1);

namespace App\Filament\Resources\LearningObjectives\Schemas;

use App\Enums\BloomLevel;
use App\Models\Topic;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class LearningObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Learning Objective Information')
                    ->schema([
                        Select::make('topic_id')
                            ->label('Topic')
                            ->relationship('topic', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Topic $record) => "{$record->unit->grade->program->name} — {$record->unit->grade->name} — {$record->unit->name} — {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('The code below is validated for uniqueness against this Topic.'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(30)
                            ->regex('/^KIM\.\d{1,2}\.\d{1,2}\.\d{1,2}\.\d{1,2}$/')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('topic_id', $get('topic_id')),
                            )
                            ->helperText('MEB format: KIM.{grade}.{unit}.{topic}.{sequence}, e.g. "KIM.9.2.1.1" — unique within the selected Topic'),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3),

                        Select::make('bloom_level')
                            ->label('Bloom\'s Taxonomy Level')
                            ->options(BloomLevel::options())
                            ->default(BloomLevel::Remember->value)
                            ->required()
                            ->native(false),

                        TextInput::make('difficulty')
                            ->label('Difficulty')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1)
                            ->helperText('1 (very easy) to 5 (very hard)'),

                        TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Lower values appear first within the Topic'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive objectives are excluded from question tagging'),
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
}

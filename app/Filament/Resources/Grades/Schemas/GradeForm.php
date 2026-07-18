<?php

declare(strict_types=1);

namespace App\Filament\Resources\Grades\Schemas;

use App\Models\Program;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Grade Information')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program')
                            ->relationship('program', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('The grade range below is validated against this Program\'s min/max grade.'),

                        TextInput::make('number')
                            ->label('Grade Number')
                            ->required()
                            ->numeric()
                            ->minValue(fn (Get $get) => Program::find($get('program_id'))?->min_grade ?? 1)
                            ->maxValue(fn (Get $get) => Program::find($get('program_id'))?->max_grade ?? 12)
                            ->step(1)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('program_id', $get('program_id')),
                            )
                            ->helperText('Must fall within the selected Program\'s grade range, and unique within it'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(50)
                            ->helperText('e.g., "9. Sınıf"'),

                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3),

                        TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Lower values appear first within the Program'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive grades are hidden from students'),
                    ])->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Max 60 characters for search engines'),

                        TextInput::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->helperText('Max 160 characters for search engines'),
                    ])->columns(2)->collapsible()->collapsed(),

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

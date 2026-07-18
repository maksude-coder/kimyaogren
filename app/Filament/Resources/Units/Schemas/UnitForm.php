<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Grade;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Unit Information')
                    ->schema([
                        Select::make('grade_id')
                            ->label('Grade')
                            ->relationship('grade', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Grade $record) => "{$record->program->name} — {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('The unit code must be unique within the selected Grade.'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('grade_id', $get('grade_id')),
                            )
                            ->helperText('MEB unit code, e.g. "9.1" — unique within the selected Grade'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100),

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
                            ->helperText('Lower values appear first within the Grade'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive units are hidden from students'),
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

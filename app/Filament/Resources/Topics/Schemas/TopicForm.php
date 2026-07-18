<?php

declare(strict_types=1);

namespace App\Filament\Resources\Topics\Schemas;

use App\Enums\TopicStatus;
use App\Models\Unit;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class TopicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Topic Information')
                    ->schema([
                        Select::make('unit_id')
                            ->label('Unit')
                            ->relationship('unit', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Unit $record) => "{$record->grade->program->name} — {$record->grade->name} — {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('The slug below is validated for uniqueness against this Unit.'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->disabled()
                            ->dehydrated()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('unit_id', $get('unit_id')),
                            )
                            ->helperText('Auto-generated from name — unique within the selected Unit'),

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
                            ->helperText('Lower values appear first within the Unit'),

                        TextInput::make('difficulty')
                            ->label('Difficulty')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1)
                            ->helperText('1 (very easy) to 5 (very hard)'),
                    ])->columns(2),

                Section::make('Status & Visibility')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(TopicStatus::options())
                            ->default(TopicStatus::Draft->value)
                            ->required()
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive topics are hidden from students'),
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

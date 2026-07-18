<?php

declare(strict_types=1);

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Hidden fields
                Hidden::make('uuid'),

                // Primary Section
                Section::make('Program Information')
                    ->schema([
                        Select::make('primary_language')
                            ->label('Primary Language')
                            ->options([
                                'tr' => 'Turkish',
                                'en' => 'English',
                            ])
                            ->required()
                            ->default('tr')
                            ->native(false),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            ->helperText('Machine-readable code (e.g., HS_CHEM_TR)'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(150)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(180)
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->helperText('Auto-generated from name'),

                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(5000)
                            ->columnSpanFull()
                            ->rows(4),

                        TextInput::make('short_description')
                            ->label('Short Description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('supported_languages')
                            ->label('Supported Languages')
                            ->options([
                                'tr' => 'Turkish',
                                'en' => 'English',
                            ])
                            ->multiple()
                            ->default(['tr'])
                            ->required()
                            ->preload()
                            ->native(false)
                            ->helperText('Select all supported languages'),
                    ])->columns(2),

                // Grade Range Section
                Section::make('Grade Range')
                    ->schema([
                        TextInput::make('min_grade')
                            ->label('Minimum Grade')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(9)
                            ->step(1),

                        TextInput::make('max_grade')
                            ->label('Maximum Grade')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(12)
                            ->step(1)
                            ->gte('min_grade'),
                    ])->columns(2),

                // Status & Visibility Section
                Section::make('Status & Visibility')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                1 => 'Draft',
                                2 => 'Published',
                                3 => 'Archived',
                            ])
                            ->default(1)
                            ->required()
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive programs are hidden from students'),

                        TextInput::make('display_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Lower values appear first'),
                    ])->columns(2),

                // Versioning Section
                Section::make('Versioning')
                    ->schema([
                        TextInput::make('version')
                            ->label('Version')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->disabled(),

                        Select::make('current_version_id')
                            ->label('Current Version')
                            ->relationship('currentVersion', 'id')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Version {$record->version_number} ({$record->created_at->format('Y-m-d')})"),
                    ])->columns(2),

                // SEO Section
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

                        TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->maxLength(255)
                            ->helperText('Comma-separated keywords'),

                        FileUpload::make('og_image_path')
                            ->label('Open Graph Image')
                            ->image()
                            ->directory('programs/og')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])->columns(2),

                // Settings Section
                Section::make('Settings')
                    ->schema([
                        Toggle::make('settings.spaced_repetition')
                            ->label('Spaced Repetition')
                            ->default(true),

                        Toggle::make('settings.gamification')
                            ->label('Gamification')
                            ->default(false),

                        Toggle::make('settings.parent_portal')
                            ->label('Parent Portal')
                            ->default(false),

                        Toggle::make('settings.offline_mode')
                            ->label('Offline Mode')
                            ->default(true),
                    ])->columns(2)->collapsible()->collapsed(),

                // Audit Fields (Hidden by default)
                Section::make('Audit')
                    ->schema([
                        Placeholder::make('created_by')
                            ->label('Created By')
                            ->content(fn ($record) => $record?->creator?->name ?? '—'),

                        Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(fn ($record) => $record?->updater?->name ?? '—'),

                        Placeholder::make('published_at')
                            ->label('Published At')
                            ->content(fn ($record) => $record?->published_at?->format('Y-m-d H:i') ?? '—'),

                        Placeholder::make('archived_at')
                            ->label('Archived At')
                            ->content(fn ($record) => $record?->archived_at?->format('Y-m-d H:i') ?? '—'),

                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i') ?? '—'),

                        Placeholder::make('updated_at')
                            ->label('Updated At')
                            ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i') ?? '—'),
                    ])->columns(3)->collapsible()->collapsed(),
            ]);
    }
}

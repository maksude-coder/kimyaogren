# Filament Standards

These standards apply to **all** Filament resources, pages, widgets, and components.

---

## Core Principles

| Principle | Description |
|-----------|-------------|
| **Resources = UI Only** | No business logic in Resources |
| **Forms = Presentation** | Forms define fields, not validation logic |
| **Tables = Display** | Tables define columns, not data transformation |
| **Actions = Triggers** | Actions dispatch to services/jobs, don't contain logic |
| **Keep Resources Thin** | Delegate to Models, Services, Policies |

---

## Resource Structure

### File Organization

```
app/Filament/
├── Resources/
│   ├── TopicResource/
│   │   ├── TopicResource.php
│   │   ├── Pages/
│   │   │   ├── ListTopics.php
│   │   │   ├── CreateTopic.php
│   │   │   ├── EditTopic.php
│   │   │   └── ViewTopic.php
│   │   ├── RelationManagers/
│   │   │   └── QuestionsRelationManager.php
│   │   └── Widgets/
│   │       └── TopicStatsWidget.php
│   └── QuestionResource/
│       └── ...
├── Pages/
│   └── Dashboard.php
└── Widgets/
    └── StatsOverviewWidget.php
```

### Base Resource Template

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Models\Topic;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Curriculum';
    
    protected static ?int $navigationSort = 10;
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema(self::getFormSchema());
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions(self::getTableBulkActions());
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
            'view' => Pages\ViewTopic::route('/{record}'),
        ];
    }
    
    // Schema methods - static for reusability
    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Topic Information')
                ->schema([
                    Forms\Components\Select::make('unit_id')
                        ->relationship('unit', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->rules(['alpha_dash']),
                    
                    Forms\Components\Textarea::make('description')
                        ->maxLength(1000)
                        ->columnSpanFull(),
                    
                    Forms\Components\TextInput::make('order')
                        ->required()
                        ->numeric()
                        ->default(fn () => static::getModel()::max('order') + 1),
                ])->columns(2),
        ];
    }
    
    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('unit.name')
                ->label('Unit')
                ->sortable()
                ->searchable(),
            
            Tables\Columns\TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->weight('medium'),
            
            Tables\Columns\TextColumn::make('slug')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            
            Tables\Columns\TextColumn::make('questions_count')
                ->counts('questions')
                ->label('Questions')
                ->sortable()
                ->alignCenter(),
            
            Tables\Columns\TextColumn::make('order')
                ->sortable()
                ->alignCenter(),
            
            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->sortable()
                ->alignCenter(),
            
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
    
    public static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('unit_id')
                ->relationship('unit', 'name')
                ->searchable()
                ->preload(),
            
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Active Status'),
        ];
    }
    
    public static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }
    
    public static function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
```

---

## Forms Standards

### Field Patterns

```php
// ✓ GOOD - Required string
Forms\Components\TextInput::make('name')
    ->required()
    ->maxLength(255)
    ->unique(ignoreRecord: true),

// ✓ GOOD - Optional textarea
Forms\Components\Textarea::make('description')
    ->maxLength(1000)
    ->columnSpanFull(),

// ✓ GOOD - Relationship select
Forms\Components\Select::make('unit_id')
    ->relationship('unit', 'name')
    ->required()
    ->searchable()
    ->preload()
    ->getOptionLabelFromRecordUsing(fn (Unit $record) => "{$record->grade->name} - {$record->name}"),

// ✓ GOOD - Boolean toggle
Forms\Components\Toggle::make('is_active')
    ->label('Active')
    ->default(true),

// ✓ GOOD - Enum select
Forms\Components\Select::make('difficulty')
    ->options(QuestionDifficulty::options())
    ->required()
    ->native(false),

// ✓ GOOD - File upload
Forms\Components\FileUpload::make('image')
    ->image()
    ->directory('topics')
    ->maxSize(2048)
    ->columnSpanFull(),
```

### Section Organization

```php
public static function getFormSchema(): array
{
    return [
        // Primary section - required fields first
        Forms\Components\Section::make('Basic Information')
            ->schema([...])
            ->columns(2),
        
        // Secondary section - optional content
        Forms\Components\Section::make('Content')
            ->schema([...])
            ->columns(1)
            ->collapsible(),
        
        // Meta section - SEO, ordering, status
        Forms\Components\Section::make('Settings')
            ->schema([...])
            ->columns(2)
            ->collapsible()
            ->collapsed(),
    ];
}
```

---

## Tables Standards

### Column Patterns

```php
// Text column with sorting/search
Tables\Columns\TextColumn::make('name')
    ->sortable()
    ->searchable()
    ->weight('medium'),

// Relationship column
Tables\Columns\TextColumn::make('unit.name')
    ->label('Unit')
    ->sortable(query: fn (Builder $query, string $direction) => 
        $query->orderBy('units.name', $direction)
    )
    ->searchable(),

// Count column
Tables\Columns\TextColumn::make('questions_count')
    ->counts('questions')
    ->label('Questions')
    ->sortable()
    ->alignCenter(),

// Badge column for enums/status
Tables\Columns\BadgeColumn::make('difficulty')
    ->colors([
        'success' => QuestionDifficulty::Easy->value,
        'warning' => QuestionDifficulty::Medium->value,
        'danger' => QuestionDifficulty::Hard->value,
    ])
    ->formatStateUsing(fn ($state) => QuestionDifficulty::tryFrom($state)?->label()),

// Boolean/icon column
Tables\Columns\IconColumn::make('is_active')
    ->boolean()
    ->trueIcon('heroicon-o-check-circle')
    ->falseIcon('heroicon-o-x-circle')
    ->trueColor('success')
    ->falseColor('danger')
    ->alignCenter(),

// Date column
Tables\Columns\TextColumn::make('created_at')
    ->dateTime('d M Y H:i')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

// Image column
Tables\Columns\ImageColumn::make('image')
    ->circular()
    ->size(40),
```

### Default Table Configuration

```php
public static function table(Table $table): Table
{
    return $table
        ->columns(self::getTableColumns())
        ->filters(self::getTableFilters())
        ->actions(self::getTableActions())
        ->bulkActions(self::getTableBulkActions())
        ->defaultSort('created_at', 'desc')
        ->paginated([10, 25, 50, 100])
        ->defaultPaginationPageOption(25)
        ->striped()
        ->poll('30s'); // For real-time updates
}
```

---

## Actions Standards

### Action Pattern

```php
// ✓ GOOD - Simple action delegating to service
Tables\Actions\Action::make('duplicate')
    ->label('Duplicate')
    ->icon('heroicon-o-document-duplicate')
    ->action(function (Topic $record): void {
        $newRecord = $record->replicate();
        $newRecord->name = "{$record->name} (Copy)";
        $newRecord->slug = "{$record->slug}-copy-" . Str::random(4);
        $newRecord->save();
        
        // Replicate relationships
        foreach ($record->questions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->topic_id = $newRecord->id;
            $newQuestion->save();
        }
        
        Notification::make()
            ->title('Topic duplicated successfully')
            ->success()
            ->send();
    })
    ->requiresConfirmation()
    ->modalHeading('Duplicate Topic')
    ->modalDescription('This will create a copy of the topic with all its questions.')

// ✗ BAD - Business logic in action
Tables\Actions\Action::make('publish')
    ->action(function (Topic $record): void {
        // 50 lines of publishing logic here - WRONG
    })
```

### Bulk Actions

```php
public static function getTableBulkActions(): array
{
    return [
        Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make(),
            
            Tables\Actions\BulkAction::make('activate')
                ->label('Activate')
                ->icon('heroicon-o-check-circle')
                ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(),
            
            Tables\Actions\BulkAction::make('deactivate')
                ->label('Deactivate')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(),
        ]),
    ];
}
```

---

## Relation Managers

### Standard Relation Manager

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\TopicResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $recordTitleAttribute = 'content';
    
    protected static ?string $title = 'Questions';
    
    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema(QuestionResource::getFormSchema());
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors(QuestionType::colors()),
                
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->tooltip(fn (Question $record) => $record->content),
                
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->colors(QuestionDifficulty::colors()),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(QuestionType::options()),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options(QuestionDifficulty::options()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
```

---

## Filters Standards

### Filter Types

```php
public static function getTableFilters(): array
{
    return [
        // Select filter for relationships
        Tables\Filters\SelectFilter::make('unit_id')
            ->label('Unit')
            ->relationship('unit', 'name')
            ->searchable()
            ->preload()
            ->multiple(),
        
        // Ternary for boolean
        Tables\Filters\TernaryFilter::make('is_active')
            ->label('Active')
            ->trueLabel('Active only')
            ->falseLabel('Inactive only')
            ->native(false),
        
        // Enum filter
        Tables\Filters\SelectFilter::make('difficulty')
            ->options(QuestionDifficulty::options())
            ->multiple(),
        
        // Date range
        Tables\Filters\Filter::make('created_at')
            ->form([
                Forms\Components\DatePicker::make('created_from')
                    ->label('Created From'),
                Forms\Components\DatePicker::make('created_until')
                    ->label('Created Until'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when($data['created_from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                    ->when($data['created_until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                if ($data['created_from'] ?? null) {
                    $indicators[] = 'Created from ' . Carbon::parse($data['created_from'])->format('d M Y');
                }
                if ($data['created_until'] ?? null) {
                    $indicators[] = 'Created until ' . Carbon::parse($data['created_until'])->format('d M Y');
                }
                return $indicators;
            }),
    ];
}
```

---

## Navigation & Grouping

### Navigation Structure

```php
protected static ?string $navigationGroup = 'Curriculum';
protected static ?int $navigationSort = 10;

// Groups (configured in PanelProvider)
->navigationGroups([
    'Curriculum' => ['Units', 'Topics', 'Learning Objectives'],
    'Content' => ['Theory Articles', 'Media', 'Simulations'],
    'Assessment' => ['Questions', 'Quizzes', 'Exams'],
    'Users' => ['Students', 'Teachers', 'Admins'],
    'Analytics' => ['Progress', 'Reports', 'Insights'],
])
```

### Navigation Badge

```php
public static function getNavigationBadge(): ?string
{
    return static::getModel()::where('is_active', true)->count();
}

public static function getNavigationBadgeColor(): ?string
{
    return static::getModel()::where('is_active', false)->exists() ? 'warning' : 'success';
}
```

---

## Widgets

### Stats Widget

```php
<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Topic;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurriculumStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Units', Unit::count())
                ->description('Curriculum units')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),
            
            Stat::make('Total Topics', Topic::count())
                ->description('Learning topics')
                ->descriptionIcon('heroicon-o-tag')
                ->color('success'),
            
            Stat::make('Total Questions', Question::count())
                ->description('Practice questions')
                ->descriptionIcon('heroicon-o-question-mark-circle')
                ->color('info'),
            
            Stat::make('Active Students', User::role('student')->where('is_active', true)->count())
                ->description('Registered learners')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
        ];
    }
}
```

---

## Customization

### Override Default Behavior

```php
// Customize create/edit form layout
public function create(CreateAction $action): void
{
    $action->modalWidth('7xl');
}

// Customize table query
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->withCount('questions')
        ->with('unit.grade');
}

// Customize record title
public static function getRecordTitleAttribute(): string
{
    return 'name';
}

// Customize globally searchable columns
public static function getGloballySearchableAttributes(): array
{
    return ['name', 'slug', 'description'];
}
```

---

## Prohibited Patterns

| ❌ Prohibited | ✅ Alternative |
|--------------|----------------|
| Business logic in Resource | Service class, Model method, Policy |
| Validation in Form schema | Form Request class |
| Complex queries in Table | Eloquent scopes, Query builders |
| Database writes in Actions | Jobs, Services, Model events |
| Inline closures > 10 lines | Dedicated methods/classes |
| Custom HTML in columns | Column types, View components |

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*
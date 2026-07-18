# Filament Resource Checklist

## Purpose

Quality gate for every Filament Resource. Ensures admin panel consistency and maintainability.

---

## Resource Structure

```
app/Filament/Resources/
└── TopicResource/
    ├── TopicResource.php
    ├── Pages/
    │   ├── ListTopics.php
    │   ├── CreateTopic.php
    │   ├── EditTopic.php
    │   └── ViewTopic.php
    ├── RelationManagers/
    │   ├── QuestionsRelationManager.php
    │   └── LearningObjectivesRelationManager.php
    └── Widgets/
        └── TopicStatsWidget.php
```

---

## Main Resource Class

### Required Properties
- [ ] `protected static ?string $model = Topic::class;`
- [ ] `protected static ?string $navigationIcon = 'heroicon-o-book-open';`
- [ ] `protected static ?string $navigationGroup = 'Curriculum';`
- [ ] `protected static ?int $navigationSort = 10;`
- [ ] `protected static ?string $recordTitleAttribute = 'name';`

### Navigation Badge (if applicable)
- [ ] `getNavigationBadge()` returns count
- [ ] `getNavigationBadgeColor()` for status indication

### Methods
- [ ] `form()` uses static schema method
- [ ] `table()` uses static columns/filters/actions methods
- [ ] `getRelations()` returns RelationManager classes
- [ ] `getPages()` returns page routes
- [ ] `getEloquentQuery()` eager loads relationships
- [ ] `getGloballySearchableAttributes()` for global search

---

## Form Schema

### Structure
- [ ] Sections for logical grouping
- [ ] Required fields first
- [ ] `columns(2)` for compact fields
- [ ] `columnSpanFull()` for textareas

### Field Types

| Field | Usage |
|-------|-------|
| `Select::make('parent_id')->relationship()` | BelongsTo |
| `TextInput::make('name')->required()->maxLength(100)` | Short text |
| `TextInput::make('slug')->unique(ignoreRecord: true)` | Slug |
| `Textarea::make('description')->columnSpanFull()` | Long text |
| `TextInput::make('sort_order')->numeric()->default(fn => Model::max('sort_order') + 1)` | Order |
| `Toggle::make('is_active')->default(true)` | Boolean |
| `Select::make('status')->options(StatusEnum::options())` | Enum |
| `FileUpload::make('image')->image()->directory('topics')` | Media |
| `RichEditor::make('content')->columnSpanFull()` | Rich text |

### Validation
- [ ] All validation in Form Request (`StoreTopicRequest`, `UpdateTopicRequest`)
- [ ] Form uses `->rules()` only for UI hints, not validation
- [ ] Unique rules use `ignoreRecord: true`

### Relationship Fields
- [ ] `relationship('parent', 'name')` loads related
- [ ] `searchable()` for > 20 options
- [ ] `preload()` for < 50 options
- [ ] `getOptionLabelFromRecordUsing()` for custom labels

---

## Table Configuration

### Columns
- [ ] `TextColumn::make('parent.name')->label('Unit')->sortable()->searchable()`
- [ ] `TextColumn::make('name')->searchable()->weight('medium')`
- [ ] `TextColumn::make('sort_order')->sortable()->alignCenter()`
- [ ] `BadgeColumn::make('status')->colors(StatusEnum::colors())->formatStateUsing(fn => StatusEnum::tryFrom($state)?->label())`
- [ ] `IconColumn::make('is_active')->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')->alignCenter()`
- [ ] `TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)`

### Default Sort
- [ ] `->defaultSort('sort_order', 'asc')` or `created_at desc`

### Pagination
- [ ] `->paginated([10, 25, 50, 100])`
- [ ] `->defaultPaginationPageOption(25)`

### Styling
- [ ] `->striped()`
- [ ] `->poll('30s')` for real-time updates

---

## Filters

### Standard Filters
- [ ] `SelectFilter::make('parent_id')->relationship('parent', 'name')->searchable()->preload()->multiple()`
- [ ] `TernaryFilter::make('is_active')->label('Active')`
- [ ] `SelectFilter::make('status')->options(StatusEnum::options())->multiple()`
- [ ] Date range filter for `created_at`

### Filter Indicators
- [ ] `indicateUsing()` returns human-readable filter description

---

## Actions

### Row Actions
- [ ] `ViewAction::make()`
- [ ] `EditAction::make()`
- [ ] `DeleteAction::make()->requiresConfirmation()`
- [ ] Custom actions use `Action::make()->action(fn => Service::execute())`
- [ ] Custom actions require confirmation for destructive ops
- [ ] Custom actions show modal with heading/description
- [ ] Actions use Filament Notifications for feedback

### Bulk Actions
- [ ] `DeleteBulkAction::make()`
- [ ] `BulkAction::make('activate')->action(fn => $records->each->update(['is_active' => true]))`
- [ ] `BulkAction::make('deactivate')->color('danger')`
- [ ] `deselectRecordsAfterCompletion()`
- [ ] `requiresConfirmation()`

---

## Relation Managers

### Structure
```php
class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    protected static ?string $recordTitleAttribute = 'content';
    protected static ?string $title = 'Questions';
    
    public function form(Form $form): Form { return $form->schema(QuestionResource::getFormSchema()); }
    public function table(Table $table): Table { return $table->columns([...])->headerActions([CreateAction::make()])->actions([...])->bulkActions([...]); }
}
```

### Checklist
- [ ] `$relationship` matches model relationship name
- [ ] `$recordTitleAttribute` for breadcrumbs
- [ ] `$title` for section header
- [ ] `form()` reuses Resource schema
- [ ] `table()` has relevant columns
- [ ] `headerActions()` includes `CreateAction::make()`
- [ ] `actions()` includes Edit/Delete

---

## Widgets

### Resource Widgets
- [ ] Stats widget for key metrics
- [ ] Chart widget if applicable
- [ ] Registered in `getWidgets()`

---

## Pages

### List Page
- [ ] Extends `ListRecords`
- [ ] Custom header actions if needed

### Create/Edit Page
- [ ] Extends `CreateRecord` / `EditRecord`
- [ ] `redirectUrl()` returns to list
- [ ] Custom `mutateFormDataBeforeCreate()` if needed

### View Page
- [ ] Extends `ViewRecord`
- [ ] Infolists for read-only display
- [ ] `getHeaderActions()` includes Edit/Delete

---

## Authorization

- [ ] Resource uses Policies: `TopicPolicy`
- [ ] `canViewAny()`, `canView()`, `canCreate()`, `canUpdate()`, `canDelete()`
- [ ] Relation Managers check `$record->can('update')`
- [ ] Actions check `$record->can('customAction')`

---

## Performance

- [ ] `getEloquentQuery()` eager loads: `with(['parent', 'childrenCount', 'questionsCount'])`
- [ ] Table uses `withCount()` for counts
- [ ] No N+1 in columns (relationship columns use `getStateUsing` with loaded relations)
- [ ] Filters use `query()` modifier correctly

---

## Internationalization

- [ ] Labels use `__('filament.resources.topic.fields.name')`
- [ ] Placeholders translated
- [ ] Validation messages in Form Request translated
- [ ] Enum labels translated

---

## Testing

- [ ] Feature test: List page loads
- [ ] Feature test: Create works
- [ ] Feature test: Edit works
- [ ] Feature test: Delete works
- [ ] Feature test: Authorization enforced
- [ ] Feature test: Relation Manager CRUD
- [ ] Feature test: Filters work

---

## Common Mistakes (Auto-Reject)

| Mistake | Fix |
|---------|-----|
| Business logic in Resource | Move to Action/Service/Model |
| Validation in `form()` | Use Form Request |
| Inline closures > 10 lines | Extract to static methods |
| Hardcoded strings | Use translation keys |
| Missing eager loading | Add to `getEloquentQuery()` |
| No authorization | Add Policy |
| Custom action does DB write directly | Delegate to Action class |

---

*Every Filament Resource PR must pass this checklist.*
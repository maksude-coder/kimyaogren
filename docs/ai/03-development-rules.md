# Development Rules

These rules are **mandatory** for all code written by the AI Assistant. No exceptions.

---

## Core Principles

| Principle | Description |
|-----------|-------------|
| **Ask First** | Never guess. If unclear → ask Project Manager |
| **No Prototypes** | Every line of code is production quality |
| **Task Isolation** | Only modify files for the current task |
| **Follow Conventions** | Laravel first, Filament first, custom last |
| **Documentation First** | Code follows approved docs, never the reverse |

---

## Code Quality Rules

### 1. Laravel Conventions First

```php
// ✓ GOOD - Use Laravel conventions
User::query()->where('active', true)->get();
$request->validate(['email' => 'required|email']);
User::factory()->create();

// ✗ BAD - Custom abstractions when Laravel provides them
UserRepository::getActiveUsers();
CustomValidator::validateEmail($request);
UserGenerator::generate();
```

### 2. Filament Conventions First

```php
// ✓ GOOD - Use Filament conventions
protected static ?string $navigationIcon = 'heroicon-o-user';
protected static ?string $model = User::class;
public static function getPages(): array { ... }

// ✗ BAD - Custom admin panel patterns
public function customIndex() { ... }
protected function buildCustomTable() { ... }
```

### 3. No Premature Abstraction

```php
// ✓ GOOD - Direct, simple code
public function store(StoreUserRequest $request): RedirectResponse
{
    User::create($request->validated());
    return redirect()->route('users.index');
}

// ✗ BAD - Unnecessary service layer for simple CRUD
public function store(StoreUserRequest $request): RedirectResponse
{
    $this->userService->create($request->validated());
    return redirect()->route('users.index');
}
```

**Exception**: Create services only when:
- Business logic is complex (>50 lines)
- Logic is reused in 3+ places
- Explicitly requested by Architect

### 4. Strict Typing Always

```php
<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### 5. PHPDoc for Public API

```php
/**
 * Calculate the user's mastery level for a topic.
 *
 * @param int $userId The user ID
 * @param int $topicId The topic ID
 * @return float Mastery percentage (0-100)
 */
public function calculateMastery(int $userId, int $topicId): float
{
    // implementation
}
```

---

## Process Rules

### 1. Task Workflow (Mandatory)

Every task follows **exactly** these steps:

```
1. ANALYZE     → Read task spec, understand requirements
2. DOCUMENT    → Create/update Data Dictionary (if DB changes)
3. IMPLEMENT   → Write code following all conventions
4. TEST        → Write and run tests
5. REVIEW      → Self-review, request PM review
```

**Never skip steps. Never reorder steps.**

### 2. Task Isolation

```bash
# ✓ GOOD - Only files for current task
git status
# modified:   database/migrations/xxx_create_topics_table.php
# new:        app/Models/Topic.php
# new:        app/Filament/Resources/TopicResource.php

# ✗ BAD - Unrelated changes
git status
# modified:   database/migrations/xxx_create_topics_table.php
# modified:   app/Models/User.php          ← UNRELATED!
# modified:   config/app.php               ← UNRELATED!
```

### 3. No Drive Implementation only touches files for the current task.

### 4. Data Dictionary Required

**No database migration without approved Data Dictionary.**

| Scenario | Action |
|----------|--------|
| New table | Create Data Dictionary → Architect approval → Migration |
| Column change | Update Data Dictionary → Architect approval → Migration |
| Index change | Update Data Dictionary → Architect approval → Migration |

### 5. Testing Required

| Code Type | Test Requirement |
|-----------|------------------|
| New feature | Feature test + Factory |
| Bug fix | Regression test |
| Service class | Unit test |
| Filament Resource | Feature test (CRUD) |
| API endpoint | Feature test |

**Minimum coverage**: New code must have tests. No exceptions.

---

## Git Rules

### Commit Messages

```
type(scope): description

feat(users): add user profile resource
fix(auth): resolve login redirect loop
test(questions): add question factory tests
docs(api): update authentication endpoints
refactor(models): extract common scopes
```

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `test` | Test addition/update |
| `docs` | Documentation |
| `refactor` | Code restructuring |
| `style` | Formatting only |
| `chore` | Maintenance |

### Branch Naming

```
feature/task-description
fix/task-description
refactor/task-description
```

---

## Laravel-Specific Rules

### Models

```php
// ✓ GOOD
class Topic extends Model
{
    protected $fillable = ['name', 'unit_id', 'order'];
    protected $casts = ['order' => 'integer'];
    
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
}

// ✗ BAD
class Topic extends Model
{
    protected $guarded = [];  // Never use $guarded
    public $timestamps = false;  // Always use timestamps unless justified
}
```

### Migrations

```php
// ✓ GOOD
Schema::create('topics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
    $table->string('name', 100);
    $table->unsignedSmallInteger('order')->default(0);
    $table->timestamps();
    
    $table->index(['unit_id', 'order']);
    $table->unique(['unit_id', 'name']);
});

// ✗ BAD
Schema::create('topics', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('unit_id');  // No FK!
    $table->string('name');
    $table->integer('order');  // Not unsigned
    // No indexes, no constraints
});
```

### Requests

```php
// ✓ GOOD
class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Topic::class);
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('topics')->where('unit_id', $this->unit_id)],
            'unit_id' => ['required', 'exists:units,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

---

## Filament-Specific Rules

### Resources

```php
// ✓ GOOD
class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Curriculum';
    protected static ?int $navigationSort = 10;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('unit_id')
                ->relationship('unit', 'name')
                ->required(),
            TextInput::make('name')
                ->required()
                ->maxLength(100),
            TextInput::make('order')
                ->numeric()
                ->default(0),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit.name')->label('Unit'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('order')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                SelectFilter::make('unit_id')->relationship('unit', 'name'),
            ]);
    }
}
```

### Actions

```php
// ✓ GOOD - Use Filament Actions
Tables\Actions\Action::make('duplicate')
    ->label('Duplicate')
    ->icon('heroicon-o-document-duplicate')
    ->requiresConfirmation()
    ->action(fn (Topic $record) => $record->replicate()->push());
```

---

## Database Rules

| Rule | Mandatory |
|------|-----------|
| Every table has `id` (bigint, unsigned, PK) | ✓ |
| Every table has `created_at`, `updated_at` | ✓ |
| Foreign keys on ALL relationships | ✓ |
| `cascadeOnDelete()` for owned relationships | ✓ |
| Indexes on all FK columns | ✓ |
| Unique constraints where business logic requires | ✓ |
| `unsignedSmallInteger` for order/sort columns | ✓ |
| `string` with explicit length (not `text` unless needed) | ✓ |
| Enum columns use `tinyInteger` + PHP Enum | ✓ |

---

## Testing Rules

```php
// ✓ GOOD - Pest feature test
test('user can create topic', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $unit = Unit::factory()->create();
    
    $this->actingAs($user)
        ->post(route('topics.store'), [
            'name' => 'Atomic Structure',
            'unit_id' => $unit->id,
        ])
        ->assertRedirect(route('topics.index'));
    
    expect(Topic::where('name', 'Atomic Structure'))->toExist();
});

// ✗ BAD - No assertions, no factory, no auth
test('create topic', function () {
    Topic::create(['name' => 'Test', 'unit_id' => 1]);
});
```

---

## Prohibited Patterns

| Pattern | Reason |
|---------|--------|
| `$guarded = []` | Security risk |
| `DB::raw()` in migrations | Use Blueprint methods |
| `Nullable` without justification | Data integrity |
| Logic in Blade views | Separation of concerns |
| `config()` in models | Testability |
| Static facades in models | Testability |
| Custom base controllers | Laravel provides what's needed |
| Repositories for simple CRUD | YAGNI |

---

## Review Checklist (Self-Review Before PM Review)

- [ ] Task requirements fully implemented
- [ ] All conventions followed (Laravel, Filament, DB, Code)
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] No unrelated files modified
- [ ] Commit messages follow convention
- [ ] No `dd()`, `dump()`, `ray()` in code
- [ ] Strict typing enabled
- [ ] PHPDoc on public methods

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*
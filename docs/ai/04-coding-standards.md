# Coding Standards

This document defines the coding standards for all PHP code in the KimyaÖğren project.

---

## PHP Version & Configuration

- **PHP 8.3+** (minimum)
- **strict_types=1** on every file
- **PSR-12** coding style (enforced by Laravel Pint)

---

## File Structure

### File Header (Required)

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

### Namespace Organization

| Type | Namespace |
|------|-----------|
| Models | `App\Models` |
| Enums | `App\Enums` |
| Actions/Services | `App\Actions` |
| Events | `App\Events` |
| Listeners | `App\Listeners` |
| Jobs | `App\Jobs` |
| Policies | `App\Policies` |
| Form Requests | `App\Http\Requests` |
| API Resources | `App\Http\Resources` |
| Filament Resources | `App\Filament\Resources` |
| Filament Widgets | `App\Filament\Widgets` |
| View Components | `App\View\Components` |
| Validation Rules | `App\Rules` |

---

## Naming Conventions

### Classes & Interfaces

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `Topic`, `CalculateMastery` |
| Interfaces | PascalCase + `Interface` | `MasteryCalculatorInterface` |
| Traits | PascalCase + `Trait` | `HasUuidTrait` |
| Enums | PascalCase (singular) | `UserRole`, `QuestionType` |
| Exceptions | PascalCase + `Exception` | `TopicNotFoundException` |
| Jobs | PascalCase + `Job` | `SendWelcomeEmailJob` |
| Events | PascalCase (past tense) | `UserRegistered`, `QuestionAnswered` |
| Listeners | PascalCase + `Listener` | `SendWelcomeEmailListener` |
| Policies | PascalCase + `Policy` | `TopicPolicy` |
| Form Requests | PascalCase + `Request` | `StoreTopicRequest` |
| Factories | PascalCase + `Factory` | `TopicFactory` |
| Seeders | PascalCase + `Seeder` | `CurriculumSeeder` |

### Methods & Properties

| Type | Convention | Example |
|------|------------|---------|
| Methods | camelCase | `getFullName()`, `calculateMastery()` |
| Properties | camelCase | `$firstName`, `$unitId`, `$isActive` |
| Constants | UPPER_SNAKE_CASE | `MAX_ATTEMPTS`, `DEFAULT_ORDER` |

### Database

| Type | Convention | Example |
|------|------------|---------|
| Tables | snake_case, plural | `topics`, `user_progress` |
| Columns | snake_case | `first_name`, `unit_id`, `created_at` |
| Foreign Keys | `{table}_{column}_foreign` | `topics_unit_id_foreign` |
| Indexes | `{table}_{columns}_index` | `topics_unit_id_order_index` |
| Unique | `{table}_{columns}_unique` | `topics_unit_id_slug_unique` |

### Routes & Views

| Type | Convention | Example |
|------|------------|---------|
| Route names | kebab-case | `topics.index`, `users.profile.edit` |
| View files | kebab-case | `topics.index.blade.php` |
| Config keys | snake_case | `app.timezone`, `filament.navigation.groups` |

---

## Code Organization

### Class Structure (Order Matters)

```php
class Topic extends Model
{
    // 1. Traits
    use HasFactory, HasUuids, SoftDeletes;
    
    // 2. Constants
    public const MAX_NAME_LENGTH = 100;
    
    // 3. Properties (protected/private first)
    protected $fillable = ['name', 'unit_id', 'order'];
    protected $casts = [
        'order' => 'integer',
        'status' => TopicStatus::class,
    ];
    
    // 4. Boot methods
    protected static function booted(): void
    {
        static::creating(fn (self $topic) => $topic->order ??= 0);
    }
    
    // 5. Relationships
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    
    // 6. Scopes
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
    
    // 7. Accessors / Mutators
    public function getDisplayNameAttribute(): string
    {
        return "{$this->unit->code} - {$this->name}";
    }
    
    // 8. Public methods
    public function getNextTopic(): ?self
    {
        return self::where('unit_id', $this->unit_id)
            ->where('order', '>', $this->order)
            ->ordered()
            ->first();
    }
}
```

---

## Clean Code Principles

### 1. Early Return

```php
// ✓ GOOD
public function store(StoreTopicRequest $request): RedirectResponse
{
    if (! $request->user()->can('create', Topic::class)) {
        abort(403);
    }
    
    $topic = Topic::create($request->validated());
    
    return redirect()->route('topics.index');
}

// ✗ BAD - Nested
public function store(StoreTopicRequest $request): RedirectResponse
{
    if ($request->user()->can('create', Topic::class)) {
        $topic = Topic::create($request->validated());
        return redirect()->route('topics.index');
    }
    
    abort(403);
}
```

### 2. Small Methods (Single Responsibility)

```php
// ✓ GOOD - Each method does one thing
public function calculateMastery(int $userId, int $topicId): float
{
    $correctAnswers = $this->getCorrectAnswers($userId, $topicId);
    $totalAttempts = $this->getTotalAttempts($userId, $topicId);
    
    return $this->calculatePercentage($correctAnswers, $totalAttempts);
}

private function getCorrectAnswers(int $userId, int $topicId): int { /* ... */ }
private function getTotalAttempts(int $userId, int $topicId): int { /* ... */ }
private function calculatePercentage(int $correct, int $total): float { /* ... */ }

// ✗ BAD - One large method
public function calculateMastery(int $userId, int $topicId): float
{
    $attempts = QuestionAttempt::where('user_id', $userId)
        ->whereHas('question', fn ($q) => $q->where('topic_id', $topicId))
        ->get();
    
    $correct = $attempts->where('is_correct', true)->count();
    $total = $attempts->count();
    
    if ($total === 0) {
        return 0.0;
    }
    
    return round(($correct / $total) * 100, 1);
}
```

### 3. Meaningful Names

```php
// ✓ GOOD
$topics = Topic::query()->where('unit_id', $unitId)->ordered()->get();
$masteryPercentage = $this->calculateMastery($userId, $topicId);

// ✗ BAD
$data = Topic::query()->where('unit_id', $u)->orderBy('o')->get();
$mp = $this->calc($uid, $tid);
```

### 4. Avoid Magic Numbers

```php
// ✓ GOOD
public const MAX_ATTEMPTS = 3;
public const DEFAULT_MASTERY_THRESHOLD = 70.0;

if ($attempts >= self::MAX_ATTEMPTS) { ... }
if ($mastery >= self::DEFAULT_MASTERY_THRESHOLD) { ... }

// ✗ BAD
if ($attempts >= 3) { ... }
if ($mastery >= 70.0) { ... }
```

---

## Type System

### Strict Typing (Always)

```php
declare(strict_types=1);

class TopicService
{
    public function __construct(
        private readonly MasteryCalculator $calculator
    ) {}
    
    public function calculateForUser(int $userId, int $topicId): float
    {
        return $this->calculator->calculate($userId, $topicId);
    }
}
```

### Return Types (Always)

```php
// ✓ ALWAYS declare return type
public function getFullName(): string
{
    return "{$this->first_name} {$this->last_name}";
}

public function getTopics(): Collection
{
    return $this->topics;
}

public function getNextTopic(): ?Topic
{
    return $this->topics->first();
}
```

### Parameter Types (Always)

```php
public function calculateMastery(int $userId, int $topicId): float
public function findBySlug(string $slug): ?Topic
public function syncTopics(Collection $topics): void
```

---

## PHPDoc Standards

### Required On

- All public methods
- All protected methods in abstract classes
- Complex return types
- All class properties (if not obvious)

### Format

```php
/**
 * Calculate the user's mastery level for a specific topic.
 *
 * Uses spaced repetition algorithm with weighted scoring
 * based on recency and correctness of answers.
 *
 * @param int $userId The authenticated user ID
 * @param int $topicId The topic to calculate mastery for
 * @return float Mastery percentage from 0.0 to 100.0
 * @throws TopicNotFoundException If topic does not exist
 */
public function calculateMastery(int $userId, int $topicId): float
{
    // ...
}
```

### Property Documentation

```php
/**
 * The topic's display name within its unit.
 *
 * @var string
 */
protected string $name;

/**
 * The unit this topic belongs to.
 *
 * @var \App\Models\Unit
 */
public function unit(): BelongsTo
{
    return $this->belongsTo(Unit::class);
}
```

---

## Enums

### Always Use Backed Enums

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';
    
    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Multiple Choice',
            self::TrueFalse => 'True / False',
            self::ShortAnswer => 'Short Answer',
            self::Essay => 'Essay',
        };
    }
    
    public static function options(): array
    {
        return array_column(self::cases(), 'label', 'value');
    }
}
```

### Integer Enums for Database

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TopicStatus: int
{
    case Draft = 1;
    case Published = 2;
    case Archived = 3;
    
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }
}
```

### Model Cast

```php
class Topic extends Model
{
    protected $casts = [
        'status' => TopicStatus::class,
    ];
}
```

---

## Validation

### Form Requests (Required for All Input)

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Question::class);
    }
    
    public function rules(): array
    {
        return [
            'topic_id' => ['required', 'exists:topics,id'],
            'type' => ['required', 'string', Rule::in(QuestionType::values())],
            'content' => ['required', 'string', 'max:5000'],
            'difficulty' => ['required', 'string', Rule::in(QuestionDifficulty::values())],
            'explanation' => ['nullable', 'string', 'max:10000'],
            'options' => ['required_if:type,multiple_choice', 'array', 'min:2'],
            'options.*.text' => ['required', 'string', 'max:500'],
            'options.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
```

---

## Collections

### Type-Hinted Collections

```php
use Illuminate\Support\Collection;

/** @return Collection<int, Topic> */
public function getActiveTopics(): Collection
{
    return $this->topics()->where('is_active', true)->get();
}

/** @return Collection<int, Question> */
public function getQuestionsByDifficulty(QuestionDifficulty $difficulty): Collection
{
    return $this->questions()->where('difficulty', $difficulty)->get();
}
```

---

## Error Handling

### Exceptions

```php
// Custom exceptions for domain errors
class TopicNotFoundException extends Exception
{
    public function __construct(int $topicId)
    {
        parent::__construct("Topic with ID {$topicId} not found", 404);
    }
}

// Usage
public function findOrFail(int $id): Topic
{
    return Topic::find($id) ?? throw new TopicNotFoundException($id);
}
```

### Try-Catch Only at Boundaries

```php
// Controller - boundary
public function store(StoreTopicRequest $request): RedirectResponse
{
    try {
        $topic = $this->topicService->create($request->validated());
    } catch (TopicCreationFailed $e) {
        return back()->withErrors(['error' => $e->getMessage()]);
    }
    
    return redirect()->route('topics.show', $topic);
}

// Service - throws domain exceptions
public function create(array $data): Topic
{
    if (Topic::where('unit_id', $data['unit_id'])->where('slug', $data['slug'])->exists()) {
        throw new TopicCreationFailed('Slug already exists in this unit');
    }
    
    return Topic::create($data);
}
```

---

## Prohibited Patterns

| Pattern | Reason | Alternative |
|---------|--------|-------------|
| `$guarded = []` | Mass assignment vulnerability | `$fillable = [...]` |
| `DB::raw()` in migrations | Not portable, no type safety | Blueprint methods |
| `config()` in models | Hard to test, hidden dependency | Pass config as parameter |
| Facades in models | Hard to test | Dependency injection |
| `public $timestamps = false` | Breaks conventions | Use `$table->timestamps()` |
| Logic in Blade views | Separation of concerns | View components, accessors |
| `dd()`, `dump()`, `ray()` in code | Debugging only | Remove before commit |
| `TODO`/`FIXME` comments | Track in issue tracker | GitHub Issues |

---

## Laravel Pint

```bash
# Format code
./vendor/bin/pint

# Check only
./vendor/bin/pint --test

# CI runs this
./vendor/bin/pint --test
```

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*
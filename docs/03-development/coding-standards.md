# Coding Standards

## Purpose

Single source of truth for all PHP code style and patterns in KimyaÖğren. Enforced by Laravel Pint.

---

## PHP Version

**PHP 8.3+** with `strict_types=1` on every file.

---

## File Structure

```php
<?php

declare(strict_types=1);

namespace App\<Domain>\<Layer>;

use App\Models\<Model>;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ClassName
{
    // 1. Traits
    use HasFactory, HasUuids;
    
    // 2. Constants
    public const MAX_NAME_LENGTH = 100;
    
    // 3. Properties (protected/private first)
    protected $fillable = ['name', 'unit_id'];
    protected $casts = ['status' => TopicStatus::class];
    
    // 4. Boot methods
    protected static function booted(): void
    {
        static::creating(fn ($model) => $model->sort_order ??= 0);
    }
    
    // 5. Relationships
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    
    // 6. Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TopicStatus::Published);
    }
    
    // 7. Accessors/Mutators
    public function getDisplayNameAttribute(): string
    {
        return "{$this->unit->code} - {$this->name}";
    }
    
    // 8. Public methods
    public function getNextTopic(): ?self
    {
        return self::where('unit_id', $this->unit_id)
            ->where('sort_order', '>', $this->sort_order)
            ->ordered()
            ->first();
    }
}
```

---

## Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Classes | PascalCase | `Topic`, `CalculateMastery` |
| Interfaces | PascalCase + `Interface` | `MasteryCalculatorInterface` |
| Traits | PascalCase + `Trait` | `HasUuidTrait` |
| Enums | PascalCase, singular | `TopicStatus`, `QuestionType` |
| Methods | camelCase | `getFullName()`, `calculateMastery()` |
| Properties | camelCase | `$firstName`, `$unitId` |
| Constants | UPPER_SNAKE_CASE | `MAX_ATTEMPTS` |
| Tables | snake_case, plural | `topics`, `question_options` |
| Columns | snake_case | `sort_order`, `created_by_id` |
| Routes | kebab-case | `topics.index`, `topics.learning-objectives` |
| Views | kebab-case | `topics.index.blade.php` |
| Config keys | snake_case | `app.timezone` |

---

## Type System

### Always Declare

```php
// Properties
protected string $name;
protected int $sortOrder = 0;
protected ?string $description = null;

// Method signatures
public function __construct(
    private readonly MasteryCalculator $calculator
) {}

public function calculate(int $userId, int $topicId): float
{
    // ...
}
```

### Collections

```php
/** @return Collection<int, Topic> */
public function getTopics(): Collection
{
    return Topic::all();
}
```

---

## Clean Code Principles

### Early Return

```php
// ✓ GOOD
public function publish(Topic $topic): void
{
    if ($topic->isPublished()) {
        return;
    }
    
    if (! $topic->hasContent()) {
        throw new TopicNotReadyException();
    }
    
    $topic->update(['status' => TopicStatus::Published]);
}

// ✗ BAD - Arrow code
public function publish(Topic $topic): void
{
    if (! $topic->isPublished()) {
        if ($topic->hasContent()) {
            $topic->update(['status' => TopicStatus::Published]);
        } else {
            throw new TopicNotReadyException();
        }
    }
}
```

### Small Methods

```php
// ✓ GOOD - Single responsibility
public function store(StoreTopicRequest $request): RedirectResponse
{
    $topic = $this->createTopic($request);
    $this->syncQuestions($topic, $request);
    $this->logCreation($topic);
    
    return redirect()->route('topics.index');
}

private function createTopic(StoreTopicRequest $request): Topic
{
    return Topic::create($request->validated());
}

private function syncQuestions(Topic $topic, StoreTopicRequest $request): void
{
    $topic->questions()->sync($request->input('question_ids', []));
}

private function logCreation(Topic $topic): void
{
    activity()->performedOn($topic)->log('created');
}
```

### Meaningful Names

```php
// ✓ GOOD
$topicsWithinUnit = Topic::where('unit_id', $unitId)->ordered()->get();
$masteryThreshold = 70.0;
$hasAchievedMastery = $masteryPercentage >= $masteryThreshold;

// ✗ BAD
$data = Topic::where('unit_id', $u)->orderBy('o')->get();
$mt = 70.0;
$passed = $mp >= $mt;
```

---

## Laravel Patterns

### Models

```php
class Topic extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    
    protected $fillable = [
        'unit_id', 'name', 'slug', 'description', 
        'sort_order', 'status', 'is_active'
    ];
    
    protected $casts = [
        'sort_order' => 'integer',
        'status' => TopicStatus::class,
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    // Relationships with return types
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    
    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TopicStatus::Published);
    }
}
```

### Form Requests

```php
class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Topic::class);
    }
    
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:100', 
                Rule::unique('topics')->where('unit_id', $this->unit_id)
            ],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('topics')->where('unit_id', $this->unit_id)
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', 'integer', Rule::in(TopicStatus::values())],
        ];
    }
}
```

### Policies

```php
class TopicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view curriculum');
    }
    
    public function view(User $user, Topic $topic): bool
    {
        return $topic->is_active || $user->can('manage curriculum');
    }
    
    public function create(User $user): bool
    {
        return $user->can('manage curriculum');
    }
    
    public function update(User $user, Topic $topic): bool
    {
        return $user->can('manage curriculum');
    }
    
    public function delete(User $user, Topic $topic): bool
    {
        return $user->can('manage curriculum');
    }
}
```

### Actions (Single-Use Business Logic)

```php
class CalculateTopicMasteryAction
{
    public function __construct(
        private readonly QuestionAttemptRepository $attempts,
        private readonly SpacedRepetitionCalculator $srCalculator
    ) {}
    
    public function execute(int $userId, int $topicId): float
    {
        $attempts = $this->attempts->getForTopic($userId, $topicId);
        
        if ($attempts->isEmpty()) {
            return 0.0;
        }
        
        $weightedScore = $this->srCalculator->calculate($attempts);
        
        return round($weightedScore, 1);
    }
}
```

### Events & Listeners

```php
// Event
class TopicPublished
{
    public function __construct(
        public readonly Topic $topic,
        public readonly User $publishedBy
    ) {}
}

// Listener
class NotifySubscribersOfNewTopic
{
    public function handle(TopicPublished $event): void
    {
        // Queue notification
        NotifySubscribersJob::dispatch($event->topic);
    }
}
```

---

## Database Patterns

### Migrations

```php
Schema::create('topics', function (Blueprint $table) {
    // 1. PK
    $table->id();
    
    // 2. FKs (dependency order)
    $table->foreignId('unit_id')
        ->constrained()
        ->cascadeOnDelete();
    
    // 3. Business fields
    $table->string('name', 100);
    $table->string('slug', 120);
    $table->text('description')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);
    
    // 4. Status/Type
    $table->tinyInteger('status')->default(1); // TopicStatus::Draft
    $table->boolean('is_active')->default(true);
    
    // 5. SEO (if applicable)
    $table->string('meta_title', 60)->nullable();
    $table->text('meta_description')->nullable();
    
    // 6. Audit
    $table->foreignId('created_by_id')
        ->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by_id')
        ->nullable()->constrained('users')->nullOnDelete();
    
    // 7. Timestamps
    $table->timestamps();
    
    // 8. Soft deletes
    $table->softDeletes();
    
    // 9. Indexes
    $table->index(['unit_id', 'sort_order']);
    $table->unique(['unit_id', 'slug']);
    $table->index(['status', 'created_at']);
});
```

### Factories

```php
class TopicFactory extends Factory
{
    protected $model = Topic::class;
    
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'name' => fake()->unique()->words(3, true),
            'slug' => fn (array $attrs) => Str::slug($attrs['name']),
            'description' => fake()->optional()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(TopicStatus::cases()),
            'is_active' => true,
        ];
    }
    
    public function published(): static
    {
        return $this->state(fn () => [
            'status' => TopicStatus::Published,
            'is_active' => true,
        ]);
    }
}
```

---

## Testing Patterns

### Feature Test

```php
test('admin can create topic', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    
    $response = $this->actingAs($admin)
        ->post(route('topics.store'), [
            'name' => 'New Topic',
            'unit_id' => $unit->id,
            'slug' => 'new-topic',
            'status' => TopicStatus::Draft->value,
        ]);
    
    $response->assertRedirect(route('topics.index'));
    
    expect(Topic::where('name', 'New Topic'))->toExist();
});
```

### Unit Test (Service/Action)

```php
test('mastery calculator returns zero for no attempts', function () {
    $calculator = new MasteryCalculator();
    $result = $calculator->calculate(
        userId: 1,
        topicId: 1,
        attempts: collect()
    );
    
    expect($result)->toBe(0.0);
});
```

---

## Prohibited Patterns

| Pattern | Reason | Alternative |
|---------|--------|-------------|
| `$guarded = []` | Mass assignment vulnerability | `$fillable = [...]` |
| `DB::raw()` in migrations | Not portable, no type safety | Blueprint methods |
| `config()` in models | Hidden dependency, hard to test | Constructor injection |
| Facades in models | Hard to test, tight coupling | Dependency injection |
| `public $timestamps = false` | Breaks conventions | Use `timestamps()` in migration |
| Logic in Blade views | Separation of concerns | View components, accessors |
| `dd()`, `dump()`, `ray()` in code | Debugging only | Remove before commit |
| `TODO`/`FIXME` comments | Track in issue tracker | GitHub Issues |
| Custom base controllers | Unnecessary abstraction | Use Laravel's built-in |

---

## Code Style (Enforced by Pint)

```bash
# Check
./vendor/bin/pint --test

# Fix
./vendor/bin/pint
```

Key rules:
- 4 spaces indentation (no tabs)
- Braces on same line for classes/methods
- Braces next line for control structures
- Trailing commas in multiline arrays
- Single quotes for strings (unless interpolation)
- Type declarations with space: `function foo(string $bar): void`

---

*Run `./vendor/bin/pint --test` before every commit.*
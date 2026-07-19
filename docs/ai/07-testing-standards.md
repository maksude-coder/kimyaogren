# Testing Standards

These standards define the testing approach for the KimyaÖğren project.

> **Correction (Repository Audit / Stabilization Sprint, 2026-07-19):** Epic 1's actual test suite (`tests/Feature/*.php`, `tests/Unit/*.php`) is written entirely in **PHPUnit** class-based syntax (`class FooTest extends TestCase`, `public function test_...()`), not Pest. `pestphp/pest-plugin` appears only in `composer.json`'s `allow-plugins` (a Laravel-skeleton default), and no Pest package is actually installed. The Pest-specific syntax, `Pest.php` config, and `use function Pest\Laravel\...` examples below describe an approach that was never adopted — they predate the PHPUnit-based convention Epic 1 actually established. Treat the *principles* in this document (test structure, coverage targets, what to test) as current; treat all **Pest-syntax code samples and CLI commands** (`./vendor/bin/pest ...`) as historical/superseded — the real commands are `php artisan test` / `vendor/bin/phpunit`, and existing tests under `tests/` are the authoritative style reference.

---

## Testing Philosophy

| Principle | Description |
|-----------|-------------|
| **Test Behavior, Not Implementation** | Test what the code does, not how it does it |
| **Feature Tests First** | Integration > Unit for Laravel applications |
| **Real Database** | Use SQLite (local) / MySQL (CI) - no mocks for Eloquent |
| **Factories Over Fixtures** | Dynamic test data generation |
| **Pest Framework** | Modern, expressive testing syntax |

---

## Test Structure

### Directory Organization

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegisterTest.php
│   ├── Curriculum/
│   │   ├── TopicTest.php
│   │   ├── UnitTest.php
│   │   └── QuestionTest.php
│   ├── Filament/
│   │   ├── TopicResourceTest.php
│   │   └── QuestionResourceTest.php
│   └── Api/
│       └── TopicApiTest.php
├── Unit/
│   ├── Enums/
│   │   └── QuestionTypeTest.php
│   ├── Services/
│   │   └── MasteryServiceTest.php
│   └── Actions/
│       └── CalculateMasteryActionTest.php
├── Pest.php
└── TestCase.php
```

### Base TestCase

```php
<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
}
```

### Pest Configuration

```php
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

pest()->extend(TestCase::class);

// Global setup
beforeEach(function () {
    Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
});

// Global helpers
function actingAsAdmin(): User
{
    return User::factory()->admin()->create();
}

function actingAsStudent(): User
{
    return User::factory()->student()->create();
}

function actingAsTeacher(): User
{
    return User::factory()->teacher()->create();
}
```

---

## Feature Tests

### Standard CRUD Test Pattern

```php
<?php

declare(strict_types=1);

use App\Models\Topic;
use App\Models\Unit;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\get;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('admin can create topic', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    
    $response = actingAs($admin)
        ->post(route('topics.store'), [
            'name' => 'Atomic Structure',
            'unit_id' => $unit->id,
            'description' => 'Basic atomic theory',
            'order' => 1,
        ]);
    
    $response->assertRedirect(route('topics.index'));
    
    assertDatabaseHas('topics', [
        'name' => 'Atomic Structure',
        'unit_id' => $unit->id,
        'order' => 1,
    ]);
});

test('student cannot create topic', function () {
    $student = User::factory()->student()->create();
    $unit = Unit::factory()->create();
    
    actingAs($student)
        ->post(route('topics.store'), [
            'name' => 'Atomic Structure',
            'unit_id' => $unit->id,
        ])
        ->assertForbidden();
});

test('topic name must be unique within unit', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    Topic::factory()->create(['unit_id' => $unit->id, 'name' => 'Atomic Structure']);
    
    actingAs($admin)
        ->post(route('topics.store'), [
            'name' => 'Atomic Structure',
            'unit_id' => $unit->id,
        ])
        ->assertSessionHasErrors('name');
});

test('admin can view topic list', function () {
    $admin = User::factory()->admin()->create();
    $topics = Topic::factory()->count(3)->create();
    
    $response = actingAs($admin)->get(route('topics.index'));
    
    $response->assertOk();
    $topics->each(fn ($topic) => $response->assertSee($topic->name));
});

test('admin can update topic', function () {
    $admin = User::factory()->admin()->create();
    $topic = Topic::factory()->create(['name' => 'Old Name']);
    
    $response = actingAs($admin)
        ->put(route('topics.update', $topic), [
            'name' => 'New Name',
            'unit_id' => $topic->unit_id,
        ]);
    
    $response->assertRedirect(route('topics.index'));
    
    assertDatabaseHas('topics', [
        'id' => $topic->id,
        'name' => 'New Name',
    ]);
});

test('admin can delete topic', function () {
    $admin = User::factory()->admin()->create();
    $topic = Topic::factory()->create();
    
    $response = actingAs($admin)
        ->delete(route('topics.destroy', $topic));
    
    $response->assertRedirect(route('topics.index'));
    
    assertDatabaseMissing('topics', ['id' => $topic->id]);
});
```

### Filament Resource Tests

```php
<?php

declare(strict_types=1);

use App\Filament\Resources\TopicResource;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\User;
use function Pest\Laravel\actingAs;

test('topic resource can be accessed by admin', function () {
    $admin = User::factory()->admin()->create();
    
    $this->actingAs($admin)
        ->get(TopicResource::getUrl('index'))
        ->assertOk();
});

test('topic resource cannot be accessed by student', function () {
    $student = User::factory()->student()->create();
    
    $this->actingAs($student)
        ->get(TopicResource::getUrl('index'))
        ->assertForbidden();
});

test('topic resource create form works', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    
    $this->actingAs($admin)
        ->get(TopicResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Name')
        ->assertSee('Unit');
});

test('topic resource can create record', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    
    $this->actingAs($admin)
        ->post(TopicResource::getUrl('store'), [
            'name' => 'Test Topic',
            'unit_id' => $unit->id,
            'order' => 1,
        ])
        ->assertRedirect(TopicResource::getUrl('index'));
    
    expect(Topic::where('name', 'Test Topic'))->toExist();
});
```

---

## Unit Tests

### Enum Tests

```php
<?php

declare(strict_types=1);

use App\Enums\QuestionType;

test('question type has correct values', function () {
    expect(QuestionType::MultipleChoice->value)->toBe('multiple_choice');
    expect(QuestionType::TrueFalse->value)->toBe('true_false');
});

test('question type label is correct', function () {
    expect(QuestionType::MultipleChoice->label())->toBe('Multiple Choice');
    expect(QuestionType::TrueFalse->label())->toBe('True / False');
});

test('question type options returns key-value array', function () {
    $options = QuestionType::options();
    
    expect($options)
        ->toBeArray()
        ->toHaveKey('multiple_choice', 'Multiple Choice')
        ->toHaveKey('true_false', 'True / False');
});

test('question type cases can be iterated', function () {
    $cases = QuestionType::cases();
    
    expect($cases)->toHaveCount(4);
    expect($cases[0])->toBeInstanceOf(QuestionType::class);
});
```

### Service Tests

```php
<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Topic;
use App\Models\User;
use App\Services\MasteryService;
use function Pest\Laravel\actingAs;

test('mastery service calculates percentage correctly', function () {
    $user = User::factory()->create();
    $topic = Topic::factory()->create();
    $questions = Question::factory()->count(5)->create(['topic_id' => $topic->id]);
    
    // 3 correct, 2 incorrect = 60%
    QuestionAttempt::factory()->count(3)->create([
        'user_id' => $user->id,
        'question_id' => $questions->take(3)->pluck('id')->toArray(),
        'is_correct' => true,
    ]);
    
    QuestionAttempt::factory()->count(2)->create([
        'user_id' => $user->id,
        'question_id' => $questions->skip(3)->pluck('id')->toArray(),
        'is_correct' => false,
    ]);
    
    $service = new MasteryService();
    $mastery = $service->calculateForUser($user, $topic);
    
    expect($mastery)->toBe(60.0);
});

test('mastery service returns 0 for no attempts', function () {
    $user = User::factory()->create();
    $topic = Topic::factory()->create();
    
    $service = new MasteryService();
    $mastery = $service->calculateForUser($user, $topic);
    
    expect($mastery)->toBe(0.0);
});

test('mastery service uses only latest attempt per question', function () {
    $user = User::factory()->create();
    $topic = Topic::factory()->create();
    $question = Question::factory()->create(['topic_id' => $topic->id]);
    
    // First attempt: incorrect
    QuestionAttempt::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
        'is_correct' => false,
        'created_at' => now()->subHour(),
    ]);
    
    // Second attempt: correct
    QuestionAttempt::factory()->create([
        'user_id' => $user->id,
        'question_id' => $question->id,
        'is_correct' => true,
        'created_at' => now(),
    ]);
    
    $service = new MasteryService();
    $mastery = $service->calculateForUser($user, $topic);
    
    expect($mastery)->toBe(100.0); // Only latest attempt counts
});
```

---

## Factories

### Model Factory Standards

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TopicStatus;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    protected $model = Topic::class;
    
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'name' => fake()->unique()->words(3, true),
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'description' => fake()->optional()->paragraph(),
            'order' => fake()->numberBetween(0, 100),
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
    
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => TopicStatus::Draft,
            'is_active' => false,
        ]);
    }
}
```

### Factory States for Testing

```php
// Usage in tests
$topic = Topic::factory()->published()->create();
$draftTopic = Topic::factory()->draft()->create();

// Sequence for ordered data
Topic::factory()
    ->count(5)
    ->sequence(fn ($sequence) => ['order' => $sequence->index + 1])
    ->create();
```

---

## Database in Tests

### SQLite Configuration

```php
// phpunit.xml
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

### Migration Fresh for Each Test

```php
// TestCase.php uses RefreshDatabase trait
// Automatically runs migrations before each test
```

---

## Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific file
./vendor/bin/pest tests/Feature/Curriculum/TopicTest.php

# Run specific test
./vendor/bin/pest --filter="admin can create topic"

# Run in parallel (faster)
./vendor/bin/pest --parallel

# Watch mode
./vendor/bin/pest --watch
```

---

## CI Configuration

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: kimyaogren_test
        ports: ['3306:3306']
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql
          coverage: xdebug
      
      - name: Cache Composer
        uses: actions/cache@v3
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run Tests
        run: ./vendor/bin/pest --parallel
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_DATABASE: kimyaogren_test
          DB_USERNAME: root
          DB_PASSWORD: password
```

---

## Test Coverage Requirements

| Code Type | Minimum Coverage |
|-----------|------------------|
| New Features | 80% |
| Bug Fixes | 100% (regression test) |
| Services/Actions | 90% |
| Enums/Value Objects | 100% |
| Models (scopes, accessors) | 80% |
| Controllers | Feature tests only |

---

## Prohibited Patterns

| ❌ Don't | ✅ Do |
|----------|-------|
| Mock Eloquent models | Use real database |
| Mock Facades | Use real implementations |
| Use `RefreshDatabase` on every test | Use it on TestCase (runs once per test) |
| Hardcode IDs | Use factories |
| Test private methods | Test public behavior |
| Assert exact query count | Assert behavior/results |

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*
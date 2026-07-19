# Testing Checklist

## Purpose

Quality gate for test coverage and quality. Every feature must pass this checklist.

---

## Test Types & Requirements

### Feature Tests (Primary)

| Target | Minimum Coverage | Location |
|--------|------------------|----------|
| New API endpoint | 100% paths | `tests/Feature/Api/` |
| New Filament Resource | 100% CRUD | `tests/Feature/Filament/` |
| New Model/Relationship | 100% | `tests/Feature/{Domain}/` |
| New Action/Service | 90% | `tests/Feature/{Domain}/` |
| Auth flows | 100% | `tests/Feature/Auth/` |

### Unit Tests

| Target | Minimum Coverage | Location |
|--------|------------------|----------|
| Enums | 100% | `tests/Unit/Enums/` |
| Value Objects | 100% | `tests/Unit/ValueObjects/` |
| Actions/Services | 90% | `tests/Unit/Actions/`, `tests/Unit/Services/` |
| Custom Rules | 100% | `tests/Unit/Rules/` |
| Scopes/Accessors | 80% | `tests/Unit/Models/` |

---

## Test Structure

### Feature Test Template

```php
<?php

declare(strict_types=1);

use App\Models\{User, Topic, Unit};
use App\Enums\UserRole;
use function Pest\Laravel\{actingAs, post, get, assertDatabaseHas, assertDatabaseMissing};

test('admin can create topic', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    
    // Act
    $response = actingAs($admin)
        ->post(route('topics.store'), [
            'name' => 'Atomic Structure',
            'unit_id' => $unit->id,
            'description' => 'Basic atomic theory',
            'sort_order' => 1,
        ]);
    
    // Assert
    $response->assertRedirect(route('topics.index'));
    assertDatabaseHas('topics', [
        'name' => 'Atomic Structure',
        'unit_id' => $unit->id,
        'sort_order' => 1,
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
```

### Unit Test Template

```php
<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Enums\QuestionDifficulty;

test('question type has correct labels', function () {
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

test('question type colors for badges', function () {
    expect(QuestionType::MultipleChoice->color())->toBe('primary');
    expect(QuestionType::Essay->color())->toBe('warning');
});
```

---

## Required Test Scenarios

### CRUD Operations (Every Resource)

- [ ] Index: authorized user sees list
- [ ] Index: unauthorized user gets 403
- [ ] Index: pagination works
- [ ] Index: search works
- [ ] Index: filters work
- [ ] Create: valid data creates record
- [ ] Create: invalid data shows errors
- [ ] Create: unauthorized gets 403
- [ ] Edit: valid data updates record
- [ ] Edit: invalid data shows errors
- [ ] Edit: unauthorized gets 403
- [ ] Delete: record soft deleted
- [ ] Delete: unauthorized gets 403

### Authorization (Every Policy)

- [ ] `viewAny`: admin=true, student=false
- [ ] `view`: owner=true, other=false
- [ ] `create`: role has permission
- [ ] `update`: owner=true, other=false
- [ ] `delete`: admin=true, other=false
- [ ] Custom actions tested

### Validation (Every Form Request)

- [ ] Required fields enforced
- [ ] Max length enforced
- [ ] Unique constraints (with ignore)
- [ ] Enum values only
- [ ] Numeric ranges
- [ ] Date formats
- [ ] File types/sizes
- [ ] Conditional rules (`required_if`)

### Relationships

- [ ] BelongsTo loads parent
- [ ] HasMany loads children
- [ ] HasManyThrough works
- [ ] BelongsToMany syncs
- [ ] MorphTo/MorphMany works
- [ ] Cascade delete works
- [ ] Cascade set null works

### Scopes

- [ ] `active()` filters correctly
- [ ] `published()` filters correctly
- [ ] `ordered()` sorts correctly
- [ ] Scopes chainable
- [ ] Scopes work with relationships

### Custom Methods

- [ ] Happy path works
- [ ] Edge cases (empty, null, boundaries)
- [ ] Exceptions thrown correctly
- [ ] Return types correct

---

## Test Quality Standards

### Naming
- [ ] Test names describe behavior: `test('admin can create topic')`
- [ ] Use `test()` not `it()` for Pest
- [ ] Group with `describe()` for related tests

### Assertions
- [ ] Use `expect()` (Pest) or `assert*()` (PHPUnit)
- [ ] Specific assertions: `assertDatabaseHas()` not `assertTrue()`
- [ ] Test one thing per test
- [ ] Assert both success and error cases

### Data
- [ ] Use factories: `User::factory()->admin()->create()`
- [ ] Use factory states: `Topic::factory()->published()->create()`
- [ ] No hardcoded IDs
- [ ] Minimal data for test

### Isolation
- [ ] `RefreshDatabase` trait on TestCase
- [ ] No test depends on another
- [ ] No shared state
- [ ] Parallel-safe

---

## Coverage Targets

```bash
# Run with coverage
php artisan test --coverage --min=80

# Per-file minimum (enforced in CI)
# New files: 90%
# Modified files: 80%
```

### Coverage Exclusions (configure in `phpunit.xml`)

```php
coverage()->exclude([
    'app/Console/Kernel.php',
    'app/Exceptions/Handler.php',
    'app/Http/Kernel.php',
    'app/Providers/*',
    'database/*',
    'tests/*',
]);
```

---

## CI Pipeline

```yaml
# .github/workflows/tests.yml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, xdebug
          coverage: xdebug
      - run: composer install --prefer-dist --no-progress
      - run: php artisan test --coverage --min=80
```

---

## Common Gaps (Review Checklist)

| Gap | Detection |
|-----|-----------|
| No test for new enum | Coverage < 100% on enum file |
| Missing authorization test | Policy not covered |
| No validation test | FormRequest not covered |
| Happy path only | No error case tests |
| No relationship test | Cascade/delete not tested |
| Hardcoded data | Factory not used |
| Flaky test | Fails intermittently in parallel |

---

*No PR merges without passing this checklist and CI coverage gate.*
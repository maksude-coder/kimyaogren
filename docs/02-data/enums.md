# Enum Standards

## Purpose

Defines how enums are handled at both database and PHP levels. Ensures type safety and consistency.

---

## Architecture

```
Database: TINYINT/SMALLINT UNSIGNED  ──►  PHP: Backed Enum (int/string)  ──►  Model: $casts
```

---

## Database Level

### Storage Types

| Enum Size | DB Column | Range | Example |
|-----------|-----------|-------|---------|
| Small (≤ 255) | `TINYINT UNSIGNED` | 0-255 | Status, Type |
| Medium (≤ 65,535) | `SMALLINT UNSIGNED` | 0-65,535 | Priority, Category |
| String-based | `VARCHAR(20)` | - | Slug-like values |

### Column Naming

| Pattern | Example |
|---------|---------|
| `{entity}_{type}` | `topic_status`, `question_type` |
| Short form: `{type}` | `status`, `difficulty` (if unambiguous) |

### Default Values

```php
// Always explicit default
$table->tinyInteger('status')->default(1);  // Draft
$table->tinyInteger('difficulty')->default(2);  // Medium
```

---

## PHP Level

### Enum Definition Template

```php
<?php

declare(strict_types=1);

namespace App\Enums\{Domain};

enum {EnumName}: int  // or string
{
    case {CaseName} = {Value};
    
    public function label(): string
    {
        return match ($this) {
            self::{CaseName} => '{Human Label}',
        };
    }
    
    public static function options(): array
    {
        return array_column(self::cases(), 'label', 'value');
    }
    
    public function color(): string
    {
        return match ($this) {
            self::{CaseName} => '{tailwind_color}',
        };
    }
    
    public function description(): string
    {
        return match ($this) {
            self::{CaseName} => '{Description}',
        };
    }
}
```

### Required Methods

| Method | Purpose | Example |
|--------|---------|---------|
| `label()` | Human-readable | `Published` |
| `options()` | Form select array | `['published' => 'Published']` |
| `color()` | UI badge color | `success`, `warning`, `danger` |
| `description()` | Tooltip/help text | `Live for students` |

---

## Model Integration

### Cast Declaration

```php
class Topic extends Model
{
    protected $casts = [
        'status' => TopicStatus::class,
        'difficulty' => QuestionDifficulty::class,
    ];
}
```

### Usage in Code

```php
// Comparison
if ($topic->status === TopicStatus::Published) { }

// Form options
Select::make('status')
    ->options(TopicStatus::options())
    ->required()

// Table badge
BadgeColumn::make('status')
    ->colors(TopicStatus::colors())
    ->formatStateUsing(fn ($state) => TopicStatus::tryFrom($state)?->label())
```

### Enum Colors (Standard)

| Semantic | Tailwind Color | Usage |
|----------|----------------|-------|
| Success | `success` | Published, Active, Passed |
| Warning | `warning` | Draft, Pending, In Progress |
| Danger | `danger` | Archived, Failed, Deleted |
| Info | `info` | Pending Review, Scheduled |
| Gray | `gray` | Neutral, Unknown |

---

## Standard Enums (Registry)

### Academic Domain

#### TopicStatus
```php
enum TopicStatus: int
{
    case Draft = 1;
    case Published = 2;
    case Archived = 3;
}
```

#### LearningObjectiveBloomLevel
```php
enum LearningObjectiveBloomLevel: int
{
    case Remember = 1;
    case Understand = 2;
    case Apply = 3;
    case Analyze = 4;
    case Evaluate = 5;
    case Create = 6;
}
```

### Assessment Domain

#### QuestionType
```php
enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case FillBlank = 'fill_blank';
    case Matching = 'matching';
    case Ordering = 'ordering';
    case Essay = 'essay';
}
```

#### QuestionDifficulty
```php
enum QuestionDifficulty: int
{
    case VeryEasy = 1;
    case Easy = 2;
    case Medium = 3;
    case Hard = 4;
    case VeryHard = 5;
}
```

#### YksSession
```php
enum YksSession: int
{
    case Tyt = 1;
    case Ayt = 2;
}
```

#### QuestionStatus
```php
enum QuestionStatus: int
{
    case Draft = 1;
    case Review = 2;
    case Approved = 3;
    case Deprecated = 4;
}
```

### Practice Domain

#### PracticeSessionType
```php
enum PracticeSessionType: string
{
    case Topic = 'topic';
    case MixedReview = 'mixed_review';
    case WeakAreas = 'weak_areas';
    case TimedQuiz = 'timed_quiz';
    case ExamSimulation = 'exam_simulation';
}
```

#### MasteryLevel
```php
enum MasteryLevel: int
{
    case InsufficientData = 0; // fewer than 5 answers
    case Weak = 1;             // below 50%
    case Developing = 2;       // 50-64%
    case Proficient = 3;       // 65-79%
    case Mastered = 4;         // 80-100%
}
```

### User Domain

#### UserRole
```php
enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';
    case ContentEditor = 'content_editor';
}
```

#### UserStatus
```php
enum UserStatus: int
{
    case Pending = 1;
    case Active = 2;
    case Suspended = 3;
    case Deleted = 4;
}
```

---

## Migration Pattern

```php
// In migration
$table->tinyInteger('status')->default(1);  // TopicStatus::Draft
$table->tinyInteger('difficulty')->default(3);  // QuestionDifficulty::Medium
$table->string('type', 20)->default('multiple_choice');  // QuestionType::MultipleChoice
```

---

## Form Request Validation

```php
// In FormRequest
'status' => ['required', 'integer', new Enum(TopicStatus::class)],
'type' => ['required', 'string', new Enum(QuestionType::class)],
```

### Custom Enum Rule

```php
class Enum implements Rule
{
    public function __construct(
        public readonly \BackedEnum $enumClass
    ) {}
    
    public function passes($attribute, $value): bool
    {
        return $this->enumClass::tryFrom($value) !== null;
    }
    
    public function message(): string
    {
        $values = implode(', ', $this->enumClass::values());
        return "The :attribute must be one of: {$values}.";
    }
}
```

---

## Filament Integration

```php
// Select field
Select::make('status')
    ->options(TopicStatus::options())
    ->default(TopicStatus::Draft->value)
    ->required()

// Badge column
BadgeColumn::make('status')
    ->colors(TopicStatus::colors())
    ->formatStateUsing(fn ($state) => TopicStatus::tryFrom($state)?->label())

// Filter
SelectFilter::make('status')
    ->options(TopicStatus::options())
```

---

## Adding New Enums

1. Create enum class in `app/Enums/{Domain}/`
2. Add migration with `tinyInteger` column
3. Add cast to Model
4. Add to this registry
5. Create unit tests for all methods
6. Update FormRequest validation

---

*All enums must have: label(), options(), color(), description()*
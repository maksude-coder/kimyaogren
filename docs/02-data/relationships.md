# Entity Relationships

## Purpose

Defines all entity relationships, cardinality, cascade rules, and query patterns for the KimyaÖğren data model.

---

## Relationship Rules

| Rule | Standard |
|------|----------|
| **All FKs explicit** | Every relationship has FK constraint |
| **Cascade delete** | Parent delete → children deleted (owned entities) |
| **Null on delete** | Reference delete → FK set null (shared entities) |
| **Index every FK** | Automatic in MySQL 8+, but declare explicitly |
| **No circular deps** | Relationship graph is a DAG |
| **A relationship has exactly one cascade rule** | The parent's `HasMany`/`HasOne` row and the child's `BelongsTo` row describe the *same physical foreign key* — they must always state the same cascade behavior. If they disagree, the documentation is wrong (see `docs/05-backlog/debt-log.md` `DEBT-004` for a real instance of this class of error, and `DEBT-005` for one still open). |
| **Soft deletes never cascade automatically** | Every `CASCADE DELETE` entry in this document describes the **database-level `ON DELETE CASCADE`** constraint, which only fires on a real SQL `DELETE` — i.e. Eloquent's `forceDelete()`. A model using `SoftDeletes` performs an `UPDATE ... SET deleted_at = ...` on ordinary `delete()`, which does **not** touch child rows at all. Routine (soft) deletion in Filament leaves children completely untouched; only an explicit, separately-authorized `ForceDeleteAction` triggers the cascade. Empirically verified for `Program → Grade` in Sprint 1.2 (see `docs/04-sprints/sprint-02-review.md`). |

---

## Academic Domain

> **Ownership tree (Architecture Decision, Sprint 1.3 planning):** `Program` owns `Grade`, `Grade` owns `Unit`, `Unit` owns `Topic`, `Topic` owns `Learning Objective`. Owned entities must not outlive their owner — every FK in this ownership chain uses `CASCADE DELETE` on force-delete, consistently, in both directions of each relationship. See `docs/05-backlog/debt-log.md` `DEBT-004` for the decision record. This does **not** extend to entities *referencing into* this tree from other domains (e.g. `Question`, `TheoryArticle`, `PracticeSession` below reference `Topic` but are not owned by it — their `RESTRICT`/`SET NULL` rules reflect a deliberate, separate design choice to preserve assessment/content history, and are unaffected by this decision).

### Program
```php
// Program.php
public function versions(): HasMany
{
    return $this->hasMany(ProgramVersion::class);
}

public function grades(): HasMany
{
    return $this->hasMany(Grade::class);
}

public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_programs')
        ->withPivot('enrolled_at', 'status', 'progress_percentage')
        ->withTimestamps();
}

public function media(): MorphMany
{
    return $this->morphMany(Media::class, 'model');
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| versions | HasMany | ProgramVersion | `program_id` | CASCADE DELETE |
| grades | HasMany | Grade | `program_id` | CASCADE DELETE |
| users | BelongsToMany | User | `user_programs` pivot | DETACH |
| creator | BelongsTo | User | `created_by_id` | SET NULL |
| updater | BelongsTo | User | `updated_by_id` | SET NULL |
| media | MorphMany | Media | `model_id`, `model_type` | CASCADE DELETE — forward-declared, `Media` not yet implemented (see `docs/01-architecture/domain-program.md` Architectural Note) |

### Grade
```php
// Grade.php
public function program(): BelongsTo
{
    return $this->belongsTo(Program::class);
}

public function units(): HasMany
{
    return $this->hasMany(Unit::class)->orderBy('order');
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| program | BelongsTo | Program | `program_id` | CASCADE DELETE |
| units | HasMany | Unit | `grade_id` | CASCADE DELETE |

### Unit
```php
// Unit.php
public function grade(): BelongsTo
{
    return $this->belongsTo(Grade::class);
}

public function topics(): HasMany
{
    return $this->hasMany(Topic::class)->ordered();
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| grade | BelongsTo | Grade | `grade_id` | CASCADE DELETE |
| topics | HasMany | Topic | `unit_id` | CASCADE DELETE |

### Topic
```php
// Topic.php
public function unit(): BelongsTo
{
    return $this->belongsTo(Unit::class);
}

public function learningObjectives(): HasMany
{
    return $this->hasMany(LearningObjective::class)->ordered();
}

public function questions(): HasMany
{
    return $this->hasMany(Question::class);
}

public function theoryArticles(): HasMany
{
    return $this->hasMany(TheoryArticle::class)->published();
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| unit | BelongsTo | Unit | `unit_id` | CASCADE DELETE |
| learningObjectives | HasMany | LearningObjective | `topic_id` | CASCADE DELETE |
| questions | HasMany | Question | `topic_id` | CASCADE DELETE (see note above — Question is not part of the ownership tree; this specific rule predates this decision and is out of scope for it) |
| theoryArticles | HasMany | TheoryArticle | `topic_id` | CASCADE DELETE (same note as `questions` above) |

### LearningObjective
```php
// LearningObjective.php
public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}

public function questions(): HasMany
{
    return $this->hasMany(Question::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| topic | BelongsTo | Topic | `topic_id` | CASCADE DELETE |
| questions | HasMany | Question | `learning_objective_id` | SET NULL |

---

## Content Domain

### TheoryArticle
```php
// TheoryArticle.php
public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}

public function versions(): HasMany
{
    return $this->hasMany(TheoryVersion::class)->latest();
}

public function currentVersion(): HasOne
{
    return $this->hasOne(TheoryVersion::class)->latestOfMany();
}

public function tags(): MorphToMany
{
    return $this->morphToMany(Tag::class, 'taggable');
}

public function media(): MorphMany
{
    return $this->morphMany(Media::class, 'model');
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| topic | BelongsTo | Topic | `topic_id` | RESTRICT |
| versions | HasMany | TheoryVersion | `theory_article_id` | CASCADE DELETE |
| currentVersion | HasOne | TheoryVersion | `theory_article_id` | - |
| tags | MorphToMany | Tag | `taggable` pivot | DETACH |
| media | MorphMany | Media | `model_id`, `model_type` | CASCADE DELETE |

### TheoryVersion (Immutable)
```php
// TheoryVersion.php
public function article(): BelongsTo
{
    return $this->belongsTo(TheoryArticle::class);
}

public function author(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by_id');
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| article | BelongsTo | TheoryArticle | `theory_article_id` | CASCADE DELETE |
| author | BelongsTo | User | `created_by_id` | SET NULL |

---

## Assessment Domain

### Question
```php
// Question.php
public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}

public function learningObjective(): BelongsTo
{
    return $this->belongsTo(LearningObjective::class);
}

public function options(): HasMany
{
    return $this->hasMany(QuestionOption::class)->ordered();
}

public function correctOption(): HasOne
{
    return $this->hasOne(QuestionOption::class)->where('is_correct', true);
}

public function exams(): BelongsToMany
{
    return $this->belongsToMany(Exam::class, 'exam_section_questions')
        ->withPivot(['section_id', 'order'])
        ->withTimestamps();
}

public function attempts(): HasMany
{
    return $this->hasMany(QuestionAttempt::class);
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| topic | BelongsTo | Topic | `topic_id` | RESTRICT |
| learningObjective | BelongsTo | LearningObjective | `learning_objective_id` | SET NULL |
| options | HasMany | QuestionOption | `question_id` | CASCADE DELETE |
| correctOption | HasOne | QuestionOption | `question_id` | - |
| exams | BelongsToMany | Exam | `exam_section_questions` | DETACH |
| attempts | HasMany | QuestionAttempt | `question_id` | CASCADE DELETE |

### QuestionOption
```php
// QuestionOption.php
public function question(): BelongsTo
{
    return $this->belongsTo(Question::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| question | BelongsTo | Question | `question_id` | CASCADE DELETE |

### Exam
```php
// Exam.php
public function sections(): HasMany
{
    return $this->hasMany(ExamSection::class)->ordered();
}

public function attempts(): HasMany
{
    return $this->hasMany(ExamAttempt::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| sections | HasMany | ExamSection | `exam_id` | CASCADE DELETE |
| attempts | HasMany | ExamAttempt | `exam_id` | CASCADE DELETE |

### ExamSection
```php
// ExamSection.php
public function exam(): BelongsTo
{
    return $this->belongsTo(Exam::class);
}

public function questions(): BelongsToMany
{
    return $this->belongsToMany(Question::class, 'exam_section_questions')
        ->withPivot('order')
        ->ordered();
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| exam | BelongsTo | Exam | `exam_id` | CASCADE DELETE |
| questions | BelongsToMany | Question | `exam_section_questions` | DETACH |

---

## Practice Domain

### PracticeSession
```php
// PracticeSession.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}

public function attempts(): HasMany
{
    return $this->hasMany(QuestionAttempt::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| user | BelongsTo | User | `user_id` | CASCADE DELETE |
| topic | BelongsTo | Topic | `topic_id` | SET NULL |
| attempts | HasMany | QuestionAttempt | `practice_session_id` | CASCADE DELETE |

### QuestionAttempt
```php
// QuestionAttempt.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function question(): BelongsTo
{
    return $this->belongsTo(Question::class);
}

public function practiceSession(): BelongsTo
{
    return $this->belongsTo(PracticeSession::class);
}

public function selectedOption(): BelongsTo
{
    return $this->belongsTo(QuestionOption::class, 'selected_option_id');
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| user | BelongsTo | User | `user_id` | CASCADE DELETE |
| question | BelongsTo | Question | `question_id` | RESTRICT |
| practiceSession | BelongsTo | PracticeSession | `practice_session_id` | SET NULL |
| selectedOption | BelongsTo | QuestionOption | `selected_option_id` | SET NULL |

### SpacedRepetitionSchedule
```php
// SpacedRepetitionSchedule.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function question(): BelongsTo
{
    return $this->belongsTo(Question::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| user | BelongsTo | User | `user_id` | CASCADE DELETE |
| question | BelongsTo | Question | `question_id` | CASCADE DELETE |

### TopicMastery
```php
// TopicMastery.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}
```

| Relationship | Type | Related | FK | Cascade |
|--------------|------|---------|-----|---------|
| user | BelongsTo | User | `user_id` | CASCADE DELETE |
| topic | BelongsTo | Topic | `topic_id` | CASCADE DELETE |

**Unique Constraint:** `(user_id, topic_id)`

---

## Progress Domain

### StudentProgress (Snapshot)
```php
// StudentProgress.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### WeakAreaReport
```php
// WeakAreaReport.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}
```

### MasterySnapshot (Daily)
```php
// MasterySnapshot.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function topic(): BelongsTo
{
    return $this->belongsTo(Topic::class);
}
```

**Unique Constraint:** `(user_id, topic_id, date)`

---

## User Domain

### User
```php
// User.php
public function classes(): BelongsToMany
{
    return $this->belongsToMany(Class::class, 'class_students')
        ->withPivot(['enrolled_at', 'status'])
        ->withTimestamps();
}

public function taughtClasses(): HasMany
{
    return $this->hasMany(Class::class, 'teacher_id');
}

public function practiceSessions(): HasMany
{
    return $this->hasMany(PracticeSession::class);
}

public function questionAttempts(): HasMany
{
    return $this->hasMany(QuestionAttempt::class);
}

public function topicMasteries(): HasMany
{
    return $this->hasMany(TopicMastery::class);
}

public function spacedRepetitionSchedules(): HasMany
{
    return $this->hasMany(SpacedRepetitionSchedule::class);
}

public function examAttempts(): HasMany
{
    return $this->hasMany(ExamAttempt::class);
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| classes | BelongsToMany | Class | `class_students` | DETACH |
| taughtClasses | HasMany | Class | `teacher_id` | SET NULL |
| practiceSessions | HasMany | PracticeSession | `user_id` | CASCADE DELETE |
| questionAttempts | HasMany | QuestionAttempt | `user_id` | CASCADE DELETE |
| topicMasteries | HasMany | TopicMastery | `user_id` | CASCADE DELETE |
| spacedRepetitionSchedules | HasMany | SpacedRepetitionSchedule | `user_id` | CASCADE DELETE |
| examAttempts | HasMany | ExamAttempt | `user_id` | CASCADE DELETE |

### Class
```php
// Class.php
public function teacher(): BelongsTo
{
    return $this->belongsTo(User::class, 'teacher_id');
}

public function students(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'class_students')
        ->withPivot(['enrolled_at', 'status'])
        ->withTimestamps();
}
```

| Relationship | Type | Related | FK/Pivot | Cascade |
|--------------|------|---------|----------|---------|
| teacher | BelongsTo | User | `teacher_id` | SET NULL |
| students | BelongsToMany | User | `class_students` | DETACH |

---

## Media Domain (Spatie MediaLibrary)

### Media Collection Mappings

| Model | Collection | Conversions |
|-------|------------|-------------|
| TheoryArticle | `theory_images` | thumb (300w), webp |
| Question | `question_media` | thumb, webp |
| QuestionOption | `option_media` | thumb, webp |
| User | `avatars` | thumb (100x100), circle |

### Media Model (Spatie)
- Uses `spatie/laravel-medialibrary` tables
- Do not modify directly
- Configure in model: `registerMediaCollections()`

---

## Query Patterns

### Eager Loading Standards

```php
// Academic: Program with full tree
Program::with(['grades.units.topics.learningObjectives'])->get();

// Assessment: Question with options
Question::with('options')->find($id);

// Practice: Session with attempts and questions
PracticeSession::with(['attempts.question.options', 'topic'])->find($id);

// Progress: User with masteries and weak areas
User::with(['topicMasteries.topic', 'weakAreas.topic'])->find($id);

// User: Teacher with classes and students
User::with(['taughtClasses.students'])->whereHas('roles', fn($q) => $q->where('name', 'teacher'))->get();
```

### Common Scopes

```php
// Topic
Topic::published()->active()->ordered()->get();

// Question
Question::whereHas('topic', fn($q) => $q->published())->get();

// PracticeSession
PracticeSession::where('user_id', auth()->id())
    ->where('completed_at', '!=', null)
    ->latest()
    ->paginate();
```

---

## Relationship Diagram (Mermaid)

```mermaid
erDiagram
    PROGRAM ||--o{ GRADE : has
    GRADE ||--o{ UNIT : has
    UNIT ||--o{ TOPIC : has
    TOPIC ||--o{ LEARNING_OBJECTIVE : has
    TOPIC ||--o{ QUESTION : has
    TOPIC ||--o{ THEORY_ARTICLE : has
    LEARNING_OBJECTIVE ||--o{ QUESTION : tagged
    QUESTION ||--o{ QUESTION_OPTION : has
    EXAM ||--o{ EXAM_SECTION : has
    EXAM_SECTION }|--o{ QUESTION : contains
    USER ||--o{ PRACTICE_SESSION : creates
    PRACTICE_SESSION ||--o{ QUESTION_ATTEMPT : has
    USER }|--o{ QUESTION : attempts
    USER ||--o{ TOPIC_MASTERY : tracks
    USER ||--o{ SPACED_REPETITION_SCHEDULE : schedules
    USER }|--o{ CLASS : enrolled
    CLASS ||--o{ USER : taught_by
```

---

*All relationships must have: explicit FK, cascade rule, index, and test coverage.*
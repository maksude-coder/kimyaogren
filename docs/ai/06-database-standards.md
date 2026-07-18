# Database Standards

These standards apply to **all** database changes in the KimyaÖğren project.

---

## Core Principles

| Principle | Description |
|-----------|-------------|
| **Data Dictionary First** | No migration without approved Data Dictionary |
| **Constraints Over Validation** | Database enforces integrity |
| **Explicit Over Implicit** | Every column, index, FK is intentional |
| **Naming Consistency** | Follow Laravel conventions exactly |

---

## Migration Order

**Always** follow this column order in migrations:

```php
Schema::create('topics', function (Blueprint $table) {
    // 1. PRIMARY KEY
    $table->id();
    
    // 2. FOREIGN KEYS (in order of dependency)
    $table->foreignId('unit_id')
        ->constrained()
        ->cascadeOnDelete();
    
    // 3. BUSINESS FIELDS (required first, then optional)
    $table->string('name', 100);
    $table->string('slug', 120)->unique();
    $table->text('description')->nullable();
    
    // 4. STATUS FIELDS (enums, booleans)
    $table->tinyInteger('status')->default(1); // Use PHP Enum cast
    $table->boolean('is_active')->default(true);
    
    // 5. SEO FIELDS (if applicable)
    $table->string('meta_title', 60)->nullable();
    $table->text('meta_description')->nullable();
    
    // 6. AUDIT FIELDS
    $table->foreignId('created_by_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
    $table->foreignId('updated_by_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
    
    // 7. TIMESTAMPS
    $table->timestamps();
    
    // 8. SOFT DELETES
    $table->softDeletes();
    
    // 9. INDEXES (after all columns)
    $table->index(['unit_id', 'order']);
    $table->unique(['unit_id', 'slug']);
});
```

---

## Column Standards

### Primary Key

```php
// ✓ ALWAYS
$table->id(); // bigIncrements, unsigned

// ✗ NEVER
$table->increments('id'); // int, not big enough
$table->uuid('id')->primary(); // Only if explicitly approved
```

### Foreign Keys

```php
// ✓ STANDARD - Owned relationship (cascade delete)
$table->foreignId('unit_id')
    ->constrained()
    ->cascadeOnDelete();

// ✓ STANDARD - Reference relationship (restrict delete)
$table->foreignId('created_by_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();

// ✓ EXPLICIT - Custom FK name
$table->foreignId('unit_id')
    ->constrained('units', 'id')
    ->cascadeOnDelete()
    ->name('topics_unit_id_foreign');

// ✗ NEVER - No foreign key
$table->unsignedBigInteger('unit_id');
```

### Business Fields

```php
// Strings - ALWAYS specify length
$table->string('name', 100);        // Short text
$table->string('slug', 120);        // URL-safe
$table->string('email', 255);       // Email
$table->string('phone', 20);        // Phone

// Text - only when length unknown/unbounded
$table->text('description');        // Long text
$table->longText('content');        // Very long text

// Numbers - use appropriate size
$table->unsignedTinyInteger('order');        // 0-255
$table->unsignedSmallInteger('sort_order');  // 0-65535
$table->unsignedInteger('view_count');       // 0-4B
$table->decimal('price', 10, 2);             // Money
$table->float('rating', 3, 2);               // Ratings

// Enums - tinyInteger + PHP Enum cast
$table->tinyInteger('status')->default(1);   // Model: $casts['status'] = StatusEnum::class

// Booleans
$table->boolean('is_active')->default(true);
$table->boolean('is_featured')->default(false);

// JSON - only when structure varies
$table->json('settings')->nullable();
$table->jsonb('metadata')->nullable(); // PostgreSQL only
```

### Special Columns

```php
// UUID (when approved)
$table->uuid('uuid')->unique();

// IP Address
$table->ipAddress('last_login_ip')->nullable();

// MAC Address
$table->macAddress('device_mac')->nullable();

// Geometry (PostGIS)
$table->point('location')->nullable();
```

---

## Index Standards

### Required Indexes

```php
// 1. Every FK column MUST have index
$table->index('unit_id'); // Auto-created by FK in MySQL 8+, but explicit is clearer

// 2. Composite indexes for common queries
$table->index(['unit_id', 'order']);          // Filter + Sort
$table->index(['unit_id', 'is_active']);      // Filter + Filter
$table->index(['status', 'created_at']);      // Status + Date range

// 3. Unique constraints for business keys
$table->unique(['unit_id', 'slug']);          // Unique within parent
$table->unique('email');                       // Global unique
$table->unique(['user_id', 'topic_id']);      // Composite unique
```

### Index Naming

```php
// Pattern: {table}_{columns}_index
$table->index(['unit_id', 'order'], 'topics_unit_id_order_index');
$table->unique(['unit_id', 'slug'], 'topics_unit_id_slug_unique');

// Foreign key: {table}_{column}_foreign
$table->foreignId('unit_id')
    ->constrained()
    ->cascadeOnDelete()
    ->name('topics_unit_id_foreign');
```

### Index Guidelines

| Scenario | Index Type |
|----------|------------|
| FK column | BTREE (auto) |
| WHERE column | BTREE |
| ORDER BY column | BTREE |
| Composite WHERE + ORDER | Composite BTREE |
| Business unique key | UNIQUE |
| Full-text search | FULLTEXT (MySQL) |
| JSON field search | GIN (PostgreSQL) |

---

## Enum Handling

### Database Level

```php
// Migration: Use tinyInteger
$table->tinyInteger('status')->default(1);
$table->tinyInteger('difficulty')->default(1);
```

### Model Level - PHP Backed Enum

```php
<?php

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
    
    public static function options(): array
    {
        return array_column(self::cases(), 'label', 'value');
    }
}
```

### Model Cast

```php
class Topic extends Model
{
    protected $casts = [
        'status' => TopicStatus::class,
        'difficulty' => QuestionDifficulty::class,
    ];
}
```

---

## Soft Deletes

### When to Use

| Use Soft Deletes | Don't Use Soft Deletes |
|------------------|------------------------|
| User-facing content | Audit logs |
| Recoverable entities | Session data |
| Referenced by other tables | Temporary tables |
| Legal/compliance retention | High-volume write tables |

### Implementation

```php
// Migration
$table->softDeletes();

// Model
class Topic extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}

// Query scopes
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

public function scopeTrashed(Builder $query): Builder
{
    return $query->onlyTrashed();
}
```

---

## Timestamps

### Always Include

```php
// Migration
$table->timestamps(); // created_at, updated_at

// Model - automatic, no extra code needed
```

### Custom Timestamp Columns

```php
// If you need published_at, approved_at, etc.
$table->timestamp('published_at')->nullable();
$table->timestamp('approved_at')->nullable();
$table->timestamp('rejected_at')->nullable();
```

---

## Audit Fields

### Standard Audit Columns

```php
$table->foreignId('created_by_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();

$table->foreignId('updated_by_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();
```

### Auto-Populate (Observer)

```php
class TopicObserver
{
    public function creating(Topic $topic): void
    {
        if (auth()->check()) {
            $topic->created_by_id = auth()->id();
        }
    }
    
    public function updating(Topic $topic): void
    {
        if (auth()->check()) {
            $topic->updated_by_id = auth()->id();
        }
    }
}
```

---

## Pivot Tables

### Standard Pivot

```php
Schema::create('topic_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    
    // Pivot fields
    $table->unsignedTinyInteger('mastery_level')->default(0);
    $table->timestamp('mastered_at')->nullable();
    $table->unsignedInteger('attempts')->default(0);
    
    $table->unique(['topic_id', 'user_id']);
    $table->index(['user_id', 'mastery_level']);
    
    $table->timestamps();
});
```

### Pivot Model

```php
class TopicUser extends Pivot
{
    protected $table = 'topic_user';
    
    protected $fillable = [
        'mastery_level',
        'mastered_at',
        'attempts',
    ];
    
    protected $casts = [
        'mastered_at' => 'datetime',
        'mastery_level' => MasteryLevel::class,
    ];
}
```

---

## Data Dictionary Template

Every database change requires this document:

```markdown
# Data Dictionary: [Table Name]

## Overview
- **Table**: `topics`
- **Purpose**: Store curriculum topics within units
- **Owner**: Curriculum Domain
- **Created**: Sprint X.Y

## Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AI | Primary key |
| unit_id | BIGINT UNSIGNED | FK → units.id, CASCADE | Parent unit |
| name | VARCHAR(100) | NOT NULL | Topic name |
| slug | VARCHAR(120) | NOT NULL, UNIQUE (unit_id) | URL identifier |
| description | TEXT | NULLABLE | Detailed description |
| order | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Display order |
| status | TINYINT | NOT NULL, DEFAULT 1 | Draft=1, Published=2, Archived=3 |
| is_active | BOOLEAN | NOT NULL, DEFAULT TRUE | Visibility flag |
| created_by_id | BIGINT UNSIGNED | FK → users.id, NULL ON DELETE | Creator |
| updated_by_id | BIGINT UNSIGNED | FK → users.id, NULL ON DELETE | Last editor |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

## Indexes

| Name | Columns | Type |
|------|---------|------|
| topics_unit_id_order_index | (unit_id, order) | BTREE |
| topics_unit_id_slug_unique | (unit_id, slug) | UNIQUE |
| topics_status_created_at_index | (status, created_at) | BTREE |

## Relationships

| Relationship | Type | Related Table | Cascade |
|--------------|------|---------------|---------|
| unit | BelongsTo | units | ON DELETE CASCADE |
| questions | HasMany | questions | ON DELETE CASCADE |
| creator | BelongsTo | users | ON DELETE SET NULL |
| updater | BelongsTo | users | ON DELETE SET NULL |

## Business Rules

1. Slug must be unique within a unit
2. Order determines display sequence within unit
3. Only Published topics visible to students
4. Deleting unit cascades to topics
```

---

## Migration Checklist

- [ ] Data Dictionary approved by Architect
- [ ] Migration follows column order
- [ ] All FKs have `constrained()` and cascade/null rules
- [ ] All string columns have explicit length
- [ ] All FK columns indexed
- [ ] Business unique constraints defined
- [ ] Composite indexes for query patterns
- [ ] Soft deletes if applicable
- [ ] Timestamps included
- [ ] Audit fields if required
- [ ] Enum columns use tinyInteger + PHP Enum
- [ ] Migration tested: `migrate:fresh --seed`
- [ ] Rollback tested: `migrate:rollback`

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*
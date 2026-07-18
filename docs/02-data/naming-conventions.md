# Database Naming Conventions

## Purpose

Consistent naming ensures readability, maintainability, and prevents collisions. These conventions are mandatory for all schema changes.

---

## Tables

| Rule | Convention | Example |
|------|------------|---------|
| **Format** | `snake_case`, plural | `learning_objectives` |
| **Prefix** | Domain prefix if ambiguous | `academic_grades`, `content_articles` |
| **Pivot** | Singular, alphabetical | `question_tag` (not `tag_question`) |
| **No abbreviations** | Full words | `exam_sections` not `exam_secs` |
| **No reserved words** | Avoid `order`, `group`, `key` | Use `sort_order`, `question_group` |

### Domain Prefixes (when needed)

| Domain | Prefix | Example |
|--------|--------|---------|
| Academic | `academic_` | `academic_grades` |
| Content | `content_` | `content_theory_articles` |
| Assessment | `assessment_` | `assessment_questions` |
| Practice | `practice_` | `practice_sessions` |
| Progress | `progress_` | `progress_mastery_snapshots` |
| User | *(none)* | `users`, `classes` |
| Media | *(none)* | `media` (spatie) |

---

## Columns

### Primary Key
| Pattern | Example |
|---------|---------|
| `id` | `id` (always BIGINT UNSIGNED) |

### Foreign Keys
| Pattern | Example |
|---------|---------|
| `{related_table_singular}_id` | `unit_id`, `topic_id`, `created_by_id` |
| Polymorphic: `{morph_name}_id`, `{morph_name}_type` | `taggable_id`, `taggable_type` |

### Business Fields
| Pattern | Example |
|---------|---------|
| Descriptive snake_case | `name`, `slug`, `description`, `sort_order` |
| Boolean: `is_`, `has_`, `can_`, `should_` | `is_active`, `has_explanation`, `can_retry` |
| Count: `{noun}_count` | `question_count`, `attempt_count` |
| Date: `{event}_at` | `published_at`, `completed_at`, `due_at` |
| Datetime: `{event}_at` | `created_at`, `started_at` |

### Status/Type Fields
| Pattern | Example |
|---------|---------|
| `{entity}_status` | `topic_status`, `question_status` |
| `{entity}_type` | `question_type`, `media_type` |
| Use PHP Enum cast | `tinyInteger` in DB |

### Audit Fields
| Field | Type | Standard |
|-------|------|----------|
| `created_by_id` | BIGINT UNSIGNED | FK → users.id, SET NULL |
| `updated_by_id` | BIGINT UNSIGNED | FK → users.id, SET NULL |
| `created_at` | TIMESTAMP | Auto |
| `updated_at` | TIMESTAMP | Auto |
| `deleted_at` | TIMESTAMP | Soft delete, nullable |

### SEO Fields (when applicable)
| Field | Type | Length |
|-------|------|--------|
| `meta_title` | VARCHAR | 60 |
| `meta_description` | TEXT | - |
| `meta_keywords` | VARCHAR | 255 |
| `canonical_url` | VARCHAR | 500 |

---

## Indexes

### Naming Pattern
| Type | Pattern | Example |
|------|---------|---------|
| Primary Key | `PRIMARY` (implicit) | - |
| Foreign Key | `{table}_{column}_foreign` | `topics_unit_id_foreign` |
| Unique | `{table}_{columns}_unique` | `topics_unit_id_slug_unique` |
| Index | `{table}_{columns}_index` | `topics_unit_id_order_index` |
| Fulltext | `{table}_{columns}_fulltext` | `articles_content_fulltext` |

### Multi-Column Order
1. Equality columns first (`WHERE unit_id = ?`)
2. Sort columns second (`ORDER BY order`)
3. Range columns last

```sql
-- Good: unit_id (equality) + order (sort)
INDEX idx_topics_unit_order (unit_id, order)

-- Bad: order (sort) + unit_id (equality)
INDEX idx_topics_order_unit (order, unit_id)
```

---

## Constraints

### Foreign Keys
```sql
-- Pattern: {table}_{column}_foreign
CONSTRAINT topics_unit_id_foreign 
    FOREIGN KEY (unit_id) 
    REFERENCES units(id) 
    ON DELETE CASCADE
```

### Unique Constraints
```sql
-- Pattern: {table}_{columns}_unique
CONSTRAINT topics_unit_id_slug_unique 
    UNIQUE (unit_id, slug)
```

### Check Constraints (MySQL 8.0.16+)
```sql
-- Pattern: {table}_{column}_check
CONSTRAINT questions_difficulty_check 
    CHECK (difficulty BETWEEN 1 AND 5)
```

---

## Enums (Database Level)

### Storage
| Enum Type | DB Column | PHP Cast |
|-----------|-----------|----------|
| Small (< 256 values) | `TINYINT UNSIGNED` | `BackedEnum::class` |
| Large | `SMALLINT UNSIGNED` | `BackedEnum::class` |
| String-based | `VARCHAR(20)` | `BackedEnum::class` |

### Naming
| Level | Convention | Example |
|-------|------------|---------|
| PHP Enum Class | PascalCase, singular | `QuestionType` |
| PHP Enum Cases | PascalCase | `MultipleChoice` |
| DB Values | Enum value (int/string) | `1`, `multiple_choice` |
| Column Name | `{entity}_{type}_status` | `question_type` |

---

## Relationships

### One-to-Many
| Side | Method | FK Column |
|------|--------|-----------|
| Parent | `hasMany()` | - |
| Child | `belongsTo()` | `{parent}_id` |

### Many-to-Many (Pivot)
| Table | Pattern | Example |
|-------|---------|---------|
| Pivot | `{model_a}_{model_b}` | `question_tag` |
| Pivot PK | `id` (or composite) | `id` |
| Pivot FKs | `{model}_id` | `question_id`, `tag_id` |

### Polymorphic
| Field | Pattern | Example |
|-------|---------|---------|
| ID | `{morph}_id` | `taggable_id` |
| Type | `{morph}_type` | `taggable_type` |
| Index | `{table}_{morph}_index` | `tags_taggable_index` |

---

## Reserved Words (Avoid)

MySQL reserved words that cause issues:
```
order, group, key, index, primary, unique, check, 
references, constraint, foreign, table, column, 
database, schema, view, trigger, procedure, function,
user, password, host, status, grant, privilege
```

**Always use alternatives:**
- `order` → `sort_order`, `display_order`
- `group` → `question_group`, `item_group`
- `key` → `api_key`, `secret_key`
- `status` → `topic_status`, `question_status`

---

## Migration Column Order (Mandatory)

```php
Schema::create('table_name', function (Blueprint $table) {
    // 1. Primary Key
    $table->id();
    
    // 2. Foreign Keys (dependency order)
    $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
    
    // 3. Business Fields (required first, then optional)
    $table->string('name', 100);
    $table->string('slug', 120);
    $table->text('description')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);
    
    // 4. Status/Type Fields (enums as tinyInteger)
    $table->tinyInteger('status')->default(1);
    $table->boolean('is_active')->default(true);
    
    // 5. SEO Fields (if applicable)
    $table->string('meta_title', 60)->nullable();
    $table->text('meta_description')->nullable();
    
    // 6. Audit Fields
    $table->foreignId('created_by_id')
        ->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by_id')
        ->nullable()->constrained('users')->nullOnDelete();
    
    // 7. Timestamps
    $table->timestamps();
    
    // 8. Soft Deletes
    $table->softDeletes();
    
    // 9. Indexes (after all columns)
    $table->index(['parent_id', 'sort_order']);
    $table->unique(['parent_id', 'slug']);
});
```

---

## Quick Reference Card

| Element | Format | Example |
|---------|--------|---------|
| Table | `snake_case` plural | `learning_objectives` |
| Column | `snake_case` | `learning_objective_id` |
| PK | `id` | `id` |
| FK | `{table}_id` | `unit_id` |
| Boolean | `is_` / `has_` / `can_` | `is_published` |
| Enum Column | `{entity}_status` | `topic_status` |
| Enum Class | PascalCase singular | `TopicStatus` |
| Index | `{table}_{cols}_index` | `topics_unit_id_order_index` |
| Unique | `{table}_{cols}_unique` | `topics_unit_id_slug_unique` |
| FK Constraint | `{table}_{col}_foreign` | `topics_unit_id_foreign` |
| Pivot Table | `{a}_{b}` (alphabetical) | `question_tag` |

---

*Enforced by: Migration template, code review, CI linting.*
# Data Layer README

## Purpose

Overview of the data layer documentation structure. The data layer is the foundation of KimyaÖğren - all code derives from approved data definitions.

---

## Documentation Structure

```
docs/02-data/
├── README.md                    # This file
├── data-dictionary.md           # Master data dictionary template & process
├── naming-conventions.md        # Database naming standards
├── enums.md                     # Enum definitions (DB + PHP)
├── relationships.md             # Entity relationship diagrams & rules
└── migrations/
    ├── process.md               # Migration workflow
    └── templates/               # Migration templates by type
```

---

## Data-First Workflow

```
1. REQUIREMENTS
   │
   ▼
2. DATA DICTIONARY (Architect + PM approval)
   │
   ▼
3. MIGRATION (generated from Data Dictionary)
   │
   ▼
4. MODEL + CASTS + RELATIONSHIPS
   │
   ▼
5. FACTORY + SEEDER
   │
   ▼
6. TESTS
```

### Key Rules

| Rule | Enforcement |
|------|-------------|
| No migration without Data Dictionary | CI check |
| Data Dictionary = single source of truth | Architect sign-off |
| Migration column order: PK → FK → Business → Status → SEO → Audit → Timestamps → SoftDeletes | Lint rule |
| All FKs have explicit constraints | Migration review |
| All enums use `tinyInteger` + PHP Enum cast | Code review |
| Soft deletes for all user-facing entities | Default in template |

---

## Core Data Domains

| Domain | Tables | Owner |
|--------|--------|-------|
| **Academic** | grades, units, topics, learning_objectives | Architect |
| **Content** | theory_articles, media, tags | Content Lead |
| **Assessment** | questions, question_options, exams, exam_sections, exam_attempts, question_attempts | Assessment Lead |
| **Practice** | practice_sessions, question_attempts, spaced_repetition_schedules, topic_masteries | Practice Lead |
| **Progress** | student_progress, weak_area_reports, mastery_snapshots | Analytics Lead |
| **User** | users, roles, permissions, classes, class_students | Auth Lead |
| **Media** | media (spatie) | Media Lead |
| **Settings** | settings, feature_flags | Platform Lead |

---

## Quick Reference

### Migration Template (Standard Entity)
```php
Schema::create('topics', function (Blueprint $table) {
    // 1. Primary Key
    $table->id();
    
    // 2. Foreign Keys
    $table->foreignId('unit_id')
        ->constrained()
        ->cascadeOnDelete();
    
    // 3. Business Fields
    $table->string('name', 100);
    $table->string('slug', 120);
    $table->text('description')->nullable();
    $table->unsignedSmallInteger('order')->default(0);
    
    // 4. Status Fields
    $table->tinyInteger('status')->default(1); // Enum cast
    $table->boolean('is_active')->default(true);
    
    // 5. SEO Fields (if applicable)
    $table->string('meta_title', 60)->nullable();
    $table->text('meta_description')->nullable();
    
    // 6. Audit Fields
    $table->foreignId('created_by_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
    $table->foreignId('updated_by_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
    
    // 7. Timestamps
    $table->timestamps();
    
    // 8. Soft Deletes
    $table->softDeletes();
    
    // 9. Indexes
    $table->index(['unit_id', 'order']);
    $table->unique(['unit_id', 'slug']);
});
```

### Model Template
```php
class Topic extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'unit_id', 'name', 'slug', 'description', 
        'order', 'status', 'is_active',
        'meta_title', 'meta_description',
        'created_by_id', 'updated_by_id'
    ];
    
    protected $casts = [
        'order' => 'integer',
        'status' => TopicStatus::class,
        'is_active' => 'boolean',
        'meta_title' => 'string',
        'meta_description' => 'string',
    ];
    
    // Relationships
    public function unit(): BelongsTo
    public function learningObjectives(): HasMany
    public function questions(): HasMany
    public function theoryArticles(): HasMany
    public function creator(): BelongsTo
    public function updater(): BelongsTo
    
    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopeOrdered(Builder $query): Builder
}
```

---

## Data Quality Standards

| Standard | Requirement |
|----------|-------------|
| **No nullable FKs** for required relationships | `constrained()->cascadeOnDelete()` |
| **Explicit string lengths** | Never `text` when `string(255)` works |
| **Unsigned integers** for counts/orders | `unsignedSmallInteger`, `unsignedInteger` |
| **Enum casts** for status/type fields | PHP backed enum + `tinyInteger` |
| **Index every FK column** | Auto by MySQL 8, but declare explicitly |
| **Composite indexes** for query patterns | `(unit_id, order)`, `(topic_id, is_correct)` |
| **Unique constraints** for business keys | `(unit_id, slug)`, `(user_id, question_id)` |

---

*All data changes start with a Data Dictionary. See [data-dictionary.md](data-dictionary.md).*
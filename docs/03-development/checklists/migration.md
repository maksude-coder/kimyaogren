# Migration Checklist

## Purpose

Quality gate for every database migration. Must pass before PR approval.

---

## Pre-Migration

- [ ] Data Dictionary approved by Architect
- [ ] Data Dictionary confirmed by PM
- [ ] Migration generated from Data Dictionary (not written from scratch)
- [ ] Column order follows standard:
  1. Primary Key (`id()`)
  2. Foreign Keys (`foreignId()->constrained()->cascadeOnDelete()`)
  3. Business Fields (required → optional)
  4. Status Fields (enums as `tinyInteger`, booleans)
  5. SEO Fields (if applicable)
  6. Audit Fields (`created_by_id`, `updated_by_id` with `nullOnDelete()`)
  7. Timestamps (`timestamps()`)
  8. Soft Deletes (`softDeletes()`)
  9. Indexes (after all columns)

---

## Migration Structure

```php
Schema::create('table_name', function (Blueprint $table) {
    // 1. Primary Key
    $table->id();

    // 2. Foreign Keys
    $table->foreignId('parent_id')
        ->constrained()
        ->cascadeOnDelete();

    // 3. Business Fields (required first)
    $table->string('name', 100);
    $table->string('slug', 120);
    // optional
    $table->text('description')->nullable();
    $table->unsignedSmallInteger('sort_order')->default(0);

    // 4. Status Fields
    $table->tinyInteger('status')->default(1); // Enum cast
    $table->boolean('is_active')->default(true);

    // 5. SEO Fields
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
    $table->index(['parent_id', 'sort_order']);
    $table->unique(['parent_id', 'slug']);
});
```

---

## Foreign Keys

- [ ] Every relationship has explicit FK constraint
- [ ] `constrained()` references correct table
- [ ] Cascade rules correct:
  - Owned entities: `cascadeOnDelete()`
  - Referenced entities: `nullOnDelete()` or `restrictOnDelete()`
  - Pivot tables: `cascadeOnDelete()` both sides
- [ ] FK name follows pattern: `{table}_{column}_foreign`

---

## Columns

- [ ] All strings have explicit length: `string('name', 100)`
- [ ] No `text` when `string(255)` suffices
- [ ] Unsigned integers for counts/orders: `unsignedSmallInteger`, `unsignedInteger`
- [ ] Enums use `tinyInteger` (or `smallInteger` if > 255 values)
- [ ] Booleans for true/false flags (not tinyInteger)
- [ ] Nullable only when justified (document why)
- [ ] Default values set where appropriate

---

## Indexes

- [ ] Index on every FK column (explicit, not relying on MySQL auto)
- [ ] Composite indexes for common query patterns:
  - Filter + Sort: `(parent_id, sort_order)`
  - Filter + Filter: `(status, is_active)`
  - Date range: `(status, created_at)`
- [ ] Unique constraints for business keys:
  - `(parent_id, slug)`
  - `(user_id, question_id)` for pivots
- [ ] Index names follow pattern: `{table}_{columns}_index` / `_unique`

---

## Special Cases

### Pivot Tables
- [ ] Has `id` primary key (not composite PK)
- [ ] Both FKs cascade delete
- [ ] Unique constraint on both FKs
- [ ] Pivot fields included (sort_order, metadata, etc.)

### Enum Columns
- [ ] DB: `tinyInteger` with default
- [ ] Model: `$casts['column'] = EnumClass::class`
- [ ] Enum class exists in `app/Enums/{Domain}/`
- [ ] Enum has `label()`, `options()`, `color()`, `description()`

### Soft Deletes
- [ ] Included for all user-facing entities
- [ ] Not included for: audit logs, session data, high-volume writes
- [ ] Unique constraints account for soft deletes (partial indexes if needed)

### JSON Columns
- [ ] Only when structure varies
- [ ] Documented in Data Dictionary
- [ ] Consider `jsonb` (PostgreSQL) or generated columns for querying

---

## Testing

- [ ] `php artisan migrate:fresh --seed` passes
- [ ] `php artisan migrate:rollback --step=1` works
- [ ] Model factory creates valid records
- [ ] Model relationships work
- [ ] Filament resource CRUD works

---

## Documentation

- [ ] Data Dictionary updated (same PR)
- [ ] CHANGELOG.md updated (if user-facing)
- [ ] Related models updated with casts/fillable

---

## Anti-Patterns (Auto-Reject)

| Pattern | Why |
|---------|-----|
| `$table->increments('id')` | Not big enough |
| `$table->unsignedBigInteger('parent_id')` | No FK constraint |
| `$table->string('name')` | No length = 255 default, no validation |
| `$table->integer('status')` | Signed, no enum cast |
| `$guarded = []` in Model | Mass assignment vulnerability |
| No `softDeletes()` on user entity | Accidental data loss |
| No audit fields | No accountability |
| `DB::raw()` in migration | Not portable, no type safety |

---

## CI Checks (Automated)

```yaml
# .github/workflows/ci.yml
- name: Check migration order
  run: php artisan migrate:fresh --seed --force

- name: Lint migrations
  run: php artisan migrate:lint --format=json
```

---

*Every migration PR must pass this checklist. No exceptions.*
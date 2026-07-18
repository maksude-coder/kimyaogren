# Model Checklist

## Purpose

Quality gate for every Eloquent model. Must pass before PR approval.

---

## Structure

```php
<?php

declare(strict_types=1);

namespace App\Models\{Domain};

use App\Models\{RelatedModel};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelName extends Model
{
    use HasFactory, SoftDeletes; // Traits first

    // Constants
    public const MAX_NAME_LENGTH = 100;

    // Properties (protected/private)
    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'sort_order', 'status', 'is_active',
        'meta_title', 'meta_description',
        'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => ModelStatus::class,
        'is_active' => 'boolean',
    ];

    // Boot
    protected static function booted(): void
    {
        static::creating(fn ($model) => $model->sort_order ??= 0);
        static::updating(fn ($model) => $model->updated_by_id = auth()->id());
    }

    // Relationships (with return types)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return "{$this->parent->code} - {$this->name}";
    }

    // Public methods
    public function getNextSibling(): ?self
    {
        return self::where('parent_id', $this->parent_id)
            ->where('sort_order', '>', $this->sort_order)
            ->ordered()
            ->first();
    }
}
```

---

## Required Elements

### Header
- [ ] `declare(strict_types=1);`
- [ ] Correct namespace: `App\Models\{Domain}`
- [ ] Imports grouped: Laravel → App → Third-party

### Traits
- [ ] `HasFactory` (always)
- [ ] `SoftDeletes` (if user-facing entity)
- [ ] Custom traits only if reused > 2 models

### Constants
- [ ] Public constants for validation limits
- [ ] UPPER_SNAKE_CASE naming

### Properties
- [ ] `$fillable` defined (never `$guarded = []`)
- [ ] `$fillable` matches Data Dictionary business fields
- [ ] `$casts` for:
  - Enums: `ModelStatus::class`
  - Integers: `'sort_order' => 'integer'`
  - Booleans: `'is_active' => 'boolean'`
  - Dates: `'published_at' => 'datetime'`
  - Arrays: `'settings' => 'array'`
  - JSON: `'metadata' => 'json'`
- [ ] `$dates` only if not in `$casts`
- [ ] `$appends` for computed attributes
- [ ] `$hidden` for sensitive fields (API resources handle this)

### Boot Methods
- [ ] `booted()` static method for model events
- [ ] Auto-set `sort_order` default
- [ ] Auto-set audit fields (`created_by_id`, `updated_by_id`)
- [ ] No heavy logic in boot

### Relationships
- [ ] All relationships defined with return types
- [ ] `BelongsTo`, `HasMany`, `HasOne`, `MorphTo`, `MorphMany`, `BelongsToMany`
- [ ] Inverse relationships on related models
- [ ] Custom FK column specified if not convention
- [ ] `withDefault()` for optional BelongsTo

### Scopes
- [ ] `scopeActive()` for `is_active = true`
- [ ] `scopePublished()` for `status = Published`
- [ ] `scopeOrdered()` for default sort
- [ ] `scopeWithRelations()` for common eager loads
- [ ] Scopes chainable and return `Builder`

### Accessors / Mutators
- [ ] Accessors for computed display fields
- [ ] Mutators only for data transformation on set
- [ ] No business logic in accessors/mutators
- [ ] Return types declared

### Public Methods
- [ ] Single responsibility
- [ ] Return types declared
- [ ] PHPDoc with `@param`, `@return`, `@throws`
- [ ] No direct DB queries (use relationships/scopes)
- [ ] Delegate complex logic to Actions/Services

---

## Conventions

| Element | Convention |
|---------|------------|
| Table | Snake_case plural: `learning_objectives` |
| PK | `id()` (BIGINT UNSIGNED) |
| FK | `parent_id` (singular) |
| Pivot FKs | `model_a_id`, `model_b_id` |
| Polymorphic | `taggable_id`, `taggable_type` |
| Status enum | `tinyInteger` + Enum cast |
| Boolean | `is_active`, `has_feature` |
| Order | `sort_order` (unsignedSmallInteger) |
| Timestamps | `timestamps()` (created_at, updated_at) |
| Soft delete | `softDeletes()` (deleted_at) |

---

## Anti-Patterns (Auto-Reject)

| Pattern | Correct Approach |
|---------|------------------|
| `$guarded = []` | `$fillable = [...]` |
| Logic in accessor | Action/Service class |
| `DB::table()` in model | Eloquent relationships |
| Static facade calls | Dependency injection |
| `config()` in model | Pass as parameter |
| Business logic in Filament Resource | Model method or Action |
| `$model->save()` in accessor | Observer or explicit call |
| Magic `__get`/`__set` | Explicit accessors/mutators |

---

## Testing

- [ ] Factory creates valid model
- [ ] Factory states for variants
- [ ] Relationship tests (belongsTo, hasMany)
- [ ] Scope tests (active, ordered, published)
- [ ] Accessor tests
- [ ] Custom method tests
- [ ] Observer tests (if applicable)

---

## Filament Integration

- [ ] Model referenced in Resource: `protected static ?string $model = ModelName::class`
- [ ] Navigation label/group/sort configured
- [ ] Searchable columns include key fields
- [ ] Filters for common queries
- [ ] Actions delegate to services

---

*Every model PR must pass this checklist.*
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProgramStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'slug',
        'description',
        'short_description',
        'primary_language',
        'supported_languages',
        'min_grade',
        'max_grade',
        'status',
        'is_active',
        'display_order',
        'version',
        'current_version_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
        'settings',
        'created_by_id',
        'updated_by_id',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'uuid' => 'string',
        'supported_languages' => 'array',
        'min_grade' => 'integer',
        'max_grade' => 'integer',
        'status' => ProgramStatus::class,
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'version' => 'integer',
        'settings' => 'array',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $program) {
            $program->uuid ??= Str::uuid();
            $program->slug ??= Str::slug($program->name);
            $program->display_order ??= 0;
        });

        static::updating(function (self $program) {
            if ($program->isDirty('name') && ! $program->isDirty('slug')) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    // Relationships

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ProgramVersion::class, 'current_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProgramVersion::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
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

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProgramStatus::Published)
            ->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', ProgramStatus::Archived);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function scopeForGrade($query, int $grade)
    {
        return $query->where('min_grade', '<=', $grade)
            ->where('max_grade', '>=', $grade);
    }

    // Accessors

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === ProgramStatus::Published;
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === ProgramStatus::Draft;
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === ProgramStatus::Archived;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TopicStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'slug',
        'description',
        'order',
        'difficulty',
        'status',
        'is_active',
        'meta_title',
        'meta_description',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'order' => 'integer',
        'difficulty' => 'integer',
        'status' => TopicStatus::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $topic) {
            $topic->slug ??= Str::slug($topic->name);
        });

        static::updating(function (self $topic) {
            if ($topic->isDirty('name') && ! $topic->isDirty('slug')) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    // Relationships

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function learningObjectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class);
    }

    public function theoryArticles(): HasMany
    {
        return $this->hasMany(TheoryArticle::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', TopicStatus::Published)
            ->where('is_active', true);
    }
}

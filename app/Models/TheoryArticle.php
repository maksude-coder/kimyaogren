<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TheoryArticle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'title',
        'slug',
        'excerpt',
        'order',
        'difficulty',
        'view_count',
        'read_time_minutes',
        'featured_image_path',
        'legacy_slug',
        'legacy_wp_id',
        'status',
        'is_active',
        'published_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'order' => 'integer',
        'difficulty' => 'integer',
        'view_count' => 'integer',
        'read_time_minutes' => 'integer',
        'legacy_wp_id' => 'integer',
        'status' => ArticleStatus::class,
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $article) {
            $article->slug ??= Str::slug($article->title);
        });
    }

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TheoryVersion::class);
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(TheoryVersion::class)->where('is_published_version', true);
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
        return $query->where('status', ArticleStatus::Published)
            ->where('is_active', true);
    }

    // Accessors

    /**
     * Public URL: the preserved WordPress address when the article has one (ADR-004).
     */
    public function publicUrl(): ?string
    {
        return $this->legacy_slug ? url($this->legacy_slug).'/' : null;
    }
}

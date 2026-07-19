<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BloomLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'code',
        'description',
        'bloom_level',
        'difficulty',
        'order',
        'is_active',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'bloom_level' => BloomLevel::class,
        'difficulty' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
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
}

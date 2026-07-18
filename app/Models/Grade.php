<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'number',
        'name',
        'description',
        'order',
        'is_active',
        'meta_title',
        'meta_description',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'number' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
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

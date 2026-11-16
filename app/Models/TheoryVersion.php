<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable content snapshot of a TheoryArticle (dd-theory-versions.md).
 */
class TheoryVersion extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'theory_article_id',
        'version_number',
        'title',
        'content',
        'format',
        'change_summary',
        'word_count',
        'is_published_version',
        'created_by_id',
    ];

    protected $casts = [
        'theory_article_id' => 'integer',
        'version_number' => 'integer',
        'word_count' => 'integer',
        'is_published_version' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(TheoryArticle::class, 'theory_article_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

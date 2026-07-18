<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramVersion extends Model
{
    protected $fillable = [
        'program_id',
        'version_number',
        'snapshot',
        'created_by_id',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'version_number' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

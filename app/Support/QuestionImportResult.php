<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Outcome of a QuestionCsvImporter run. When `errors` is not empty, nothing was imported.
 */
final class QuestionImportResult
{
    /** @var list<string> */
    public array $errors = [];

    /** @var list<string> */
    public array $skipped = [];

    public int $imported = 0;

    public function failed(): bool
    {
        return $this->errors !== [];
    }

    public function summary(): string
    {
        if ($this->failed()) {
            return count($this->errors).' hata bulundu; hiçbir soru içe aktarılmadı.';
        }

        return "{$this->imported} soru içe aktarıldı".($this->skipped !== [] ? ', '.count($this->skipped).' tekrar eden satır atlandı' : '').'.';
    }
}

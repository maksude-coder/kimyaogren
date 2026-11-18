<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Program;
use App\Support\QuestionCsvImporter;
use Illuminate\Console\Command;

/**
 * CLI twin of the admin "Import CSV" action (Sprint 3.5).
 */
class ImportQuestions extends Command
{
    protected $signature = 'questions:import
        {file : CSV file in the question template format}
        {--program= : Program code (default: the first published program)}
        {--review : Submit imported questions for review instead of leaving them as drafts}';

    protected $description = 'Import questions from a CSV file into the question bank (all or nothing)';

    public function handle(): int
    {
        $code = $this->option('program');
        $program = $code
            ? Program::where('code', $code)->first()
            : Program::published()->ordered()->first();

        if ($program === null) {
            $this->error($code ? "Program bulunamadı: {$code}" : 'Yayımlanmış program yok; --program ile bir program kodu ver.');

            return self::FAILURE;
        }

        $result = (new QuestionCsvImporter($program))->import((string) $this->argument('file'), (bool) $this->option('review'));

        foreach ($result->errors as $error) {
            $this->line("<fg=red>✕</> {$error}");
        }

        foreach ($result->skipped as $skipped) {
            $this->line("<fg=yellow>!</> {$skipped}");
        }

        $result->failed() ? $this->error($result->summary()) : $this->info("{$program->name}: {$result->summary()}");

        return $result->failed() ? self::FAILURE : self::SUCCESS;
    }
}

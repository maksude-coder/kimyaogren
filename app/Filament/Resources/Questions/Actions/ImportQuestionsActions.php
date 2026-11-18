<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Actions;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Program;
use App\Support\QuestionCsvImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Import CSV" and "Download template" on the question list (Sprint 3.5).
 */
class ImportQuestionsActions
{
    /** Errors listed in the notification; the rest are counted. */
    private const SHOWN_ERRORS = 15;

    /**
     * @return list<Action>
     */
    public static function make(): array
    {
        return [self::import(), self::template()];
    }

    private static function import(): Action
    {
        return Action::make('importCsv')
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn (): bool => QuestionResource::canCreate())
            ->modalHeading('Import questions from CSV')
            ->modalDescription('Every row is checked first. If any row has an error, nothing is imported. Imported questions start as drafts.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                Select::make('program_id')
                    ->label('Program')
                    ->options(fn () => Program::query()->ordered()->pluck('name', 'id'))
                    ->default(fn () => Program::published()->ordered()->value('id'))
                    ->required()
                    ->native(false),

                FileUpload::make('file')
                    ->label('CSV file')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->maxSize(QuestionCsvImporter::MAX_BYTES / 1024)
                    ->storeFiles(false)
                    ->required()
                    ->helperText('UTF-8 CSV, separated by ";" or ",", at most '.QuestionCsvImporter::MAX_ROWS.' rows. Use "Download template" for the columns.'),

                Toggle::make('submit_for_review')
                    ->label('Submit for review')
                    ->helperText('Off: drafts. On: the questions go straight to the review queue. Import never approves.'),
            ])
            ->action(function (array $data, Action $action): void {
                /** @var TemporaryUploadedFile $file */
                $file = $data['file'];

                $result = (new QuestionCsvImporter(Program::findOrFail($data['program_id'])))
                    ->import($file->getRealPath(), (bool) ($data['submit_for_review'] ?? false), auth()->id());

                if ($result->failed()) {
                    Notification::make()
                        ->title($result->summary())
                        ->body(self::list($result->errors))
                        ->danger()
                        ->persistent()
                        ->send();

                    $action->halt();
                }

                Notification::make()
                    ->title($result->summary())
                    ->body($result->skipped !== [] ? self::list($result->skipped) : null)
                    ->success()
                    ->send();
            });
    }

    private static function template(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Download template')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->visible(fn (): bool => QuestionResource::canCreate())
            ->action(fn (): StreamedResponse => response()->streamDownload(
                fn () => print (QuestionCsvImporter::template()),
                'soru-sablonu.csv',
                ['Content-Type' => 'text/csv; charset=UTF-8'],
            ));
    }

    /**
     * @param  list<string>  $lines
     */
    private static function list(array $lines): string
    {
        $shown = array_map('e', array_slice($lines, 0, self::SHOWN_ERRORS));
        $more = count($lines) - count($shown);

        // Filament sanitises the body HTML; the lines themselves are escaped above.
        return implode('<br>', $shown).($more > 0 ? "<br>… ve {$more} satır daha." : '');
    }
}

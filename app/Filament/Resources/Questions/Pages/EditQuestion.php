<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\Actions\QuestionWorkflowActions;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Models\Question;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...QuestionWorkflowActions::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Question $question */
        $question = $this->getRecord();

        return [...$data, ...QuestionForm::optionState($question)];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by_id'] = auth()->id();

        return $data;
    }

    /**
     * @param  Question  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Question {
            $record->update(Arr::except($data, QuestionForm::OPTION_KEYS));
            $record->syncOptions(QuestionForm::optionRows($data));

            return $record;
        });
    }
}

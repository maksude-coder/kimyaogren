<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\Actions\QuestionWorkflowActions;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Models\Question;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestion extends ViewRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...QuestionWorkflowActions::make(),
            EditAction::make(),
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
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Models\Question;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();
        $data['updated_by_id'] = auth()->id();

        return $data;
    }

    /**
     * A new question is always a Draft; its options are written with it.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Question {
            $question = Question::create(Arr::except($data, QuestionForm::OPTION_KEYS));
            $question->syncOptions(QuestionForm::optionRows($data));

            return $question;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}

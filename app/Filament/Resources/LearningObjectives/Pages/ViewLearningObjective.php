<?php

declare(strict_types=1);

namespace App\Filament\Resources\LearningObjectives\Pages;

use App\Filament\Resources\LearningObjectives\LearningObjectiveResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLearningObjective extends ViewRecord
{
    protected static string $resource = LearningObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

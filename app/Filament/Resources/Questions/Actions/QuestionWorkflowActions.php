<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Actions;

use App\Enums\QuestionStatus;
use App\Models\Question;
use Closure;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Review workflow buttons (Sprint 3.1 plan § 2, rule 5). Each is shown only
 * when the question's status allows the transition; the rules themselves
 * live on `Question`.
 */
class QuestionWorkflowActions
{
    /**
     * @return list<Action>
     */
    public static function make(): array
    {
        return [
            self::action('submitForReview', 'Submit for review', QuestionStatus::Review, 'Submitted for review', fn (Question $record) => $record->submitForReview())
                ->color('warning'),

            self::action('requestChanges', 'Request changes', QuestionStatus::Draft, 'Returned to draft', fn (Question $record) => $record->requestChanges())
                ->color('gray'),

            self::action('approve', 'Approve', QuestionStatus::Approved, 'Question approved', fn (Question $record) => $record->approve())
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Approved questions become available to students.'),

            self::action('deprecate', 'Deprecate', QuestionStatus::Deprecated, 'Question deprecated', fn (Question $record) => $record->deprecate())
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('A deprecated question is hidden from students and cannot be approved again.'),
        ];
    }

    private static function action(string $name, string $label, QuestionStatus $to, string $success, Closure $transition): Action
    {
        return Action::make($name)
            ->label($label)
            ->authorize('update')
            ->visible(fn (Question $record): bool => ! $record->trashed() && $record->status->canTransitionTo($to))
            ->action(function (Question $record) use ($transition, $success): void {
                try {
                    $transition($record);
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();

                    return;
                }

                Notification::make()->title($success)->success()->send();
            });
    }
}

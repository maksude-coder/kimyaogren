<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Tables;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Models\Topic;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('content')
                    ->label('Question')
                    ->formatStateUsing(fn (string $state): string => Str::limit(html_entity_decode(strip_tags($state)), 90))
                    ->searchable()
                    ->wrap(),

                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('learningObjective.code')
                    ->label('Objective')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (QuestionType $state): string => $state->label())
                    ->toggleable(),

                TextColumn::make('difficulty')
                    ->label('Difficulty')
                    ->formatStateUsing(fn (QuestionDifficulty $state): int => $state->value)
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('bloom_level')
                    ->label('Bloom')
                    ->formatStateUsing(fn (BloomLevel $state): string => $state->label())
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (QuestionStatus $state): string => $state->label())
                    ->color(fn (QuestionStatus $state): string => $state->color())
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('source')
                    ->label('Source')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('topic_id')
                    ->label('Topic')
                    ->options(fn () => Topic::query()
                        ->with('unit.grade')
                        ->get()
                        ->mapWithKeys(fn (Topic $topic) => [$topic->id => "{$topic->unit->grade->name} — {$topic->unit->name} — {$topic->name}"]))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(QuestionStatus::options())
                    ->multiple(),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options(QuestionType::supportedOptions()),

                SelectFilter::make('difficulty')
                    ->label('Difficulty')
                    ->options(QuestionDifficulty::options()),

                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Question $record) => ! $record->trashed()),
                DeleteAction::make()
                    ->visible(fn (Question $record) => ! $record->trashed())
                    ->successRedirectUrl(fn () => QuestionResource::getUrl('index')),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }
}

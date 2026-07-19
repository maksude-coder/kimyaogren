<?php

declare(strict_types=1);

namespace App\Filament\Resources\LearningObjectives\Tables;

use App\Enums\BloomLevel;
use App\Filament\Resources\LearningObjectives\LearningObjectiveResource;
use App\Models\Topic;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LearningObjectivesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('topic.unit.grade.program.name')
                    ->label('Program')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->wrap(),

                BadgeColumn::make('bloom_level')
                    ->label('Bloom Level')
                    ->colors([
                        'gray' => [BloomLevel::Remember->value, BloomLevel::Understand->value],
                        'info' => [BloomLevel::Apply->value, BloomLevel::Analyze->value],
                        'warning' => [BloomLevel::Evaluate->value, BloomLevel::Create->value],
                    ])
                    ->formatStateUsing(fn ($state) => BloomLevel::tryFrom($state->value ?? $state)?->label() ?? $state)
                    ->sortable(),

                TextColumn::make('difficulty')
                    ->label('Difficulty')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
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
                        ->with('unit.grade.program')
                        ->get()
                        ->mapWithKeys(fn (Topic $topic) => [$topic->id => "{$topic->unit->grade->program->name} — {$topic->unit->grade->name} — {$topic->unit->name} — {$topic->name}"])
                    )
                    ->searchable(),

                SelectFilter::make('bloom_level')
                    ->label('Bloom Level')
                    ->options(BloomLevel::options())
                    ->multiple()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => ! $record->trashed()),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->trashed())
                    ->successRedirectUrl(fn () => LearningObjectiveResource::getUrl('index')),
            ])
            ->defaultSort('order', 'asc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }
}

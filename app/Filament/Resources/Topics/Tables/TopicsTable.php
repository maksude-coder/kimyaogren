<?php

declare(strict_types=1);

namespace App\Filament\Resources\Topics\Tables;

use App\Enums\TopicStatus;
use App\Filament\Resources\Topics\TopicResource;
use App\Models\Unit;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit.grade.program.name')
                    ->label('Program')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => TopicStatus::Draft->value,
                        'success' => TopicStatus::Published->value,
                        'warning' => TopicStatus::Archived->value,
                    ])
                    ->formatStateUsing(fn ($state) => TopicStatus::tryFrom($state->value ?? $state)?->label() ?? $state)
                    ->sortable()
                    ->searchable(),

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
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(fn () => Unit::query()
                        ->with('grade.program')
                        ->get()
                        ->mapWithKeys(fn (Unit $unit) => [$unit->id => "{$unit->grade->program->name} — {$unit->grade->name} — {$unit->name}"])
                    )
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(TopicStatus::options())
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
                    ->successRedirectUrl(fn () => TopicResource::getUrl('index')),
            ])
            ->defaultSort('order', 'asc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }
}

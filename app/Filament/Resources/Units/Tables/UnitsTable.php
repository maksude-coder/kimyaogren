<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Tables;

use App\Filament\Resources\Units\UnitResource;
use App\Models\Grade;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('grade.program.name')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

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
                SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(fn () => Grade::query()
                        ->with('program')
                        ->get()
                        ->mapWithKeys(fn (Grade $grade) => [$grade->id => "{$grade->program->name} — {$grade->name}"])
                    )
                    ->searchable(),

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
                    ->successRedirectUrl(fn () => UnitResource::getUrl('index')),
            ])
            ->defaultSort('order', 'asc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Programs\Tables;

use App\Enums\ProgramStatus;
use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->color('primary'),

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

                TextColumn::make('short_description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),

                TextColumn::make('primary_language')
                    ->label('Language')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tr' => 'Turkish',
                        'en' => 'English',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'tr' => 'success',
                        'en' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('min_grade')
                    ->label('Min Grade')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('max_grade')
                    ->label('Max Grade')
                    ->sortable()
                    ->alignCenter(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => ProgramStatus::Draft->value,
                        'success' => ProgramStatus::Published->value,
                        'warning' => ProgramStatus::Archived->value,
                    ])
                    ->formatStateUsing(fn ($state) => ProgramStatus::tryFrom($state->value ?? $state)?->label() ?? $state)
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('display_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('version')
                    ->label('Version')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('archived_at')
                    ->label('Archived')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ProgramStatus::options())
                    ->multiple()
                    ->preload(),

                SelectFilter::make('primary_language')
                    ->label('Language')
                    ->options([
                        'tr' => 'Turkish',
                        'en' => 'English',
                    ])
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
                    ->successRedirectUrl(fn () => ProgramResource::getUrl('index')),
            ])
            ->toolbarActions([
                Action::make('create')
                    ->label('Create Program')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => route('filament.admin.resources.programs.create')),
            ])
            ->defaultSort('display_order', 'asc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->poll('30s');
    }
}

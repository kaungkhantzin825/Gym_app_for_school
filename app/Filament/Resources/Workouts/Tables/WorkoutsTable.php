<?php

namespace App\Filament\Resources\Workouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WorkoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Workout Title')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('total_duration_minutes')
                    ->label('Duration')
                    ->suffix(' mins')
                    ->sortable(),

                TextColumn::make('total_volume')
                    ->label('Total Volume')
                    ->suffix(' kg')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime('g:i A')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

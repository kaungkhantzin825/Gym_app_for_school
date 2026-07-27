<?php

namespace App\Filament\Resources\Meals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->square()
                    ->disk('public'),

                TextColumn::make('user.name')
                    ->label('Member')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Meal Name')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('total_calories')
                    ->label('Calories')
                    ->suffix(' kcal')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('total_protein')
                    ->label('Protein')
                    ->suffix('g')
                    ->sortable(),

                TextColumn::make('total_carbs')
                    ->label('Carbs')
                    ->suffix('g')
                    ->sortable(),

                TextColumn::make('total_fat')
                    ->label('Fat')
                    ->suffix('g')
                    ->sortable(),

                TextColumn::make('meal_time')
                    ->label('Meal Time')
                    ->dateTime('M j, Y g:i A')
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

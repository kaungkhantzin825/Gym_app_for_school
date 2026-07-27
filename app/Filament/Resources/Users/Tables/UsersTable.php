<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Member&background=10B981&color=fff'),

                TextColumn::make('name')
                    ->label('Member Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        default => 'success',
                    }),

                TextColumn::make('workouts_count')
                    ->label('Workouts')
                    ->counts('workouts')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('meals_count')
                    ->label('Meals Logged')
                    ->counts('meals')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('profile.goal')
                    ->label('Goal')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Not set'),

                TextColumn::make('created_at')
                    ->label('Joined Date')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'user' => 'User',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

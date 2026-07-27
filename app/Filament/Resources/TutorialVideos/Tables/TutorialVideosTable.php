<?php

namespace App\Filament\Resources\TutorialVideos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TutorialVideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->square()
                    ->defaultImageUrl('https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=400'),

                TextColumn::make('title')
                    ->label('Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('muscle_group')
                    ->label('Target Muscle')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gender_target')
                    ->label('Gender')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('video_url')
                    ->label('Video Link')
                    ->limit(30)
                    ->icon('heroicon-m-play')
                    ->iconColor('primary')
                    ->copyable(),

                TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

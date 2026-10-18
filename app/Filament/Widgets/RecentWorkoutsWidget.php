<?php

namespace App\Filament\Widgets;

use App\Models\Workout;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentWorkoutsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Workout::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Workout / Exercise')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('total_duration_minutes')
                    ->label('Duration')
                    ->suffix(' mins')
                    ->sortable(),
                TextColumn::make('total_volume')
                    ->label('Volume (kg)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->heading('Recent Member Workouts')
            ->paginated(false);
    }
}

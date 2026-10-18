<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Newest sign-ups, so an admin can spot a spike (or a bot run) without
 * opening the full member table.
 */
class RecentMembersWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('name')
                    ->label('Member')
                    ->weight('bold')
                    ->limit(24),
                TextColumn::make('email')
                    ->label('Email')
                    ->limit(26)
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since(),
            ])
            ->heading('Newest members')
            ->paginated(false);
    }
}

<?php

namespace App\Filament\Resources\Workouts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('total_volume')
                    ->numeric(),
                TextInput::make('total_duration_minutes')
                    ->numeric(),
            ]);
    }
}

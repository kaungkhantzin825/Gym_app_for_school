<?php

namespace App\Filament\Resources\Exercises\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('exercise_name')
                    ->required(),
                Select::make('exercise_type')
                    ->options([
                        'cardio' => 'Cardio',
                        'strength' => 'Strength',
                        'flexibility' => 'Flexibility',
                        'sports' => 'Sports',
                    ])
                    ->required(),
                TextInput::make('exercise_tutorial_url')
                    ->url()
                    ->maxLength(255),
                TextInput::make('duration_minutes')
                    ->numeric(),
                TextInput::make('calories_burned')
                    ->numeric(),
                TextInput::make('sets')
                    ->numeric(),
                TextInput::make('reps')
                    ->numeric(),
                TextInput::make('weight')
                    ->numeric(),
                DateTimePicker::make('exercise_time')
                    ->required(),
            ]);
    }
}

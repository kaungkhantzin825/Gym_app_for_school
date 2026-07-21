<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MealForm
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
                TextInput::make('photo_path'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('total_calories')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_protein')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_carbs')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_fat')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('meal_time')
                    ->required(),
            ]);
    }
}

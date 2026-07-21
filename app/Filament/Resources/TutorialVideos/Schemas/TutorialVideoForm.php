<?php

namespace App\Filament\Resources\TutorialVideos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TutorialVideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('video_url')
                    ->url()
                    ->required(),
                TextInput::make('thumbnail_url')
                    ->url(),
                Select::make('gender_target')
                    ->options([
                        'boy' => 'Boy',
                        'girl' => 'Girl',
                        'both' => 'Both',
                    ])
                    ->required()
                    ->default('both'),
                Select::make('muscle_group')
                    ->options([
                        'chest' => 'Chest',
                        'back' => 'Back',
                        'shoulders' => 'Shoulders',
                        'arms' => 'Arms',
                        'legs' => 'Legs',
                        'core' => 'Core',
                        'full_body' => 'Full Body',
                    ])
                    ->required(),
            ]);
    }
}

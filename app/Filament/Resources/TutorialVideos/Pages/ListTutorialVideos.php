<?php

namespace App\Filament\Resources\TutorialVideos\Pages;

use App\Filament\Resources\TutorialVideos\TutorialVideoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTutorialVideos extends ListRecords
{
    protected static string $resource = TutorialVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

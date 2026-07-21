<?php

namespace App\Filament\Resources\TutorialVideos\Pages;

use App\Filament\Resources\TutorialVideos\TutorialVideoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTutorialVideo extends CreateRecord
{
    protected static string $resource = TutorialVideoResource::class;
}

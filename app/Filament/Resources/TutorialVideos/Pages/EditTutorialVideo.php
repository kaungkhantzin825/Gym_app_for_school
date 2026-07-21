<?php

namespace App\Filament\Resources\TutorialVideos\Pages;

use App\Filament\Resources\TutorialVideos\TutorialVideoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTutorialVideo extends EditRecord
{
    protected static string $resource = TutorialVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

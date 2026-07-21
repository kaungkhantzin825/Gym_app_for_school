<?php

namespace App\Filament\Resources\TutorialVideos;

use App\Filament\Resources\TutorialVideos\Pages\CreateTutorialVideo;
use App\Filament\Resources\TutorialVideos\Pages\EditTutorialVideo;
use App\Filament\Resources\TutorialVideos\Pages\ListTutorialVideos;
use App\Filament\Resources\TutorialVideos\Schemas\TutorialVideoForm;
use App\Filament\Resources\TutorialVideos\Tables\TutorialVideosTable;
use App\Models\TutorialVideo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TutorialVideoResource extends Resource
{
    protected static ?string $model = TutorialVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return TutorialVideoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TutorialVideosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTutorialVideos::route('/'),
            'create' => CreateTutorialVideo::route('/create'),
            'edit' => EditTutorialVideo::route('/{record}/edit'),
        ];
    }
}

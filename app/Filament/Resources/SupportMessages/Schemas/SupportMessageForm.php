<?php

namespace App\Filament\Resources\SupportMessages\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupportMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                TextInput::make('subject')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                Textarea::make('message')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'read' => 'Read',
                        'replied' => 'Replied',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('admin_reply')
                    ->columnSpanFull(),
                Placeholder::make('replied_at')
                    ->content(fn ($record) => $record?->replied_at?->diffForHumans())
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }
}

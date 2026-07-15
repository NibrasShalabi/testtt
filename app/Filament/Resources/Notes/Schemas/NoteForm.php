<?php

namespace App\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Title')
                    ->required(),
                Textarea::make('Content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('SubjectID')
                    ->required()
                    ->numeric(),
                TextInput::make('UserID')
                    ->required()
                    ->numeric(),
            ]);
    }
}

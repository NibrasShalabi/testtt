<?php

namespace App\Filament\Resources\Flashcards\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FlashcardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('Question')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('Answer')
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

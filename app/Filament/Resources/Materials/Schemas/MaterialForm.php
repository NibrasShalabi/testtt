<?php

namespace App\Filament\Resources\Materials\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ResourceName')
                    ->required(),
                TextInput::make('FilePath')
                    ->required(),
                TextInput::make('FileType')
                    ->required(),
                TextInput::make('SubjectID')
                    ->required()
                    ->numeric(),
                TextInput::make('UserID')
                    ->required()
                    ->numeric(),
            ]);
    }
}

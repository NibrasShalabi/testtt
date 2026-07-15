<?php

namespace App\Filament\Resources\Notes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('Title'),
                TextEntry::make('SubjectID')
                    ->numeric(),
                TextEntry::make('UserID')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}

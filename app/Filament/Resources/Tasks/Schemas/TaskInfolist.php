<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('TaskPriority'),
                IconEntry::make('State')
                    ->boolean(),
                TextEntry::make('UserID')
                    ->numeric(),
                TextEntry::make('SubjectID')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}

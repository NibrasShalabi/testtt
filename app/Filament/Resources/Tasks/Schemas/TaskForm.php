<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('Description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('TaskPriority')
                    ->options(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'])
                    ->required(),
                Toggle::make('State')
                    ->required(),
                TextInput::make('UserID')
                    ->required()
                    ->numeric(),
                TextInput::make('SubjectID')
                    ->required()
                    ->numeric(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstName')
                    ->required(),
                TextInput::make('lastName')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                DatePicker::make('dateOfBirth'),
                Select::make('role')
                    ->options(['student' => 'Student', 'admin' => 'Admin'])
                    ->default('student')
                    ->required(),
            ]);
    }
}

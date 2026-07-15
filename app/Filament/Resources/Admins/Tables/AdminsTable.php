<?php

namespace App\Filament\Resources\Admins\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // الفلترة عشان يعرض فقط الـ admin
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin'))

            // الأعمدة والبحث المتوافق مع الـ CamelCase بقاعدة بياناتكِ
            ->columns([
                TextColumn::make('firstName')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lastName')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Joined Date')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()->color('success'),
                DeleteAction::make()->color('danger'),
            ]);
    }
}

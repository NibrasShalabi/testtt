<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // فلترة الطلاب بناءً على الـ role
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'student'))

            // 🌟 ربط الأعمدة والبحث بالأسماء المطابقة تماماً لقاعدة بياناتكِ (firstName & lastName)
            ->columns([
                TextColumn::make('firstName') // 🌟 مطابقة تماماً للـ Database عندكِ
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lastName') // 🌟 مطابقة تماماً للـ Database عندكِ
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

                DeleteAction::make()->color('danger'),
            ]);
    }
}

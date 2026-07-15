<?php

namespace App\Filament\Resources\Admins;

use App\Filament\Resources\Admins\Pages\CreateAdmins;
use App\Filament\Resources\Admins\Pages\EditAdmins;
use App\Filament\Resources\Admins\Pages\ListAdmins;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'firstName';

    protected static ?string $navigationLabel = 'Admins';
    protected static ?string $pluralModelLabel = 'Admins';
    protected static ?string $modelLabel = 'Admin';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Users\Schemas\UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Admins\Tables\AdminsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        // 🌟 تعديل الأسماء هنا لتطابق الجمع (CreateAdmins & EditAdmins) كما في مجلدكِ تماماً
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmins::route('/create'),
            'edit' => EditAdmins::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // الاستدعاء الصحيح للـ Schema بالنسخة الجديدة
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // الأيقونة الأصلية تبعتكِ بدون أي تعديل عشان ما تضرب
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    // 🎨 تعديل التسميات ليظهر باسم الطلاب في القائمة الجانبية
    protected static ?string $navigationLabel = 'Students';
    protected static ?string $pluralModelLabel = 'Students';
    protected static ?string $modelLabel = 'Student';
    protected static ?int $navigationSort = 2; // الترتيب الثاني

    public static function form(Schema $schema): Schema
    {
        // بيرجع يستدعي ملف الـ Form الأصلي تبعكِ الشغال
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // بيرجع يستدعي ملف الـ UsersTable اللي لسه مصلحينه وزبط مية بالمية
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),

        ];
    }

    // سحب صلاحية التعديل نهائياً من هذا المورد
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}

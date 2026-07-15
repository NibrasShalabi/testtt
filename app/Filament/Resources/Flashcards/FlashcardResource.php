<?php

namespace App\Filament\Resources\Flashcards;

use App\Filament\Resources\Flashcards\Pages\CreateFlashcard;
use App\Filament\Resources\Flashcards\Pages\EditFlashcard;
use App\Filament\Resources\Flashcards\Pages\ListFlashcards;
use App\Filament\Resources\Flashcards\Pages\ViewFlashcard;
use App\Filament\Resources\Flashcards\Schemas\FlashcardForm;
use App\Filament\Resources\Flashcards\Schemas\FlashcardInfolist;
use App\Filament\Resources\Flashcards\Tables\FlashcardsTable;
use App\Models\Flashcard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlashcardResource extends Resource
{
    protected static ?string $model = Flashcard::class;

   // هون حددنا الاسم المخصص لـ يظهر بالقائمة الجانبية بالظبط متل ما بدكِ:
    protected static ?string $navigationLabel = 'Flashcards';

   // وهون غيرنا الأيقونة لتصير شكل كروت الاستذكار الذكية (المكدسة فوق بعض):
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';
     public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function form(Schema $schema): Schema
    {
        return FlashcardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlashcardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlashcardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlashcards::route('/'),
            'create' => CreateFlashcard::route('/create'),
            'view' => ViewFlashcard::route('/{record}'),
            'edit' => EditFlashcard::route('/{record}/edit'),
        ];
    }
}

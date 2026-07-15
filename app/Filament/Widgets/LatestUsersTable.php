<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;

class LatestUsersTable extends BaseWidget
{
    // العنوان اللي رح يظهر فوق الجدول بالواجهة
    protected static ?string $heading = 'Latest Registered Users';

    // الترتيب رقم 3 ليقعد جنب الدائرة بالظبط بالسطر الثاني
    protected static ?int $sort = 1;

    // بنخليه يأخذ مساحة ثلثين الشاشة والدائرة الثلث الباقي
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // 🌟 الشرط الصح: بنجيب الحسابات يلي الـ role تبعها student بس (يعني الطلاب ومو الأدمن)
                User::query()
                    ->where('role', 'student')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined Date')
                    ->dateTime('Y-m-d H:i')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}

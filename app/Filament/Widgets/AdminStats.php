<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Material;
use App\Models\Flashcard;

class AdminStats extends BaseWidget
{
    protected static ?int $sort = 1;

    // إضافة هذه الدالة تجعل المربعات الأربعة تتوزع بانتظام على صف واحد
    protected function getColumns(): int
    {
        return 4;
    }


   protected function getStats(): array
{
    return [
        // كل مربع يأخذ 1 من أصل 4 (يعني 25% من عرض الصف)
        Stat::make('Registered Students', User::count())
            ->description('إجمالي الطلاب في النظام')
            ->descriptionIcon('heroicon-m-users')
            ->color('primary')
            ->columnSpan(1),

        Stat::make('Generated Flashcards', Flashcard::count())
            ->description('زيادة في تفاعل الذكاء الاصطناعي')
            ->descriptionIcon('heroicon-m-sparkles')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success')
            ->columnSpan(1),

        Stat::make('Uploaded Materials', Material::count())
            ->description('المقرات التعليمية')
            ->descriptionIcon('heroicon-m-document-text')
            ->color('warning')
            ->columnSpan(1),

        Stat::make('AI Activity Today', Flashcard::whereDate('created_at', today())->count())
            ->description('عمليات المعالجة اليوم')
            ->descriptionIcon('heroicon-m-bolt')
            ->color('danger')
            ->columnSpan(1),
    ];
}
}


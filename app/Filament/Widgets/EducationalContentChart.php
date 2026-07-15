<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Material;

class EducationalContentChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'File Format Distribution'; // تم تعديل العنوان ليتناسب مع البيانات

    // بنعطيها حجم (1) عشان تاخد نصف الشاشة وتقعد جنب الجدول بالأسفل متل الصورة
    protected int | string | array $columnSpan = 'full';

    protected ?array $options = [
        'aspectRatio' => 1.8, // بيخلي الدائرة دائماً متل الصورة ما بتتطول ولا تتقلص
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'bottom', // المؤشرات تحت الدائرة
            ],
        ],
        'cutout' => '85%', // 🌟 الدائرة نحيفة جداً وأنيقة متل الصورة تماماً
    ];

    // 📐 تصغير الطول الكلي للدائرة عشان ما تطلع ضخمة وتطلع ناعمة متل الصورة
    protected function getHeight(): ?string
    {
        return '290px';
    }


    protected function getData(): array
    {
        $pdfCount = Material::where('FileType', 'pdf')->count();
        $wordCount = Material::where('FileType', 'word')->count();
        $imageCount = Material::where('FileType', 'image')->count();

        // تأكدي أن المجموع ليس صفراً لتجنب اختفاء الدائرة
        $total = $pdfCount + $wordCount + $imageCount;

        // إذا كان المجموع 0، نضع 1 لكل تصنيف لتظهر الدائرة فارغة ولكن موجودة
        $data = ($total === 0) ? [1, 1, 1] : [$pdfCount, $wordCount, $imageCount];
        $colors = ($total === 0) ? ['#e5e7eb', '#e5e7eb', '#e5e7eb'] : ['#2563eb', '#f43f5e', '#eab308'];

        return [
            'datasets' => [
                [
                    'label' => 'File Types',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'hoverBackgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['PDF Files', 'Word Files', 'Images'],

        ];

    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

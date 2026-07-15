<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use App\Models\Material;

class PlatformActivityChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Platform Monthly Activity';

    protected int | string | array $columnSpan = [
        'md'=>2,
        'xl'=>2,
    ];


    // 🌟 هاد هو قسم الخيارات (Options) اللي بنحط جواته الـ scales:
    protected ?array $options = [

        'elements' => [
            'line' => [
                'tension' => 0.45,
                'borderWidth' => 4,
            ],
            'point' => [
                'radius' => 2,
                'hoverRadius' => 5,
            ],
        ],

        // ⬇️ هاد هو المكان الصحيح للـ scales (تحت الـ elements وفوق الـ plugins):
        'scales' => [
            'x' => [
                'grid' => [
                    'display' => true, // تفعيل خطوط الشبكة العمودية بالطول
                    'color' => 'rgba(128, 128, 128, 0.15)', // لون رمادي ناعم وخفيف جداً بالخلفية
                ],
            ],
            'y' => [
                'grid' => [
                    'display' => true, // تفعيل خطوط الشبكة الأفقية بالعرض
                    'color' => 'rgba(128, 128, 128, 0.15)', // لون رمادي ناعم وخفيف
                ],
                'ticks' => [
                    'stepSize' => 1, // بيمنع الكسور العشرية (0، 1، 2، 3...)
                ],
            ],
        ],

        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'top',
                'align' => 'end',
            ],
        ],
        'maintainAspectRatio' => false,
    ];

    protected function getHeight(): ?string
    {
        return '330px';
    }

    protected function getData(): array
    {
        $studentsData = [];
        $materialsData = [];

        for ($month = 1; $month <= 12; $month++) {
            $studentsData[] = User::where('role', 'student')->whereMonth('created_at', $month)->whereYear('created_at', date('Y'))->count();
            $materialsData[] = Material::whereMonth('created_at', $month)->whereYear('created_at', date('Y'))->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Registered Students',
                    'data' => $studentsData,
                    'borderColor' => '#06b6d4',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                ],
                [
                    'label' => 'Uploaded Materials',
                    'data' => $materialsData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

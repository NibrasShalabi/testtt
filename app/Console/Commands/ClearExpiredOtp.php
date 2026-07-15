<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearExpiredOtp extends Command
{
    // الاسم الذي سنستدعي به الأمر في الـ Terminal
    protected $signature = 'otp:clear-expired';

    // وصف بسيط لما يفعله الأمر
    protected $description = 'حذف رموز الـ OTP منتهية الصلاحية من قاعدة البيانات لتخفيف حجم الجداول';

    public function handle()
    {
        // تحديد الوقت: أي كود تم إنشاؤه قبل أكثر من ساعة (60 دقيقة) يعتبر منتهي الصلاحية تماماً
        $expirationTime = now()->subMinutes(60);

        // حذف السجلات التي ينطبق عليها الشرط
        $deletedRows = DB::table('password_reset_tokens')
            ->where('created_at', '<', $expirationTime)
            ->delete();

        // طباعة رسالة نجاح في شاشة الـ Terminal للاختبار
        $this->info("تم بنجاح تنظيف الجدول وحذف {$deletedRows} رمز OTP منتهي الصلاحية.");
        
        return Command::SUCCESS;
    }
}
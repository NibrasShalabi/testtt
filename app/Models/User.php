<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// 1️⃣ ضفنا هاد السطر فوق عشان الفلمينت يتعرف على عقود الأمان
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser // 2️⃣ ضفنا implements FilamentUser هون
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'fcm_token',
        'dateOfBirth',
        'role',
        'ai_pdf_limit',//ضفنا حقل الرصيد هون
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 3️⃣ هون حطينا الدالات الجديدة بآخر الملف قبل القوس الأخير:

    /**
     * الـ Accessor السحري اللي بيخلي الفلمينت يشوف حقل اسمه name غصب عنه
     */
    public function getNameAttribute(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * دالة التحقق من صلاحية الدخول للوحة التحكم لفلمينت v4
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->role === 'admin';
    }
}

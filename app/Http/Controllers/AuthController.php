<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // تم إضافته للتعامل مع جدول الـ OTP
use Illuminate\Support\Facades\Mail; // تم إضافته لإرسال الإيميل

class AuthController extends Controller
{

 public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'firstName'   => 'required|string|between:2,100',
        'lastName'    => 'required|string|between:2,100',
        'email'       => 'required|string|email|max:255|unique:users',
        'password'    => 'required|string|min:8|max:255',
        'dateOfBirth' => 'required|date|before:-15 years',
    ]);

    if ($validator->fails()) {
        return $this->errorResponse('البيانات المرسلة غير صالحة', $validator->errors(), 422);
    }

    $user = User::create([
        'firstName'   => $request->firstName,
        'lastName'    => $request->lastName,
        'email'       => $request->email,
        'password'    => Hash::make($request->password),
        'dateOfBirth' => $request->dateOfBirth,
        'role'        => 'student',
    ]);

    // --- كود الـ OTP المدمج ---

    // 1. توليد رمز OTP
    $otp = rand(1000, 9999);

    // 2. حفظ الرمز في جدول password_reset_tokens
    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token'      => Hash::make($otp),
            'created_at' => now()
        ]
    );

    // 3. إرسال الإيميل
    try {
        Mail::raw("Your Peak Focus Account Verification Code is: $otp", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Account Verification OTP');
        });
        } catch (\Exception $e) {
        // بدلاً من الرسالة الثابتة، سنرجع نص الخطأ الحقيقي
        return $this->errorResponse($e->getMessage(), null, 500);
    }


    // --- نهاية كود الـ OTP ---

    return $this->successResponse('تم إنشاء الحساب بنجاح، يرجى تفعيل الحساب بكود الـ OTP المرسل إلى إيميلك.', $user, 201);
}

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return $this->errorResponse('كلمة المرور أو الإيميل غير صحيحة', null, 401);
    }

    // 🛡️ فحص التفعيل (OTP Verification Check)
    // إذا كان الحقل null، يعني الحساب لسه ما تفعل
    if (is_null($user->email_verified_at)) {
        return $this->errorResponse('هذا الحساب غير مفعّل، يرجى تفعيل الحساب عبر رمز الـ OTP أولاً.', null, 403);
    }

    // 🔔 تحديث توكن الإشعارات (FCM Token) إذا أرسلته الفرونت إند عند تسجيل الدخول
    if ($request->filled('fcm_token')) {
        $user->fcm_token = $request->input('fcm_token');
        $user->save();
    }

    $user->tokens()->delete();

    $token = $user->createToken('peak_token')->plainTextToken;

    return $this->successResponse('تم تسجيل الدخول بنجاح', [
        'token' => $token,
        'user'  => $user
    ], 200);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم تسجيل الخروج بنجاح', null, 200);
    }

public function verifyRegistrationOtp(Request $request)
{
    // 1. التحقق من البيانات المدخلة
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp'   => 'required|numeric',
    ]);

    // مفتاح فريد لكل مستخدم بناءً على إيميله لتتبع محاولاته
    $limiterKey = 'verify-otp:' . $request->email;

    // 2. التحقق: هل المستخدم تجاوز حد الـ 3 محاولات؟
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 3)) {
        // حساب كم ثانية متبقية لفك الحظر
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        $minutes = ceil($seconds / 60);

        return $this->errorResponse(
            "لقد تجاوزت الحد المسموح من المحاولات الخاطئة. تم حظرك مؤقتاً، يرجى المحاولة بعد {$minutes} دقائق.",
            null,
            429
        );
    }

    // 3. جلب رمز الـ OTP من الداتابيز
    $passwordReset = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    // 4. التحقق من صحة الرمز
    if (!$passwordReset || !Hash::check($request->otp, $passwordReset->token)) {
        // تسجيل محاولة فاشلة في الـ Limiter وتحديد مدة الحظر بـ 30 دقيقة (1800 ثانية)
        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 1800);

        // حساب المحاولات المتبقية للمستخدم لإظهارها بالرسالة كتحسين لتجربة المستخدم
        $attemptsLeft = \Illuminate\Support\Facades\RateLimiter::retriesLeft($limiterKey, 3);

        return $this->errorResponse("رمز التحقق غير صحيح. المحاولات المتبقية: {$attemptsLeft}", null, 422);
    }

    // 5. التحقق من صلاحية الوقت (10 دقائق)
    if (\Carbon\Carbon::parse($passwordReset->created_at)->addMinutes(10)->isPast()) {
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return $this->errorResponse('لقد انتهت صلاحية الكود، يرجى طلب كود جديد.', null, 422);
    }

    // 6. تفعيل الحساب في جدول الـ users
    $user = User::where('email', $request->email)->first();
    $user->email_verified_at = now();
    $user->save();

    // 7. حذف الرمز من الجدول بعد الاستخدام الناجح
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    // 8. تصفير عداد المحاولات الفاشلة بنجاح بعد التوثيق الصحيح
    \Illuminate\Support\Facades\RateLimiter::clear($limiterKey);

    return $this->successResponse('تم توثيق الحساب بنجاح، يمكنك الآن تسجيل الدخول.', null, 200);
}
// إذا أردتِ برمجة دالة الـ resend (إعادة الإرسال):
// لكي لا تضطري لعمل Register من جديد في كل مرة، يمكنك إضافة هذه الدالة البسيطة في الـ AuthController:

public function resendOtp(Request $request)
{
    $request->validate(['email' => 'required|email|exists:users,email']);

    // 1. حذف أي كود قديم
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    // 2. توليد كود جديد (نفس منطق كود التسجيل عندك)
    $otp = rand(1000, 9999);
    DB::table('password_reset_tokens')->insert([
        'email' => $request->email,
        'token' => Hash::make($otp),
        'created_at' => now()
    ]);

    // 3. هنا يتم إرسال الإيميل (استخدمي Mail::to(...))

    return $this->successResponse('تم إرسال كود جديد إلى بريدك الإلكتروني.', null, 200);
}


    // 1. دالة طلب الـ OTP وإرساله للـ Mailtrap
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // توليد رمز OTP عشوائي من 4 أرقام
        $otp = rand(1000, 9999);

        // حفظ الرمز في جدول password_reset_tokens المدمج باللارافيل لنتحقق منه لاحقاً
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp), // تشفير الرمز للأمان
                'created_at' => now()
            ]
        );

        try {
            // إرسال الإيميل الفعلي إلى الـ Mailtrap
            Mail::raw("Your Peak Focus OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Password Reset OTP');
            });

            // 🟢 تم التعديل لتوحيد الرد بنجاح
            return $this->successResponse('تم إرسال رمز الـ OTP بنجاح إلى حسابك بـ Mailtrap', null, 200);

        } catch (\Exception $e) {
            // 🔴 تم التعديل لتوحيد رد الخطأ عند فشل الإرسال
            return $this->errorResponse('فشل إرسال الإيميل، تأكد من إعدادات الـ .env: ' . $e->getMessage(), null, 500);
        }
    }

    // 2. دالة التحقق من الـ OTP وتغيير الباسورد فعلياً
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|numeric',
            'password' => 'required|string|min:8|max:255',
        ]);

        // جلب الرمز المخزن لهذا الإيميل
        $row = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // التحقق إذا كان الرمز موجود أو منتهي الصلاحية
        if (!$row || !Hash::check($request->otp, $row->token)) {
            // 🔴 تم التعديل لتوحيد رد الخطأ
            return $this->errorResponse('رمز الـ OTP غير صحيح أو منتهي الصلاحية', null, 422);
        }

        // تحديث كلمة مرور المستخدم بالجديدة
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // حذف الرمز من الجدول بعد الاستخدام لمرة واحدة لزيادة الأمان
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم إعادة تعيين كلمة المرور بنجاح!', null, 200);
    }
}

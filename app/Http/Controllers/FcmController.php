<?php
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
<<<<<<< Updated upstream
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmController extends Controller
{
    protected $messaging;

    public function __construct()
    {
        // تهيئة اتصال الـ Firebase باستخدام ملف الـ JSON الخاص بمشروعكم
        $credentialsPath = base_path('firebase_credentials.json');

        if (file_exists($credentialsPath)) {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * 🟢 دالة إرسال إشعار لمستخدم معين
     */
    public function sendNotification(Request $request): JsonResponse
    {
        // 1. التحقق من البيانات القادمة من البوستمان أو الفرونت إند
        $request->validate([
            'user_id' => 'required|exists:users,id', // رقم المستخدم المستهدف
            'title'   => 'required|string|max:255',   // عنوان الإشعار
            'body'    => 'required|string',           // نص الإشعار
        ]);

        // 2. جلب المستخدم من قاعدة البيانات للوصول إلى التوكن الخاص به
        $user = User::find($request->user_id);

        // 3. التحقق من وجود توكن لجهاز هذا المستخدم (fcm_token)
        if (!$user->fcm_token) {
            return response()->json([
                'status'  => 'error',
=======
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Alert;

class FcmController extends Controller
{
    protected function getAccessToken(): ?string
    {
        $credentialsPath = base_path('firebase_credentials.json');
        if (!file_exists($credentialsPath)) {
            return null;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        // بناء JWT موقّع يدويًا (بدون أي مكتبة composer إضافية)
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        $signingInput = "$base64Header.$base64Payload";

        openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');
        $base64Signature = $this->base64UrlEncode($signature);

        $jwt = "$signingInput.$base64Signature";

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            \Log::error('FCM token error: ' . $response->body());
            return null;
        }

        return $response->json('access_token');
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if (!$user->fcm_token) {
            return response()->json([
                'status' => 'error',
>>>>>>> Stashed changes
                'message' => 'fcm_token غير موجود لهذا المستخدم'
            ], 404);
        }

<<<<<<< Updated upstream
        // 4. التأكد من أن ملف الـ Firebase مهيأ بشكل صحيح
        if (!$this->messaging) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ملف إعدادات Firebase JSON غير موجود بالسيرفر'
            ], 500);
        }

        try {
            // 5. بناء الإشعار وإرساله عبر مكتبة Firebase
            $notification = Notification::create($request->title, $request->body);

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification($notification);

            $this->messaging->send($message);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إرسال الإشعار بنجاح!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'فشل الإرسال: ' . $e->getMessage()
            ], 500);
        }
    }
}
=======
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'تعذر توليد Access Token من Firebase'
            ], 500);
        }

        $credentials = json_decode(file_get_contents(base_path('firebase_credentials.json')), true);
        $projectId = $credentials['project_id'];

        $response = Http::withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->body,
                    ],
                ],
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل الإرسال: ' . $response->body()
            ], 500);
        }

        // تخزين الإشعار بجدول alerts عشان يظهر بقائمة التطبيق
        Alert::create([
            'Address' => $request->title,
            'Message' => $request->body,
            'UserID'  => $request->user_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال الإشعار بنجاح!'
        ], 200);
    }
    public function sendPushAndStoreAlert(int $userId, string $title, string $body): bool
{
    $user = User::find($userId);
    if (!$user || !$user->fcm_token) {
        return false;
    }

    $accessToken = $this->getAccessToken();
    if (!$accessToken) {
        return false;
    }

    $credentials = json_decode(file_get_contents(base_path('firebase_credentials.json')), true);
    $projectId = $credentials['project_id'];

    $response = Http::withToken($accessToken)->post(
        "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
        [
            'message' => [
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ],
        ]
    );

    if (!$response->successful()) {
        \Log::error('FCM push failed (share): ' . $response->body());
        return false;
    }

    Alert::create([
        'Address' => $title,
        'Message' => $body,
        'UserID'  => $userId,
    ]);

    return true;
}
}
>>>>>>> Stashed changes

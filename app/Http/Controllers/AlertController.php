<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    // 🟢 جلب التنبيهات بالرد الموحد
    public function index(): JsonResponse
    {
        // البحث باستخدام الاسم المطابق تماماً للداتابيز
        $alerts = Alert::where('UserID', auth()->id())->get();

        return $this->successResponse('تم جلب التنبيهات بنجاح', $alerts, 200);
    }

    // 🟢 إنشاء تنبيه جديد بالرد الموحد
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'Address' => 'required|string|max:255',
            'Message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('البيانات المرسلة غير صالحة', $validator->errors(), 422);
        }

        // إنشاء التنبيه بالاسم المطابق للداتابيز
        $alert = Alert::create([
            'Address' => $request->Address,
            'Message' => $request->Message,
            'UserID'  => auth()->id(),
        ]);

        return $this->successResponse('تم إنشاء التنبيه بنجاح', $alert, 201);
    }
    // 🔴 حذف تنبيه محدد بالرد الموحد
    public function destroy($id): JsonResponse
    {
        // البحث عن التنبيه المختار والتأكد أنه يخص المستخدم الحالي بالظبط
        $alert = Alert::where('AlertID', $id)->where('UserID', auth()->id())->first();

        // إذا لم يتم العثور على التنبيه أو كان يخص مستخدماً آخر
        if (!$alert) {
            return $this->errorResponse('التنبيه غير موجود أو لا تملك صلاحية حذفه', null, 404);
        }

        // تنفيذ الحذف الفعلي من قاعدة البيانات
        $alert->delete();

        return $this->successResponse('تم حذف التنبيه بنجاح', null, 200);
    }
    // 🟢 تحويل حالة التنبيه إلى مقروء
    public function markAsRead($id): JsonResponse
    {
        $alert = Alert::where('AlertID', $id)->where('UserID', auth()->id())->first();

        if (!$alert) {
            return $this->errorResponse('التنبيه غير موجود', null, 404);
        }

        // تحديث الحالة إلى 1 (true)
        $alert->update(['IsRead' => true]);

        return $this->successResponse('تم تحديد التنبيه كمقروء', $alert, 200);
    }
}

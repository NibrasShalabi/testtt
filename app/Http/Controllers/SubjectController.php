<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    // عرض مواد المستخدم المسجل حالياً فقط مع الموارد التابعة لها
    public function index()
    {
        // جلب المواد مع الموارد التابعة لها (علاقة Eager Loading)
        $subjects = Subject::with('resources')->where('user_id', Auth::id())->get();
        
        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم جلب المواد بنجاح', $subjects, 200);
    }

    // إضافة مادة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'subjectName' => 'required|string|max:255',
            'study_status' => 'nullable|in:pending,studying,completed,review',
        ]);

        $subject = Subject::create([
            'subjectName' => $request->subjectName,
            'description' => $request->description,
            'study_status' => $request->study_status ?? 'pending',
            'user_id' => Auth::id(),
        ]);

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند الإنشاء (مع كود 201)
        return $this->successResponse('تمت إضافة المادة للمكتبة!', $subject, 201);
    }
    
    // التعديل
    public function update(Request $request, $id)
    {
        // التأكد أن المادة موجودة وتخص المستخدم المسجل
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // التحقق من البيانات (Validation) لضمان أن الحالة المدخلة صحيحة
        $request->validate([
            'subjectName'  => 'nullable|string|max:255',
            'study_status' => 'nullable|in:pending,studying,completed,review',
        ]);

        $subject->update([
            'subjectName'  => $request->subjectName ?? $subject->subjectName,
            'description'  => $request->description ?? $subject->description,
            'study_status' => $request->study_status ?? $subject->study_status, // تعديل الحالة
        ]);

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند التعديل
        return $this->successResponse('تم التعديل بنجاح', $subject, 200);
    } 

    // الحذف
    public function destroy($id)
    {
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $subject->delete();

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند الحذف
        return $this->successResponse('تم الحذف بنجاح', null, 200);
    }
}
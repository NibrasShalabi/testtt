<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    // عرض ملاحظات المستخدم فقط
    public function index()
    {
        $notes = Note::where('UserID', Auth::id())->get();
        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم جلب الملاحظات بنجاح', $notes, 200);
    }

    // إضافة ملاحظة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'Content' => 'required',
            'SubjectID' => 'required|exists:subjects,id'
        ]);

        $note = Note::create([
            'Title' => $request->Title,
            'Content' => $request->Content,
            'SubjectID' => $request->SubjectID,
            'UserID' => Auth::id() // الحماية: ربط الملاحظة باللي سجل دخول حالياً
        ]);

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند الإنشاء مع كود الحالة 201
        return $this->successResponse('Note created!', $note, 201);
    }

    // التعديل
    public function update(Request $request, $id) 
    {
        $note = Note::where('id', $id)->where('UserID', Auth::id())->firstOrFail();

        $note->update([
            'Title' => $request->Title ?? $note->Title,
            'Content' => $request->Content ?? $note->Content,
            'SubjectID' => $request->SubjectID ?? $note->SubjectID,
        ]);

        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم تحديث الملاحظة', $note, 200);
    }

    // الحذف
    public function destroy($id)  
    {
        $note = Note::where('id', $id)->where('UserID', Auth::id())->firstOrFail();
        $note->delete();

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند الحذف
        return $this->successResponse('تم حذف الملاحظة بنجاح', null, 200);
    }
}
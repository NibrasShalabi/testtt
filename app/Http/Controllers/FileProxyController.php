<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileProxyController extends Controller
{
    public function download(Request $request)
    {
        $request->validate(['path' => 'required|string']);

        $relativePath = ltrim($request->query('path'), '/');
        $relativePath = preg_replace('#^storage/#', '', $relativePath);

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, 'الملف غير موجود');
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $mime = Storage::disk('public')->mimeType($relativePath);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}

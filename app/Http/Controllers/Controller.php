<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 🟢 تابع موحد لإرجاع ردود النجاح
     */
    protected function successResponse($message = 'تمت العملية بنجاح', $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    /**
     * 🔴 تابع موحد لإرجاع ردود الفشل والأخطاء
     */
    protected function errorResponse($message = 'حدث خطأ ما', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        // إذا كان هناك تفاصيل أخطاء (مثل أخطاء الـ Validation) بنضيفها للرد
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
<?php
namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PPTIOFactory;
use Spatie\PdfToImage\Pdf;
use App\Http\Controllers\FcmController;
use Smalot\PdfParser\Parser as PdfParser;

class MaterialController extends Controller {

    public function index() {
        $resources = Material::where('UserID', Auth::id())->paginate(15);
        return $this->successResponse('تم جلب الموارد بنجاح', $resources, 200);
    }

    public function sharedWithMe() {
        $materialIds = \App\Models\MaterialShare::where('user_id', \Illuminate\Support\Facades\Auth::id())
                                               ->pluck('material_id');

        $sharedResources = \App\Models\Material::whereIn('id', $materialIds)->paginate(15);

        return $this->successResponse('تم جلب الموارد المشتركة بنجاح', $sharedResources, 200);
    }

    public function store(Request $request) {
        $request->validate([
            'ResourceName' => 'required|string|max:255',
            'file' => 'required|file|extensions:pdf,png,jpg,jpeg|max:2048',
            'SubjectID' => 'required|exists:subjects,id'
        ]);

        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $fileSize = $file->getSize();

            $totalUsedSpace = Material::where('UserID', Auth::id())->sum('file_size');

            $maxQuota = 50 * 1024 * 1024;

            if (($totalUsedSpace + $fileSize) > $maxQuota) {
                $remainingSpaceMB = round(($maxQuota - $totalUsedSpace) / (1024 * 1024), 2);

                return $this->errorResponse(
                    "مساحتك التخزينية لا تكفي! الحد الأقصى 50MB. المساحة المتبقية لك هي: {$remainingSpaceMB} MB",
                    null,
                    403
                );
            }

            $path = $file->store('materials', 'public');
            $url = Storage::url($path);

            $resource = Material::create([
                'ResourceName' => $request->ResourceName,
                'FilePath' => $url,
                'FileType' => $file->getClientOriginalExtension(),
                'file_size' => $fileSize,
                'UserID' => Auth::id(),
                'SubjectID' => $request->SubjectID
            ]);

            return $this->successResponse('تم إضافة المورد بنجاح', $resource, 201);
        }
    }

    public function update(Request $request, $id) {
       $resource = Material::where('id', $id)->where('UserID', Auth::id())->firstOrFail();

        $request->validate([
            'ResourceName' => 'required|string|max:255',
        ]);

       $resource->update([
            'ResourceName' => $request->ResourceName,
       ]);

        return $this->successResponse('تم تعديل اسم المورد بنجاح', $resource, 200);
    }

   public function destroy($id) {
        $resource = Material::where('id', $id)->where('UserID', Auth::id())->firstOrFail();

        $fullPath = public_path($resource->FilePath);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $resource->delete();

        return $this->successResponse('تم حذف المورد والملف من السيرفر بنجاح', null, 200);
    }

    public function shareResource(Request $request)
    {
        $request->validate([
            'material_id'       => 'required|exists:materials,id',
            'shared_with_email' => 'required|email|exists:users,email',
            'access_level'      => 'required|in:View,Edit,View/Edit'
        ]);

        $resource = Material::where('id', $request->material_id)
                            ->where('UserID', Auth::id())
                            ->first();

        if (!$resource) {
            return $this->errorResponse('غير مصرح لك بمشاركة هذا المورد', null, 403);
        }

        $sharedUser = \App\Models\User::where('email', $request->shared_with_email)->first();

        MaterialShare::updateOrInsert(
            [
                'material_id' => $request->material_id,
                'user_id' => $sharedUser->id
            ],
            [
                'access_level' => $request->access_level,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        $ownerName = Auth::user()->firstName ?? 'A user';

        if ($sharedUser->fcm_token) {
            app(FcmController::class)->sendPushAndStoreAlert(
                $sharedUser->id,
                'مصدر جديد مشارك معك',
                "{$ownerName} شاركك مصدر: {$resource->ResourceName}"
            );
        }

        try {
            $fullUrl = url($resource->FilePath);

            Mail::send([], [], function ($message) use ($request, $resource, $ownerName, $fullUrl) {
                $message->to($request->shared_with_email)
                        ->subject('New Resource Shared With You - Peak Focus')
                        ->html("Hello! {$ownerName} has shared a resource with you named: '{$resource->ResourceName}'. You have [{$request->access_level}] access.<br><br>View the file directly here: <a href='{$fullUrl}' target='_blank'>{$fullUrl}</a>");
            });

            return $this->successResponse('تم مشاركة المورد وإرسال الرابط القابل للضغط بنجاح!', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse('تم التخزين بالداتابيز، لكن فشل إرسال إيميل التنبيه: ' . $e->getMessage(), null, 500);
        }
    }

    public function saveSharedResource(Request $request, $id) {
        $sharedRecord = \App\Models\MaterialShare::where('material_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$sharedRecord) {
            return $this->errorResponse('هذا المورد غير متاح لك لحفظه', null, 403);
        }

        $originalMaterial = Material::find($id);
        if (!$originalMaterial) {
            return $this->errorResponse('الملف الأصلي لم يعد موجوداً أو تم حذفه من قبل صاحبه', null, 404);
        }

        $fileSize = $originalMaterial->file_size;
        $totalUsedSpace = Material::where('UserID', Auth::id())->sum('file_size');
        $maxQuota = 50 * 1024 * 1024;

        if (($totalUsedSpace + $fileSize) > $maxQuota) {
            $remainingSpaceMB = round(($maxQuota - $totalUsedSpace) / (1024 * 1024), 2);
            return $this->errorResponse("مساحتك التخزينية لا تكفي لحفظ هذا الملف! المساحة المتبقية لك: {$remainingSpaceMB} MB", null, 403);
        }

        $relativePath = str_replace('/storage/', '', $originalMaterial->FilePath);
        $oldFullPath  = storage_path('app/public/' . $relativePath);
        if (!file_exists($oldFullPath)) {
            return $this->errorResponse('عذراً، الملف    الفعلي مفقود من الخادم', null, 404);
        }

        $extension = pathinfo($oldFullPath, PATHINFO_EXTENSION);
        $newFileName = \Illuminate\Support\Str::random(40) . '.' . $extension;
        $newRelativePath = 'materials/' . $newFileName;

        \Illuminate\Support\Facades\Storage::disk('public')->put($newRelativePath, file_get_contents($oldFullPath));
        $newUrl = \Illuminate\Support\Facades\Storage::url($newRelativePath);

        $newResource = Material::create([
            'ResourceName' => $originalMaterial->ResourceName . ' (نسخة)',
            'FilePath' => $newUrl,
            'FileType' => $originalMaterial->FileType,
            'file_size' => $originalMaterial->file_size,
            'UserID' => Auth::id(),
            'SubjectID' => $originalMaterial->SubjectID
        ]);

        $sharedRecord->delete();

        return $this->successResponse('تم حفظ المورد في ملفاتك بنجاح وأصبح مستقلاً تماماً عن صاحبه الأصلي', $newResource, 201);
    }


    public function testPdfText(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,pptx,docx,png,jpg,jpeg',
            'save_flashcards' => 'nullable|boolean',
            'SubjectID' => 'required_if:save_flashcards,1|exists:subjects,id'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->ai_pdf_limit !== null && $user->ai_pdf_limit <= 0) {
            return $this->errorResponse('نفد رصيدك المخصص لاستخدام الذكاء الاصطناعي!', null, 403);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
            return $this->processImageFile($file, $user, $request);
        }

        $text = $this->extractTextFromDocument($file, $extension);

        if (empty(trim($text))) {
            return $this->errorResponse('لم نتمكن من استخراج نص من الملف. تأكدي أن الملف واضح!', null, 400);
        }

        return $this->processTextWithOpenAi($text, $file, $user);
    }

    private function extractTextFromDocument($file, $extension)
    {
        $text = "";
        if ($extension === 'pdf') {
            $text = $this->extractTextUsingOCR($file->path());
        } elseif ($extension === 'docx') {
            $phpWord = WordIOFactory::load($file->path());
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) $text .= $element->getText() . " ";
                }
            }
        } elseif ($extension === 'pptx') {
            $phpPresentation = PPTIOFactory::load($file->path());
            foreach ($phpPresentation->getAllSlides() as $slide) {
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) $text .= $shape->getPlainText() . " ";
                }
            }
        }
        return $text;
    }

    private function processImageFile($file, $user, $request)
    {
        try {
            $response = $this->processImageWithAi($file, $user);
            if ($response->successful()) {
                $aiResult = json_decode($response->json('choices')[0]['message']['content'], true);

                $summary = \App\Models\AiSummary::create(['UserID' => $user->id, 'file_name' => $file->getClientOriginalName(), 'content' => $aiResult]);

                if ($user->ai_pdf_limit !== null) {
                    $user->decrement('ai_pdf_limit');
                    $user->refresh();
                }

                $flashcards = $aiResult['flashcards'] ?? [];

                return $this->successResponse("تمت المعالجة بنجاح!", [
                    'summary_id' => $summary->id,
                    'flashcards' => $flashcards
                ]);
            }
            return $this->errorResponse('فشل الذكاء الاصطناعي', $response->json(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('خطأ في معالجة الصورة', ['error' => $e->getMessage()], 500);
        }
    }


    private function processTextWithOpenAi($text, $file, $user)
    {
        $shortText = mb_substr($text, 0, 4000);
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => 'أنت معلم خبير. استخرج من النص ملخصاً كأسئلة وأجوبة (Flashcards) بصيغة JSON'],
                        ['role' => 'user', 'content' => $shortText]
                    ]
                ]);

            if ($response->successful()) {
                $aiResult = json_decode($response->json('choices')[0]['message']['content'], true);

                $summary = \App\Models\AiSummary::create(['UserID' => $user->id, 'file_name' => $file->getClientOriginalName(), 'content' => $aiResult]);

                if ($user->ai_pdf_limit !== null) {
                    $user->decrement('ai_pdf_limit');
                }

                return $this->successResponse("تمت المعالجة بنجاح!", [
                    'summary_id' => $summary->id,
                    'flashcards' => $aiResult['flashcards'] ?? []
                ]);
            }
            return $this->errorResponse('فشل الاتصال بـ AI', $response->json(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('خطأ أثناء المعالجة', ['error' => $e->getMessage()], 500);
        }
    }

    private function processImageWithAi($file, $user)
    {
        $imageData = base64_encode(file_get_contents($file->path()));
        $mimeType = $file->getClientMimeType();

        $response = \Illuminate\Support\Facades\Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'أنت معلم خبير ومصحح نصوص. انظر إلى الصورة بتمعن، اقرأ النص الموجود فيها (سواء كان مطبوعاً أو مكتوباً بخط اليد)، ثم استخرج منه ملخصاً كأسئلة وأجوبة (Flashcards) بصيغة JSON تماماً كالهيكل التالي: {"flashcards": [{"question": "...", "answer": "..."}]}'
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'استخرج البطاقات من هذه الصورة باللغة العربية وبشكل دقيق.'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageData}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        return $response;
    }

    private function extractTextUsingOCR($filePath)
    {
        $pdf = new Pdf($filePath);
        $totalPages = $pdf->getNumberOfPages();
        $extractedText = "";

        $pagesToRead = min($totalPages, 3);

        for ($i = 1; $i <= $pagesToRead; $i++) {
            $imagePath = storage_path('app/temp_page_' . $i . '.jpg');

            $pdf->setPage($i)->saveImage($imagePath);

            $imageData = base64_encode(file_get_contents($imagePath));

            $response = \Illuminate\Support\Facades\Http::withToken(config('services.openai.key'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'أنت خبير في التعرف البصري على الحروف (OCR). استخرج النص من هذه الصورة باللغة العربية أو الإنجليزية كنص عادي فقط بدون أي مقدمات.'
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => "data:image/jpeg;base64,{$imageData}"]
                                ]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $extractedText .= $response->json('choices')[0]['message']['content'] . "\n\n";
            }

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return $extractedText;
    }

    public function saveFlashcardsFromSummary(Request $request)
    {
        $request->validate([
            'summary_id' => 'required|exists:ai_summaries,id',
            'SubjectID' => 'required|exists:subjects,id'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        $summary = \App\Models\AiSummary::where('id', $request->summary_id)
                                        ->where('UserID', $user->id)
                                        ->firstOrFail();

        $flashcards = $summary->content['flashcards'] ?? [];

        if (empty($flashcards)) {
            return $this->errorResponse('لا يوجد أسئلة وأجوبة في هذا التلخيص لحفظها.', null, 400);
        }

        $savedCards = [];
        foreach ($flashcards as $card) {
            $savedCards[] = \App\Models\Flashcard::create([
                'UserID' => $user->id,
                'Question' => $card['question'],
                'Answer' => $card['answer'],
                'SubjectID' => $request->SubjectID
            ]);
        }

        return $this->successResponse('تم حفظ الفلاش كاردز بنجاح!', $savedCards);
    }

    public function storeLink(Request $request) {
        $request->validate([
            'ResourceName' => 'required|string|max:255',
            'url' => 'required|url',
            'SubjectID' => 'required|exists:subjects,id'
        ]);

        $resource = Material::create([
            'ResourceName' => $request->ResourceName,
            'FilePath' => $request->url,
            'FileType' => 'link',
            'file_size' => 0,
            'UserID' => Auth::id(),
            'SubjectID' => $request->SubjectID
        ]);

        return $this->successResponse('تم حفظ الرابط بنجاح', $resource, 201);
    }

    // ============================================================
    // 🆕 توليد بطاقات تعليمية عبر DeepSeek
    // ============================================================
    public function generateFlashcardsFromFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,pptx|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $text = '';
        if ($extension === 'pdf') {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->path());
            $text = $pdf->getText();
        } elseif ($extension === 'docx') {
            $phpWord = WordIOFactory::load($file->path());
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) $text .= $element->getText() . " ";
                }
            }
        } elseif ($extension === 'pptx') {
            $phpPresentation = PPTIOFactory::load($file->path());
            foreach ($phpPresentation->getAllSlides() as $slide) {
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) $text .= $shape->getPlainText() . " ";
                }
            }
        }

        if (empty(trim($text))) {
            return $this->errorResponse('لم نتمكن من استخراج نص من الملف. تأكد إنه ملف نصي واضح.', null, 400);
        }

        $shortText = mb_substr($text, 0, 6000);
        $apiKey = config('services.deepseek.key');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-chat',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => 'أنت معلم خبير. اقرأ النص وولّد بطاقات تعليمية (سؤال وجواب) تغطي أهم المعلومات. أرجع النتيجة فقط بصيغة JSON بهذا الشكل: {"flashcards": [{"question": "...", "answer": "..."}]}'],
                        ['role' => 'user', 'content' => $shortText],
                    ],
                ]);

            if ($response->successful()) {
                $aiResult = json_decode($response->json('choices.0.message.content'), true);
                return $this->successResponse('تم توليد البطاقات بنجاح', [
                    'flashcards' => $aiResult['flashcards'] ?? []
                ], 200);
            }

            return $this->errorResponse('فشل الاتصال بـ DeepSeek', $response->json(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('خطأ أثناء المعالجة', ['error' => $e->getMessage()], 500);
        }
    }

}

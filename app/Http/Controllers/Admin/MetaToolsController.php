<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Meta\WhatsAppService;
use App\Services\Meta\ConversationService;
use App\Services\Meta\PixelHelperService;
use App\Services\Meta\AbTestService;
use App\Services\Meta\InstagramService;
use App\Services\Meta\EnhancedMatchingService;
use App\Services\Audience\AudienceUploadService;
use App\Models\MarketingSetting;
use Illuminate\Http\Request;

class MetaToolsController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsapp,
        private ConversationService $conversations,
        private PixelHelperService $pixelHelper,
        private AbTestService $abTest,
        private InstagramService $instagram,
        private EnhancedMatchingService $enhancedMatching,
        private AudienceUploadService $audienceUpload,
    ) {}

    public function whatsappDashboard()
    {
        $phoneInfo = $this->whatsapp->getPhoneNumberInfo();
        $businessProfile = $this->whatsapp->getBusinessProfile();

        return view('admin.meta-tools.whatsapp', compact('phoneInfo', 'businessProfile'));
    }

    public function whatsappSend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string|max:1000',
            'template' => 'nullable|string',
        ]);

        if ($request->template) {
            $result = $this->whatsapp->sendMessage($request->phone, $request->template);
        } else {
            $result = $this->whatsapp->sendText($request->phone, $request->message);
        }

        return response()->json($result);
    }

    public function whatsappBulkSend(Request $request)
    {
        $request->validate([
            'phones' => 'required|array',
            'template' => 'required|string',
        ]);

        $result = $this->whatsapp->sendBulkMessages($request->phones, $request->template);

        return response()->json($result);
    }

    public function whatsappTest()
    {
        return response()->json($this->whatsapp->testConnection());
    }

    public function conversationsIndex()
    {
        $conversations = $this->conversations->getConversations(50);
        $stats = $this->conversations->getStats();

        return view('admin.meta-tools.conversations', compact('conversations', 'stats'));
    }

    public function conversationsMessages(string $conversationId)
    {
        $messages = $this->conversations->getMessages($conversationId);

        return response()->json($messages);
    }

    public function conversationsReply(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $result = $this->conversations->sendMessage($request->recipient_id, $request->message);

        return response()->json($result);
    }

    public function conversationsUnread()
    {
        return response()->json(['unread' => $this->conversations->getUnreadCount()]);
    }

    public function pixelHelperIndex()
    {
        $verification = $this->pixelHelper->verifyPixel();
        $browserVsCapi = $this->pixelHelper->checkBrowserVsCapi();
        $health = $this->pixelHelper->getHealthReport();

        return view('admin.meta-tools.pixel-helper', compact('verification', 'browserVsCapi', 'health'));
    }

    public function pixelHelperVerify()
    {
        return response()->json($this->pixelHelper->verifyPixel());
    }

    public function pixelHelperHealth()
    {
        return response()->json($this->pixelHelper->getHealthReport());
    }

    public function abTestsIndex()
    {
        $tests = $this->abTest->getActiveTests();
        $completedTests = \DB::table('ab_tests')->where('status', 'completed')->orderByDesc('ended_at')->limit(20)->get();

        return view('admin.meta-tools.ab-tests', compact('tests', 'completedTests'));
    }

    public function abTestsCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:meta,google',
            'test_type' => 'required|in:headline,primary_text,description,image,cta',
            'variant_a_id' => 'nullable|integer',
            'variant_b_id' => 'nullable|integer',
        ]);

        $testId = $this->abTest->createTest($request->all());

        return redirect()->route('admin.ab-tests.index')
            ->with('success', 'تم إنشاء اختبار A/B بنجاح');
    }

    public function abTestsAnalyze(int $testId)
    {
        return response()->json($this->abTest->analyzeTest($testId));
    }

    public function abTestsDeclareWinner(Request $request, int $testId)
    {
        $request->validate(['winner' => 'required|in:a,b']);

        $success = $this->abTest->declareWinner($testId, $request->winner);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تم تحديد الفائز' : 'لا يمكن التحديد بعد',
        ]);
    }

    public function instagramDashboard()
    {
        $dashboard = $this->instagram->getDashboard();

        return view('admin.meta-tools.instagram', compact('dashboard'));
    }

    public function instagramInsights()
    {
        $insights = $this->instagram->getInsights('day', 30);

        return response()->json($insights);
    }

    public function instagramTopPosts()
    {
        $posts = $this->instagram->getTopPosts(10);

        return response()->json($posts);
    }

    public function audienceUploadIndex()
    {
        $audiences = \App\Models\CustomAudience::where('platform', 'meta')->get();

        return view('admin.meta-tools.audience-upload', compact('audiences'));
    }

    public function audienceUploadCsv(Request $request)
    {
        $request->validate([
            'audience_id' => 'required|integer',
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->storeAs('temp', 'audience_' . time() . '.csv');

        $result = $this->audienceUpload->uploadCsvToCustomAudience(
            $request->audience_id,
            storage_path("app/{$path}")
        );

        return response()->json($result);
    }

    public function audienceUploadPhones(Request $request)
    {
        $request->validate([
            'audience_id' => 'required|integer',
            'phones' => 'required|array',
        ]);

        $result = $this->audienceUpload->uploadPhoneNumbersToCustomAudience(
            $request->audience_id,
            $request->phones
        );

        return response()->json($result);
    }

    public function audienceUploadEmails(Request $request)
    {
        $request->validate([
            'audience_id' => 'required|integer',
            'emails' => 'required|array',
        ]);

        $result = $this->audienceUpload->uploadEmailsToCustomAudience(
            $request->audience_id,
            $request->emails
        );

        return response()->json($result);
    }

    public function audienceTemplate()
    {
        return response($this->audienceUpload->getCsvTemplate())
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audience_template.csv"');
    }

    public function enhancedMatchingTest(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        $userData = $this->enhancedMatching->buildEnhancedUserData(null, $request->all());
        $matchRate = $this->enhancedMatching->calculateMatchRate($userData);

        return response()->json([
            'user_data' => $userData,
            'match_rate' => $matchRate,
        ]);
    }
}

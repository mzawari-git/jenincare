<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('frontend.contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($data);

        return redirect()->route('contact')->with('success', __('Your message has been sent successfully!'));
    }

    public function newsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $existing = NewsletterSubscriber::where('email', $request->email)->first();

        if ($existing) {
            if (!$existing->is_active) {
                $existing->update(['is_active' => true, 'subscribed_at' => now()]);
            }
            return response()->json([
                'success' => true,
                'message' => 'البريد الإلكتروني مسجل مسبقاً!'
            ]);
        }

        NewsletterSubscriber::create([
            'email' => $request->email,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الاشتراك بنجاح! شكراً لانضمامك.'
        ]);
    }
}

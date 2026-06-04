<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function showAdminLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الإدارة.');
            }

            $request->session()->regenerate();

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('home'));
        }

        return back()->withInput($request->only('email'))->with('error', 'بيانات الدخول غير صحيحة');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الإدارة.');
            }

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->with('error', 'ليس لديك صلاحية الوصول للوحة التحكم.');
            }

            $request->session()->regenerate();

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->route('admin.dashboard');
        }

        return back()->withInput($request->only('email'))->with('error', 'بيانات الدخول غير صحيحة');
    }

    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'security_question' => 'required|string',
            'security_answer' => 'required|string|min:2|max:255'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'security_question' => $request->security_question,
            'security_answer' => Hash::make(trim(mb_strtolower($request->security_answer, 'UTF-8'))),
            'role' => 'customer',
            'tenant_id' => 1
        ]);

        Auth::login($user, true); // Auto-login with remember-me
        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput()->with('error', 'البريد الإلكتروني غير مسجل لدينا.');
        }

        if (!$user->security_question) {
            return back()->withInput()->with('error', 'هذا الحساب لا يحتوي على سؤال أمان. يرجى التواصل مع الإدارة.');
        }

        session(['reset_email' => $user->email]);
        session(['reset_question' => $user->security_question]);

        return redirect()->route('password.security-question');
    }

    public function showSecurityQuestion()
    {
        $email = session('reset_email');
        $question = session('reset_question');

        if (!$email || !$question) {
            return redirect()->route('password.request')->with('error', 'يرجى إدخال بريدك الإلكتروني أولاً.');
        }

        return view('frontend.auth.security-question', compact('email', 'question'));
    }

    public function checkSecurityAnswer(Request $request)
    {
        $request->validate(['security_answer' => 'required|string']);

        $email = session('reset_email');
        $question = session('reset_question');

        if (!$email || !$question) {
            return redirect()->route('password.request')->with('error', 'انتهت الجلسة. يرجى المحاولة مرة أخرى.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'حدث خطأ. يرجى المحاولة مرة أخرى.');
        }

        if (!Hash::check(trim(mb_strtolower($request->security_answer, 'UTF-8')), $user->security_answer)) {
            return back()->with('error', 'الإجابة غير صحيحة. حاول مرة أخرى.');
        }

        $token = Str::random(64);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        session(['reset_token' => $token, 'reset_answer_verified' => true]);

        return redirect()->route('password.reset');
    }

    public function showResetForm()
    {
        $email = session('reset_email');

        if (!session('reset_answer_verified') || !$email) {
            return redirect()->route('password.request')->with('error', 'انتهت الجلسة. يرجى المحاولة مرة أخرى.');
        }

        return view('frontend.auth.reset-password', compact('email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $email = session('reset_email');
        $token = session('reset_token');

        if (!session('reset_answer_verified') || !$email) {
            return redirect()->route('password.request')->with('error', 'انتهت الجلسة. يرجى المحاولة مرة أخرى.');
        }

        $record = \DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return redirect()->route('password.request')->with('error', 'انتهت صلاحية الرابط. يرجى المحاولة مرة أخرى.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'حدث خطأ. يرجى المحاولة مرة أخرى.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        \DB::table('password_reset_tokens')->where('email', $email)->delete();

        session()->forget(['reset_email', 'reset_question', 'reset_token', 'reset_answer_verified']);

        return redirect()->route('login')->with('success', 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.');
    }

    public function showSecurityQuestionSetup()
    {
        $user = Auth::user();
        return view('frontend.account.security-question-setup', compact('user'));
    }

    public function updateSecurityQuestion(Request $request)
    {
        $request->validate([
            'security_question' => 'required|string',
            'security_answer' => 'required|string|min:2|max:255',
            'current_password' => 'required|string'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'كلمة المرور الحالية غير صحيحة.');
        }

        $user->update([
            'security_question' => $request->security_question,
            'security_answer' => Hash::make(trim(mb_strtolower($request->security_answer, 'UTF-8')))
        ]);

        return back()->with('success', 'تم تحديث سؤال الأمان بنجاح.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}

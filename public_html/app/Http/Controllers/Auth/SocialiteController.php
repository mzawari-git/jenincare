<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    private function redirectUrl(Request $request, string $provider): string
    {
        $base = $request->schemeAndHttpHost() . '/' . ltrim($request->getBaseUrl(), '/');
        return rtrim($base, '/') . '/auth/' . $provider . '/callback';
    }

    private function loginUser(User $user, Request $request): void
    {
        Auth::login($user);
        $request->session()->regenerate();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }

    public function redirect(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'مزود تسجيل الدخول غير مدعوم.');
        }

        session(['social_login_previous' => url()->previous()]);

        return Socialite::driver($provider)
            ->redirectUrl($this->redirectUrl($request, $provider))
            ->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('error', 'مزود تسجيل الدخول غير مدعوم.');
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl($this->redirectUrl($request, $provider))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'فشل تسجيل الدخول عبر ' . $provider . '. يرجى المحاولة مرة أخرى.');
        }

        // 1. Existing social account → login directly
        $existingAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingAccount) {
            $user = $existingAccount->user;
            if (!$user->is_active) {
                return redirect()->route('login')->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الإدارة.');
            }
            $this->loginUser($user, $request);
            return redirect()->intended(route('home'));
        }

        // 2. Existing user with same email → link social account & login
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            if (!$existingUser->is_active) {
                return redirect()->route('login')->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الإدارة.');
            }

            $existingUser->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);

            $this->loginUser($existingUser, $request);
            return redirect()->intended(route('home'));
        }

        // 3. New user → create account & login
        $email = $socialUser->getEmail();
        if (empty($email)) {
            return redirect()->route('login')->with('error', 'لم نتمكن من الحصول على بريدك الإلكتروني. يرجى المحاولة بطريقة أخرى.');
        }

        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $provider . '_user',
            'email' => $email,
            'password' => bcrypt(Str::random(32)),
            'role' => 'customer',
            'tenant_id' => 1,
            'is_active' => true,
        ]);

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        $this->loginUser($user, $request);
        return redirect()->intended(route('home'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Email tidak terdaftar.',
        ]);

        $email = $validated['email'];
        $code = random_int(100000, 999999);

        // Simpan hashed code ke password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Kirim email OTP sederhana
        try {
            Mail::raw("Kode reset password Anda: {$code}\nBerlaku 10 menit. Jangan bagikan ke siapapun.", function ($message) use ($email) {
                $message->to($email)->subject('Kode Reset Password - Cizy Nails');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email. Coba lagi.']);
        }

        return redirect()->route('password.reset', ['email' => $email])
            ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->get('email')
        ]);
    }

    public function resetWithOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.exists' => 'Email tidak terdaftar.',
            'otp.digits' => 'OTP harus 6 digit.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();
        if (!$record) {
            return back()->withErrors(['otp' => 'Kode OTP tidak ditemukan.'])->withInput();
        }

        // Expiry 10 minutes
        if (now()->diffInMinutes($record->created_at) > 10) {
            return back()->withErrors(['otp' => 'Kode OTP kadaluarsa, silakan minta lagi.'])->withInput();
        }

        if (!Hash::check($validated['otp'], $record->token)) {
            return back()->withErrors(['otp' => 'Kode OTP salah.'])->withInput();
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Hapus token setelah sukses
        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        // Auto login user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Password berhasil direset. Anda sudah login.');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role' => 'customer',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ], 201);
        }

        Auth::login($user);
        
        // Check if there's a booking intent from the landing page
        if ($request->session()->has('booking_intent')) {
            $intent = $request->session()->get('booking_intent');
            $request->session()->forget('booking_intent');
            return redirect()->route('booking.form')->with([
                'success' => 'Registration successful!',
                'booking_intent' => $intent
            ]);
        }
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Registration successful!');
        } elseif ($user->role === 'nail_artist') {
            return redirect()->route('nail-artist.dashboard')->with('success', 'Registration successful!');
        }
        
        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    public function login(Request $request)
    {
        // Izinkan login pakai email ATAU nomor HP
        $request->merge([
            'login' => $request->input('login') ?? $request->input('email'), // fallback agar API lama tetap jalan
        ]);

        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau nomor HP harus diisi.',
        ]);

        $loginInput = $validated['login'];
        $phoneDigits = preg_replace('/\D+/', '', $loginInput);

        $user = User::where(function ($q) use ($loginInput, $phoneDigits) {
                $q->where('email', $loginInput)
                  ->orWhere('phone', $loginInput);
                // Cocokkan nomor HP tanpa karakter non-digit (spasi, +, -)
                if ($phoneDigits) {
                    $q->orWhereRaw("REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(phone, '[^0-9]', ''), ' +', ''), ' +', '') = ?", [$phoneDigits]);
                }
            })
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/HP atau password salah.'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]);
        }

        Auth::login($user);
        
        // Check if there's a booking intent from the landing page
        if ($request->session()->has('booking_intent')) {
            $intent = $request->session()->get('booking_intent');
            $request->session()->forget('booking_intent');
            return redirect()->route('booking.form')->with([
                'success' => 'Login successful!',
                'booking_intent' => $intent
            ]);
        }
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
        } elseif ($user->role === 'nail_artist') {
            return redirect()->route('nail-artist.dashboard')->with('success', 'Login successful!');
        }
        
        return redirect()->route('dashboard')->with('success', 'Login successful!');
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing')->with('success', 'Logged out successfully!');
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}

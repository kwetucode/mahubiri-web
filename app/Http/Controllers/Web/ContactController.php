<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $key = 'contact:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors([
                'message' => __('Too many attempts. Please try again later.'),
            ]);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Mail::send('emails.contact', [
            'contactName' => $validated['name'],
            'contactEmail' => $validated['email'],
            'contactSubject' => $validated['subject'],
            'contactMessage' => $validated['message'],
        ], function ($mail) use ($validated) {
            $mail->to('support@mahubiri.tech')
                ->replyTo($validated['email'], $validated['name'])
                ->subject("[Mahubiri Contact] {$validated['subject']}");
        });

        return back();
    }
}

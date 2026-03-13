<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user()->load('role');

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->dashboardRoute($user) . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->dashboardRoute($user) . '?verified=1');
    }

    /**
     * Resolve the correct dashboard URL based on the user's role.
     */
    private function dashboardRoute($user): string
    {
        $roleName = $user->role?->name ?? 'user';

        return match ($roleName) {
            'admin'   => route('admin.dashboard',   absolute: false),
            'manager' => route('manager.dashboard', absolute: false),
            'agent'   => route('agent.dashboard',   absolute: false),
            default   => route('user.dashboard',    absolute: false),
        };
    }
}

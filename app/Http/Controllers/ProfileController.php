<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\OpeningBalanceService;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function openingBalance(Request $request): RedirectResponse
    {
        $companyId = session('active_company_id');
        if (!$companyId) {
            return Redirect::route('profile.edit')->with('status', 'Please select an active company before managing opening balance.');
        }

        $activePeriod = session('active_period') ?: now()->format('Y-m');
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $activePeriod)->startOfMonth();
        } catch (\Throwable $e) {
            $currentMonth = now()->startOfMonth();
        }

        OpeningBalanceService::recalcCurrentMonthFromPreviousMonth((int) $companyId, $currentMonth);

        $monthLabel = $currentMonth->isoFormat('MMMM YYYY');
        return Redirect::route('profile.edit')->with('status', "Opening balance for {$monthLabel} has been generated from the previous month.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

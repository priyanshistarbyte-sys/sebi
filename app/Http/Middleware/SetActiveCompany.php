<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;

class SetActiveCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $activeId = (int) $request->session()->get('active_company_id', 0);

        if ($user->is_admin) {
            // pick the first company if none/invalid
            if (!$activeId || !Company::whereKey($activeId)->exists()) {
                $first = Company::orderBy('name')->value('id');
                if ($first) {
                    $request->session()->put('active_company_id', $first);
                } else {
                    // no companies yet – send admin to create one
                    return redirect()->route('admin.companies.create')
                        ->with('status', 'Create a company first.');
                }
            }
            return $next($request);
        }

        // Non-admin: keep your existing “assigned company” logic
        $assignedIds = $user->companies()->pluck('companies.id')->toArray();
        if (empty($assignedIds)) abort(403, 'No company assigned.');
        if (!$activeId || !in_array($activeId, $assignedIds, true)) {
            $request->session()->put('active_company_id', $assignedIds[0]);
        }

        return $next($request);
    }
}

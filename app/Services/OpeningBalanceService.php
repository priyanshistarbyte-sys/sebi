<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OpeningBalanceService
{
    /** Ensure "Opening Balance" category exists for the given type. */
    protected static function ensureObCategory(int $companyId, string $type): Category
    {
        return Category::firstOrCreate(
            ['company_id' => $companyId, 'type' => $type, 'name' => 'Opening Balance'],
            []
        );
    }

    /** Pick the company's default Account (fallback to first Account; last resort: create one). */
    protected static function defaultAccount(int $companyId): Category
    {
        $acc = Category::where('company_id', $companyId)
            ->where('type', 'Account')
            ->where('is_default', true)
            ->first();

        if (!$acc) {
            $acc = Category::where('company_id', $companyId)
                ->where('type', 'Account')
                ->orderBy('name')
                ->first();
        }

        if (!$acc) {
            // create a minimal account if company has none
            $acc = Category::create([
                'company_id' => $companyId,
                'type'       => 'Account',
                'name'       => 'Primary Account',
                'is_default' => true,
            ]);
        }

        return $acc;
    }

    /**
     * Recalc next month's single Opening Balance for the whole company.
     * Example: pass 2025-09-01 -> writes one OB on 2025-10-01.
     */
    public static function recalcCompanyNextMonthFromMonth(int $companyId, Carbon $month, int $account_id): void
    {
        $month = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();
        $next  = $month->copy()->addMonth()->startOfMonth();

        // Company closing balance up to end of month
        $closing = (float) Transaction::where('company_id', $companyId)
            ->where('account_id', $account_id)
            ->whereDate('date', '>=', $month->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->select(DB::raw("SUM(CASE WHEN type='Income' THEN amount ELSE -amount END) AS bal"))
            ->value('bal');

        // Remove any existing company OB at next month (either Income or Expense)
        Transaction::where('company_id', $companyId)
            ->whereDate('date', $next->toDateString())
            ->where('description', 'Opening Balance')
            ->where('account_id', $account_id)
            ->delete();

        // If zero → nothing to post
        if (abs($closing) < 0.00001) {
            return;
        }

        $account = self::defaultAccount($companyId);
		$cat = self::ensureObCategory($companyId, 'Income/Expense');
        $base = [
            'company_id'  => $companyId,
          	'category_id' => $cat->id,
            'account_id'  => $account_id,
            'date'        => $next->toDateString(),
            'description' => 'Opening Balance',
        ];

        
        if ($closing > 0) {
            Transaction::create($base + [
                'type'        => 'Income',
                'amount'      => $closing,
            ]);
        } else {
            Transaction::create($base + [
                'type'        => 'Expense',
                'amount'      => abs($closing),
            ]);
        }
    }

    /**
     * Ensure this month's Opening Balance is populated from the prior month's closing balances.
     * Example: pass 2025-10-01 -> writes OB entries on 2025-10-01 based on September 2025.
     */
    public static function recalcCurrentMonthFromPreviousMonth(int $companyId, Carbon $month): void
    {
        $month = $month->copy()->startOfMonth();
        $previous = $month->copy()->subMonth()->startOfMonth();

        $accounts = Category::query()
            ->where('company_id', $companyId)
            ->where('type', 'Account')
            ->pluck('id');

        if ($accounts->isEmpty()) {
            $account = self::defaultAccount($companyId);
            self::recalcCompanyNextMonthFromMonth($companyId, $previous, (int)$account->id);
            return;
        }

        foreach ($accounts as $accountId) {
            self::recalcCompanyNextMonthFromMonth($companyId, $previous, (int)$accountId);
        }
    }
}

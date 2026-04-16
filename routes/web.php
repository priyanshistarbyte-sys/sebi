<?php

use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\OpeningBalanceService;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\DashboardFileController;
use App\Http\Controllers\Admin\TransactionExportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? to_route('admin.dashboard')
        : to_route('login');
});

// Route::get('/', fn () => redirect()->route('login'));

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/opening-balance', [ProfileController::class, 'openingBalance'])->name('profile.opening-balance');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('companies', CompanyController::class)->except('show');
    });

   
    Route::middleware('company')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('transactions', TransactionController::class)->only(['index','store','update','destroy']);

        Route::get('/dashboard/category/{category}/transactions',   [DashboardController::class, 'categoryTransactions'])->name('dashboard.category.transactions');
        Route::get('/dashboard/account/{account}/transactions',     [DashboardController::class, 'accountTransactions'])->name('dashboard.account.transactions');
        Route::post('/dashboard/files',                             [DashboardFileController::class, 'store'])->name('dashboard.files.store');
        Route::patch('/dashboard/files/{file}',                     [DashboardFileController::class, 'update'])->name('dashboard.files.update');
        Route::delete('/dashboard/files/{file}',                    [DashboardFileController::class, 'destroy'])->name('dashboard.files.destroy');
        Route::get('/dashboard/files/{file}/preview',               [DashboardFileController::class, 'preview'])->name('dashboard.files.preview');
        Route::get('/dashboard/category/{category}/transactions/export',  [TransactionExportController::class, 'export'])->name('dashboard.category.transactions.export');
        Route::get('/dashboard/gst-transactions',                          [TransactionExportController::class, 'gstExport'])->name('dashboard.gst.transactions.export');
        Route::get('/dashboard/gst-transactions/modal',                    [DashboardController::class, 'gstTransactionsModal'])->name('dashboard.gst.transactions.modal');
        Route::get('/dashboard/tds-transactions',                          [TransactionExportController::class, 'tdsExport'])->name('dashboard.tds.transactions.export');
        Route::get('/dashboard/tds-transactions/modal',                    [DashboardController::class, 'tdsTransactionsModal'])->name('dashboard.tds.transactions.modal');
    });
    Route::post('/company/switch', function (Request $request) {
        $val = (string) $request->input('company_id', '');

        if ($request->user()->is_admin) {
            if ($val === 'all') {
                session()->forget('active_company_id');
                return back()->with('status','Viewing all companies');
            }
            $company = Company::findOrFail((int)$val);
            session(['active_company_id' => $company->id]);
            return back()->with('status','Switched to '.$company->name);
        }

        $company = Company::findOrFail((int)$val);
        abort_unless($request->user()->companies()->whereKey($company->id)->exists(), 403);
        session(['active_company_id' => $company->id]);
        return back()->with('status','Switched to '.$company->name);
    })->name('company.switch');

    Route::middleware(['auth'])->post('/period/switch', function (Request $request) {
        // Accept native `<input type="month">` (YYYY-MM) or custom "MM/YYYY"
        $val = (string) $request->input('period', '');

        if (preg_match('/^\d{4}-\d{2}$/', $val)) {
            // Native month input (YYYY-MM)
            session(['active_period' => $val]);               // e.g. "2025-09"
            return back()->with('status', 'Period updated');
        }

        if (preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $val)) {
            // Custom MM/YYYY -> convert to YYYY-MM
            [$m, $y] = explode('/', $val);
            session(['active_period' => sprintf('%s-%s', $y, $m)]);
            return back()->with('status', 'Period updated');
        }

        return back()->with('status', 'Invalid period format');
    })->name('period.switch');
    Route::post('admin/cash-transfer', [TransactionController::class, 'cashTransfer'])->name('admin.cash-transfer.store');
});

require __DIR__.'/auth.php';

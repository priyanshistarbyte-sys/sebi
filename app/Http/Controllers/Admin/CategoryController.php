<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private array $types = ['Income/Expense','Account'];
    private function activeCompanyId(): int {
        return (int) session('active_company_id');
    }

    private function assignableCompanies($user)
    {
        return $user->is_admin
            ? Company::orderBy('name')->get()
            : $user->companies()->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $types = ['Income/Expense','Account'];
        $type  = $request->query('type');
        if ($type && !in_array($type, $types, true)) $type = null;

        $sortable = ['name', 'type', 'on_dashboard', 'company_id', 'sort_order', 'created_at'];
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        if (!in_array($sort, $sortable)) { $sort = 'name'; }
        if (!in_array(strtolower($dir), ['asc','desc'])) { $dir = 'asc'; }

        $user = $request->user();

        // Companies available to select in the filter
        $companies = $user->is_admin
            ? Company::orderBy('name')->get()
            : $user->companies()->orderBy('name')->get();

        // Read requested company filter. Admins may pass "all".
        $companyParam = $request->query('company_id'); // 'all' | numeric | null
        $query = Category::query()->with('company');
        
        $activeId = 0;
        if (auth()->user()->is_admin) {
            $activeId = session('active_company_id');      // null => All
            if ($activeId) { $query->where('company_id', (int)$activeId); }
        } else {
            $activeId = (int) session('active_company_id'); // guaranteed by middleware
            $query->where('company_id', $activeId);
        }
        // Lock the company selector to that one
        $companyParam = $activeId;

        $categories = $query
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy($sort, $dir)->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories'        => $categories,
            'types'             => $types,
            'currentType'       => $type,
            'companies'         => $companies,
            'currentCompanyId'  => $companyParam ?: ($user->is_admin ? 'all' : (int) session('active_company_id')),
            'isAdmin'           => (bool) $user->is_admin,
            'sort'              => $sort,
            'dir'               => $dir
        ]);
    }

    public function create()
    {
        $cid = (int) (session('active_company_id') ?? 0);
        return view('admin.categories.create', [
            'types'           => $this->types,
            'activeCompany'   => $cid ? Company::find($cid) : null,
            'canCreate'       => (bool) $cid,   // disable form if no company selected
        ]);
    }

    public function store(Request $request)
    {
        $companyId = (int) (session('active_company_id') ?? 0);
        if (!$companyId) {
            return back()->withErrors(['company' => 'Select a company in the header first.'])
                        ->withInput();
        }

        $data = $request->validate([
            'type' => ['required', Rule::in($this->types)],
            'name' => [
                'required','string','max:100',
                Rule::unique('categories')->where(fn($q)=>$q
                    ->where('company_id', $companyId)
                    ->where('type', $request->type)),
            ],
            'is_default'  => ['nullable','boolean'],
            'on_dashboard'  => ['nullable','boolean'],
            'dashboard_period' => [
                'nullable',
                Rule::in(['monthly','all_time']),
                Rule::requiredIf(fn () => $request->boolean('on_dashboard')),
            ],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['on_dashboard'] = $request->boolean('on_dashboard');

        // If not an Account, it cannot be default
        if ($data['type'] !== 'Account') {
            $data['is_default'] = false;
        }

        if (!$data['on_dashboard']) {
            $data['dashboard_period'] = null;
        }

        Category::create([
            'company_id'        => $companyId,   // ← forced from session
            'type'              => $data['type'],
            'name'              => $data['name'],
            'is_default'        => $data['is_default'],
            'on_dashboard'      => $data['on_dashboard'],
            'dashboard_period'  => $data['dashboard_period'],
        ]);

        return redirect()->route('admin.categories.index')->with('status','Category created');
    }

    public function edit(Category $category)
    {
        if ($category->company_id !== $this->activeCompanyId() && !auth()->user()->is_admin) {
            abort(403);
        }
        return view('admin.categories.edit', ['category'=>$category, 'types'=>$this->types]);
    }

    public function update(Request $request, Category $category)
    {
        // keep category within its original company
        $companyId = $category->company_id;

        $data = $request->validate([
            'type' => ['required', Rule::in($this->types)],
            'name' => [
                'required','string','max:100',
                Rule::unique('categories')->where(fn($q)=>$q
                    ->where('company_id', $companyId)
                    ->where('type', $request->type)
                )->ignore($category->id),
            ],
            'is_default'  => ['nullable','boolean'],
            'on_dashboard'  => ['nullable','boolean'],
            'dashboard_period' => [
                'nullable',
                Rule::in(['monthly','all_time']),
                Rule::requiredIf(fn () => $request->boolean('on_dashboard')),
            ],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['on_dashboard'] = $request->boolean('on_dashboard');

        // If not an Account, it cannot be default
        if ($data['type'] !== 'Account') {
            $data['is_default'] = false;
        }

        if (!$data['on_dashboard']) {
            $data['dashboard_period'] = null;
        }

        $category->update($data);

        // if ($category->type === 'Account' && $category->is_default) {
        //     Category::where('company_id',$category->company_id)
        //         ->where('type','Account')
        //         ->where('id','<>',$category->id)
        //         ->update(['is_default'=>false]);
        // }

        return redirect()->route('admin.categories.index')->with('status','Category updated');
    }

    public function destroy(Category $category)
    {
        if ($category->company_id !== $this->activeCompanyId() && !auth()->user()->is_admin) {
            abort(403);
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('status','Category deleted');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('name')->paginate(15);
        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:150','unique:companies,name'],
            'currency_symbol' => ['required', Rule::in(['₹','$','€','£'])],
        ]);
        Company::create($data);
        return redirect()->route('admin.companies.index')->with('status','Company created');
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:150', Rule::unique('companies','name')->ignore($company->id)],
            'currency_symbol' => ['required', Rule::in(['₹','$','€','£'])],
        ]);
        $company->update($data);
        return redirect()->route('admin.companies.index')->with('status','Company updated');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')->with('status','Company deleted');
    }

    public function show(Company $company)
    {
        return redirect()->route('admin.companies.edit', $company);
    }
}

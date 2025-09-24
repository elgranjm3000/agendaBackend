<?php
namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    
    public function index(Request $request): JsonResponse
    {

        $companies = Company::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($companies);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return $this->successResponse($company, 'Company created successfully', 201);
    }

    public function show(Company $company): JsonResponse
    {
        return $this->successResponse($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());

        return $this->successResponse($company, 'Company updated successfully');
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return $this->successResponse(null, 'Company deleted successfully');
    }
}
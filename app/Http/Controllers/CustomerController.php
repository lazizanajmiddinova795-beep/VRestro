<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerService $customerService, CustomerRepositoryInterface $customerRepository)
    {
        $this->customerService = $customerService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get paginated customer listing with optional search filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $customers = $this->customerRepository->getAllCustomers($filters);

        return response()->json($customers);
    }

    /**
     * Onboard a new customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25', 'unique:customers,phone'],
        ]);

        $customer = $this->customerService->createCustomer($data);

        return response()->json([
            'message' => 'Mijoz muvaffaqiyatli ro\'yxatga olindi.',
            'customer' => $customer
        ], 201);
    }

    /**
     * Update customer details.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25', 'unique:customers,phone,' . $id],
        ]);

        $customer = $this->customerService->updateCustomer($id, $data);

        return response()->json([
            'message' => 'Mijoz ma\'lumotlari yangilandi.',
            'customer' => $customer
        ]);
    }

    /**
     * Adjust customer balance manually (Admin/Cashier privilege).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function adjust(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'bonus_balance' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = $this->customerService->adjustBalance($id, (float)$data['bonus_balance']);

        return response()->json([
            'message' => 'Mijoz bonus balansi muvaffaqiyatli sozlandi.',
            'customer' => $customer
        ]);
    }

    /**
     * Get aggregate statistics for CRM dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analytics(Request $request): JsonResponse
    {
        $analytics = $this->customerRepository->getCRMAnalytics();

        return response()->json($analytics);
    }

    /**
     * Delete customer profile completely.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->customerRepository->delete($id);

        return response()->json([
            'message' => 'Mijoz tizimdan muvaffaqiyatli o\'chirildi.'
        ]);
    }
}

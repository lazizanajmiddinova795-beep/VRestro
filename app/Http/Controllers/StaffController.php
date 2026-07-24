<?php

namespace App\Http\Controllers;

use App\Services\StaffService;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected StaffService $staffService;
    protected UserRepositoryInterface $userRepository;

    public function __construct(StaffService $staffService, UserRepositoryInterface $userRepository)
    {
        $this->staffService = $staffService;
        $this->userRepository = $userRepository;
    }

    /**
     * Get paginated listing of staff.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'role' => ['nullable', 'string', 'in:Admin,Chef,Waiter,Cashier'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $staff = $this->userRepository->getAllUsers($filters);

        return response()->json($staff);
    }

    /**
     * Store a newly created staff member.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'login' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\.]+$/', 'unique:users,login'],
            'password' => ['required', 'string', 'min:4', 'max:100'],
            'role' => ['required', 'string', 'in:Admin,Chef,Waiter,Cashier'],
            'shift_hours' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:255'],
        ]);

        $data['name'] = strip_tags($data['name']);
        $data['login'] = strip_tags($data['login']);

        $user = $this->staffService->createStaff($request->user()->id, $data);

        return response()->json([
            'message' => 'Xodim muvaffaqiyatli qo\'shildi.',
            'user' => $user->load('roles')
        ], 201);
    }

    /**
     * Update details of an existing staff member.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $id],
            'login' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\.]+$/', 'unique:users,login,' . $id],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'role' => ['required', 'string', 'in:Admin,Chef,Waiter,Cashier'],
            'shift_hours' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email,' . $id],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:255'],
        ]);

        $data['name'] = strip_tags($data['name']);
        $data['login'] = strip_tags($data['login']);

        $user = $this->staffService->updateStaff($request->user()->id, $id, $data);

        return response()->json([
            'message' => 'Xodim ma\'lumotlari yangilandi.',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Toggle staff status (active / inactive).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $user = $this->staffService->toggleStatus($request->user()->id, $id);

        return response()->json([
            'message' => 'Xodim faollik holati o\'zgartirildi.',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Delete a staff user completely.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $this->staffService->deleteStaff($request->user()->id, $id);

        return response()->json([
            'message' => 'Xodim ro\'yxatdan muvaffaqiyatli o\'chirildi.'
        ]);
    }

    /**
     * Verify that current calling user is Admin.
     *
     * @param Request $request
     * @return void
     */
    protected function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403, 'Ushbu amalni bajarish uchun sizda ruxsat yo\'q.');
        }
    }
}

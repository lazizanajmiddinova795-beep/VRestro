<?php

namespace App\Http\Controllers;

use App\Services\TableService;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected TableService $tableService;
    protected TableRepositoryInterface $tableRepository;

    public function __construct(TableService $tableService, TableRepositoryInterface $tableRepository)
    {
        $this->tableService = $tableService;
        $this->tableRepository = $tableRepository;
    }

    /**
     * Display a listing of restaurant tables.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:empty,occupied,reserved'],
        ]);

        $tables = $this->tableRepository->getAllTables($filters);

        return response()->json($tables);
    }

    /**
     * Store a newly created table.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:tables,table_number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'string', 'in:empty,occupied,reserved'],
            'qr_code_token' => ['nullable', 'string', 'max:100', 'unique:tables,qr_code_token'],
        ]);

        $table = $this->tableService->createTable($data);

        return response()->json([
            'message' => 'Stol muvaffaqiyatli qo\'shildi.',
            'table' => $table
        ], 201);
    }

    /**
     * Update table details.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:tables,table_number,' . $id],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'string', 'in:empty,occupied,reserved'],
            'qr_code_token' => ['nullable', 'string', 'max:100', 'unique:tables,qr_code_token,' . $id],
        ]);

        $table = $this->tableService->updateTable($id, $data);

        return response()->json([
            'message' => 'Stol ma\'lumotlari yangilandi.',
            'table' => $table
        ]);
    }

    /**
     * Update table status only.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:empty,occupied,reserved'],
        ]);

        $table = $this->tableService->changeStatus($id, $data['status']);

        return response()->json([
            'message' => 'Stol holati o\'zgartirildi.',
            'table' => $table
        ]);
    }

    /**
     * Remove the specified table from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->tableService->deleteTable($id);

        return response()->json([
            'message' => 'Stol muvaffaqiyatli o\'chirildi.'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ToolRequest;
use App\Http\Resources\Admin\ToolResource;
use App\Services\Admin\ToolService;

class ToolController extends Controller
{
    protected ToolService $toolService;

    public function __construct(ToolService $toolService)
    {
        $this->toolService = $toolService;
    }

    public function index()
    {
        $tools = $this->toolService->getAllTools(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar alat berhasil diambil',
            'data' => [
                'tools' => ToolResource::collection($tools),
                'pagination' => [
                    'current_page' => $tools->currentPage(),
                    'last_page' => $tools->lastPage(),
                    'per_page' => $tools->perPage(),
                    'total' => $tools->total(),
                ]
            ]
        ], 200);
    }

    public function store(ToolRequest $request)
    {
        $tool = $this->toolService->createTool(
            $request->except('gambar'),
            $request->file('gambar')
        );

        return response()->json([
            'success' => true,
            'message' => 'Alat berhasil ditambahkan',
            'data' => new ToolResource($tool)
        ], 201);
    }

    public function show($id)
    {
        $tool = $this->toolService->getToolById($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail alat',
            'data' => new ToolResource($tool)
        ], 200);
    }

    public function update(ToolRequest $request, $id)
{
    // Debug: lihat data yang masuk
    \Log::info('Update Tool Request Data:', $request->all());

    $tool = $this->toolService->getToolById($id);

    if (!$tool) {
        return response()->json([
            'success' => false,
            'message' => 'Alat tidak ditemukan'
        ], 404);
    }

    $tool = $this->toolService->updateTool(
        $tool,
        $request->except('gambar'),
        $request->file('gambar')
    );

    return response()->json([
        'success' => true,
        'message' => 'Alat berhasil diperbarui',
        'data' => new ToolResource($tool)
    ], 200);
}

    public function destroy($id)
    {
        $tool = $this->toolService->getToolById($id);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        $this->toolService->deleteTool($tool);

        return response()->json([
            'success' => true,
            'message' => 'Alat berhasil dihapus'
        ], 200);
    }

    public function select()
    {
        $tools = $this->toolService->getAllToolsForSelect();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alat untuk select',
            'data' => $tools
        ], 200);
    }
}

<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait ApiResponse
{
    protected function successResponse($data, string $message = 'Operation successful', ?int $departmentId = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String()
            ]
        ];

        if ($departmentId) {
            $department = DB::table('departments')->find($departmentId);
            $response['meta']['department_id'] = $departmentId;
            $response['meta']['department_name'] = $department->name ?? null;
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse(string $message, array $errors = [], ?int $departmentId = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toIso8601String()
            ]
        ];

        if ($departmentId) {
            $department = DB::table('departments')->find($departmentId);
            $response['meta']['department_id'] = $departmentId;
            $response['meta']['department_name'] = $department->name ?? null;
        }

        return response()->json($response, $statusCode);
    }

    protected function paginatedResponse($paginator, string $message = 'Data retrieved successfully', ?int $departmentId = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'timestamp' => now()->toIso8601String()
            ]
        ];

        if ($departmentId) {
            $department = DB::table('departments')->find($departmentId);
            $response['meta']['department_id'] = $departmentId;
            $response['meta']['department_name'] = $department->name ?? null;
        }

        return response()->json($response, 200);
    }
}

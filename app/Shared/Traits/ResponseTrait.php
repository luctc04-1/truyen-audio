<?php

namespace App\Shared\Traits;

trait ResponseTrait
{
    /**
     * Send a paginated JSON response
     *
     * @param  \Illuminate\Pagination\Paginator  $data
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ]);
    }

    /**
     * Send a JSON response with timestamp
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function successWithTimestamp($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }
}

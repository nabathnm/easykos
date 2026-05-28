<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Helper to call internal API using HTTP Client with JWT Token of currently logged in user.
     */
    protected function apiCall($method, $endpoint, $data = [])
    {
        $http = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept' => 'application/json',
        ]);

        if (auth()->check()) {
            // Generate a JWT token for the currently authenticated web user
            $token = auth('api')->login(auth()->user());
            $http = $http->withToken($token);
        }

        $url = url('/api/' . ltrim($endpoint, '/'));
        
        $response = $method === 'GET' 
            ? $http->get($url, $data) 
            : $http->$method($url, $data);

        return $response;
    }

    protected function paginateApiResponse($responseData, $request)
    {
        if (!isset($responseData['data']['data'])) {
            return collect(); // Fallback if not paginated format
        }
        
        $items = json_decode(json_encode($responseData['data']['data']));
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $responseData['data']['total'] ?? 0,
            $responseData['data']['per_page'] ?? 10,
            $responseData['data']['current_page'] ?? 1,
            ['path' => url($request->path()), 'query' => $request->query()]
        );
    }
}

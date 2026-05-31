<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Helper to call internal API using HTTP Client with JWT Token of currently logged in user.
     */
    protected function apiCall($method, $endpoint, $data = [])
    {
        $uri = '/api/' . ltrim($endpoint, '/');
        
        // Prepare request parameters/query/body
        $request = \Illuminate\Http\Request::create($uri, $method, $data);
        $request->headers->set('Accept', 'application/json');

        if (auth()->check()) {
            $token = auth('api')->login(auth()->user());
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        $originalRequest = request();
        
        // Temporarily swap the request instance in the container
        app()->instance('request', $request);

        try {
            $response = app('router')->dispatch($request);
        } finally {
            // Restore original request
            app()->instance('request', $originalRequest);
        }

        return new class($response) {
            private $response;
            public function __construct($response) {
                $this->response = $response;
            }
            public function successful() {
                return $this->response->isSuccessful();
            }
            public function json($key = null, $default = null) {
                $data = json_decode($this->response->getContent(), true);
                if (is_null($key)) {
                    return $data;
                }
                return data_get($data, $key, $default);
            }
            public function status() {
                return $this->response->getStatusCode();
            }
        };
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

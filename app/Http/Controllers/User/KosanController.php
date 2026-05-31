<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kosan;
use Illuminate\Support\Facades\Auth;

class KosanController extends Controller
{
    public function show($id)
    {
        $response = $this->apiCall('GET', "kosan/{$id}");
        if (!$response->successful()) {
            abort(404, 'Kosan tidak ditemukan');
        }
        $kosan = json_decode(json_encode($response->json()['data']));

        $ulasans = collect($kosan->ulasans ?? []);
        $ratingRata = $ulasans->avg('rating') ?? 0;
        $totalUlasan = $ulasans->count();

        // Cek apakah user sudah pernah memberi ulasan & bisa mengulas
        $sudahUlasan = false;
        $bisaUlas = false;
        if (Auth::check()) {
            $sudahUlasan = $ulasans->where('user_id', Auth::id())->isNotEmpty();
            // Cek apakah ada pesanan disetujui (panggil API pemesanan atau periksa jika ada relasi)
            // Namun API kosan/{id} tidak mengembalikan pemesanan user ini, jadi kita harus memanggil API pemesanan
            $pemesananResp = $this->apiCall('GET', 'pemesanan', ['kosan_id' => $id, 'user_id' => Auth::id(), 'status' => 'disetujui']);
            if ($pemesananResp->successful()) {
                $pemesanans = $pemesananResp->json()['data']['data'] ?? [];
                $bisaUlas = count($pemesanans) > 0;
            }
        }

        return view('user.show', compact('kosan', 'ratingRata', 'totalUlasan', 'sudahUlasan', 'bisaUlas'));
    }

    public function index(Request $request)
    {
        $params = ['tersedia' => 1, 'per_page' => 12];
        $kosanResponse = $this->apiCall('GET', 'kosan', $params);
        $kosans = $this->paginateApiResponse($kosanResponse->json(), $request);
        
        $fasilitasResponse = $this->apiCall('GET', 'fasilitas');
        $resData = $fasilitasResponse->json()['data'] ?? [];
        $fasilitasList = isset($resData['data']) && is_array($resData['data'])
            ? $resData['data']
            : $resData;
        $fasilitasList = json_decode(json_encode($fasilitasList));
        
        return view('user.home', compact('kosans', 'fasilitasList'));
    }

    public function search(Request $request)
    {
        $params = $request->all();
        $params['tersedia'] = 1;
        $params['per_page'] = 12;
        // Map harga_max to max_harga for API
        if ($request->filled('harga_max')) {
            $params['max_harga'] = $request->harga_max;
        }

        $kosanResponse = $this->apiCall('GET', 'kosan', $params);
        $kosans = $this->paginateApiResponse($kosanResponse->json(), $request);
            
        $fasilitasResponse = $this->apiCall('GET', 'fasilitas');
        $resData = $fasilitasResponse->json()['data'] ?? [];
        $fasilitasList = isset($resData['data']) && is_array($resData['data'])
            ? $resData['data']
            : $resData;
        $fasilitasList = json_decode(json_encode($fasilitasList));

        return view('user.home', compact('kosans', 'fasilitasList'));
    }

    public function ulasan(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string'
        ]);

        $response = $this->apiCall('POST', 'ulasan', [
            'kosan_id' => $id,
            'rating' => $request->rating,
            'komentar' => $request->komentar
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Ulasan berhasil ditambahkan!');
        }

        $errorMsg = $response->json()['message'] ?? 'Gagal menambahkan ulasan.';
        return back()->with('error', $errorMsg);
    }
}

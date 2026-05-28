<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kosan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemesananController extends Controller
{
    /**
     * Dashboard: daftar semua pemesanan user
     */
    public function dashboard()
    {
        $response = $this->apiCall('GET', 'pemesanan');
        $pemesanans = json_decode(json_encode($response->json()['data'] ?? []));

        return view('user.dashboard', compact('pemesanans'));
    }

    public function index()
    {
        return redirect()->route('user.dashboard');
    }

    public function create(Request $request)
    {
        $kosanResp = $this->apiCall('GET', "kosan/{$request->kosan_id}");
        if (!$kosanResp->successful()) {
            return back()->with('error', 'Kosan tidak ditemukan.');
        }
        $kosan = json_decode(json_encode($kosanResp->json()['data']));

        if ($kosan->kamar_tersedia <= 0) {
            return back()->with('error', 'Maaf, kamar sudah penuh.');
        }

        return view('user.pemesanan.create', compact('kosan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kosan_id'      => 'required',
            'tanggal_masuk' => 'required|date|after_or_equal:today',
            'durasi_bulan'  => 'required|integer|min:1|max:24',
            'catatan'       => 'nullable|string|max:500',
        ]);

        $response = $this->apiCall('POST', 'pemesanan', $request->all());

        if ($response->successful()) {
            $pemesananId = $response->json()['data']['id'];
            return redirect()->route('user.pemesanan.show', $pemesananId)
                ->with('success', 'Pemesanan berhasil diajukan! Menunggu persetujuan pemilik kos.');
        }

        $errorMsg = $response->json()['message'] ?? 'Maaf, kamar sudah penuh atau terjadi kesalahan.';
        return back()->with('error', $errorMsg)->withInput();
    }

    public function show($id)
    {
        $response = $this->apiCall('GET', "pemesanan/{$id}");
        
        if (!$response->successful()) {
            abort($response->status() === 403 ? 403 : 404);
        }

        $pemesanan = json_decode(json_encode($response->json()['data']));

        return view('user.pemesanan.show', compact('pemesanan'));
    }

    public function destroy($id)
    {
        $response = $this->apiCall('DELETE', "pemesanan/{$id}");
        
        if ($response->successful()) {
            return redirect()->route('user.dashboard')
                ->with('success', 'Pemesanan berhasil dibatalkan.');
        }

        $errorMsg = $response->json()['message'] ?? 'Gagal membatalkan pemesanan.';
        if ($response->status() === 403) abort(403);
        
        return back()->with('error', $errorMsg);
    }
}

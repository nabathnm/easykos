<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kosan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class KosanApiController extends Controller
{
    #[OA\Get(
        path: "/api/kosan",
        summary: "Daftar semua kosan aktif (publik)",
        tags: ["Kosan"],
        parameters: [
            new OA\Parameter(name: "mine", in: "query", description: "Filter kosan milik saya (perlu login pemilik)", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "kota", in: "query", description: "Filter berdasarkan kota", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "tipe", in: "query", description: "Filter tipe (putra/putri/campur)", schema: new OA\Schema(type: "string", enum: ["putra", "putri", "campur"])),
            new OA\Parameter(name: "min_harga", in: "query", description: "Harga minimum", schema: new OA\Schema(type: "number")),
            new OA\Parameter(name: "max_harga", in: "query", description: "Harga maksimum", schema: new OA\Schema(type: "number")),
            new OA\Parameter(name: "tersedia", in: "query", description: "Hanya yang tersedia (1/0)", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Jumlah data per halaman", schema: new OA\Schema(type: "integer", default: 10))
        ],
        responses: [
            new OA\Response(response: 200, description: "Berhasil mengambil daftar kosan"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($request->boolean('mine')) {
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            if ($user->role !== 'pemilik') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pemilik yang dapat melihat kosan miliknya',
                ], 403);
            }
            $query = Kosan::with('fotoUtama')->where('user_id', $user->id);
        } else {
            $query = Kosan::with('fotoUtama')->where('status', 'aktif');
        }

        // Filter opsional
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kosan', 'like', '%' . $request->q . '%')
                  ->orWhere('kota', 'like', '%' . $request->q . '%')
                  ->orWhere('alamat', 'like', '%' . $request->q . '%');
            });
        }
        if ($request->filled('kota')) {
            $query->where('kota', 'like', '%' . $request->kota . '%');
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('min_harga')) {
            $query->where('harga_per_bulan', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('harga_per_bulan', '<=', $request->max_harga);
        }
        if ($request->filled('fasilitas')) {
            $fasilitas = is_array($request->fasilitas) ? $request->fasilitas : [$request->fasilitas];
            foreach ($fasilitas as $f) {
                $query->whereJsonContains('fasilitas', $f);
            }
        }
        if ($request->filled('tersedia') && $request->tersedia == 1) {
            $query->where('kamar_tersedia', '>', 0);
        }

        $kosans = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar kosan berhasil diambil',
            'data'    => $kosans,
        ]);
    }

    #[OA\Get(
        path: "/api/kosan/{id}",
        summary: "Detail satu kosan (publik)",
        tags: ["Kosan"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID Kosan", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Berhasil mengambil detail kosan"),
            new OA\Response(response: 404, description: "Kosan tidak ditemukan")
        ]
    )]
    public function show($id)
    {
        $kosan = Kosan::with(['pemilik:id,nama,no_telepon', 'fotoUtama', 'fotos', 'ulasans.user:id,nama'])
            ->find($id);

        if (! $kosan) {
            return response()->json([
                'success' => false,
                'message' => 'Kosan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kosan berhasil diambil',
            'data'    => $kosan,
        ]);
    }

    #[OA\Post(
        path: "/api/kosan",
        summary: "Tambah kosan baru (hanya pemilik)",
        tags: ["Kosan"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama_kosan", "deskripsi", "alamat", "kota", "harga_per_bulan", "jumlah_kamar", "kamar_tersedia", "tipe"],
                properties: [
                    new OA\Property(property: "nama_kosan", type: "string", example: "Kos Melati"),
                    new OA\Property(property: "deskripsi", type: "string", example: "Kos nyaman tengah kota"),
                    new OA\Property(property: "alamat", type: "string", example: "Jl. Merdeka No. 10"),
                    new OA\Property(property: "kota", type: "string", example: "Malang"),
                    new OA\Property(property: "harga_per_bulan", type: "number", example: 1000000),
                    new OA\Property(property: "jumlah_kamar", type: "integer", example: 10),
                    new OA\Property(property: "kamar_tersedia", type: "integer", example: 5),
                    new OA\Property(property: "tipe", type: "string", enum: ["putra", "putri", "campur"], example: "campur"),
                    new OA\Property(property: "fasilitas", type: "array", items: new OA\Items(type: "string"), example: ["WiFi", "AC"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Kosan berhasil ditambahkan"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'pemilik' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemilik atau admin yang dapat menambah kosan',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id'        => 'sometimes|required|exists:users,id',
            'nama_kosan'     => 'required|string|max:255',
            'deskripsi'      => 'required|string',
            'alamat'         => 'required|string',
            'kota'           => 'required|string|max:100',
            'harga_per_bulan'=> 'required|numeric|min:0',
            'jumlah_kamar'   => 'required|integer|min:1',
            'kamar_tersedia' => 'required|integer|min:0',
            'tipe'           => 'required|in:putra,putri,campur',
            'fasilitas'      => 'nullable|array',
            'fotos.*'        => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $userId = $user->role === 'admin' ? $request->get('user_id', $user->id) : $user->id;

        $kosan = Kosan::create(array_merge($validator->validated(), [
            'user_id' => $userId,
            'status'  => $request->get('status', 'aktif'),
        ]));

        if ($request->hasFile('fotos')) {
            $is_utama = true;
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('foto_kosan', 'public');
                \App\Models\FotoKosan::create([
                    'kosan_id' => $kosan->id,
                    'foto' => $path,
                    'is_utama' => $is_utama,
                ]);
                $is_utama = false;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Kosan berhasil ditambahkan',
            'data'    => $kosan->load('fotos'),
        ], 201);
    }

    #[OA\Patch(
        path: "/api/kosan/{id}",
        summary: "Update kosan (hanya pemilik kosan tersebut)",
        tags: ["Kosan"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID Kosan", schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nama_kosan", type: "string"),
                    new OA\Property(property: "deskripsi", type: "string"),
                    new OA\Property(property: "alamat", type: "string"),
                    new OA\Property(property: "kota", type: "string"),
                    new OA\Property(property: "harga_per_bulan", type: "number"),
                    new OA\Property(property: "jumlah_kamar", type: "integer"),
                    new OA\Property(property: "kamar_tersedia", type: "integer"),
                    new OA\Property(property: "tipe", type: "string", enum: ["putra", "putri", "campur"]),
                    new OA\Property(property: "status", type: "string", enum: ["aktif", "nonaktif"]),
                    new OA\Property(property: "fasilitas", type: "array", items: new OA\Items(type: "string"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Kosan berhasil diperbarui"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Kosan tidak ditemukan")
        ]
    )]
    public function update(Request $request, $id)
    {
        $kosan = Kosan::find($id);

        if (! $kosan) {
            return response()->json([
                'success' => false,
                'message' => 'Kosan tidak ditemukan',
            ], 404);
        }

        if ($kosan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah kosan ini',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_kosan'     => 'sometimes|required|string|max:255',
            'deskripsi'      => 'sometimes|required|string',
            'alamat'         => 'sometimes|required|string',
            'kota'           => 'sometimes|required|string|max:100',
            'harga_per_bulan'=> 'sometimes|required|numeric|min:0',
            'jumlah_kamar'   => 'sometimes|required|integer|min:1',
            'kamar_tersedia' => 'sometimes|required|integer|min:0',
            'tipe'           => 'sometimes|required|in:putra,putri,campur',
            'fasilitas'      => 'nullable|array',
            'status'         => 'sometimes|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $kosan->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kosan berhasil diperbarui',
            'data'    => $kosan->fresh(),
        ]);
    }

    #[OA\Delete(
        path: "/api/kosan/{id}",
        summary: "Hapus kosan (hanya pemilik kosan tersebut)",
        tags: ["Kosan"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID Kosan", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Kosan berhasil dihapus"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Kosan tidak ditemukan")
        ]
    )]
    public function destroy($id)
    {
        $kosan = Kosan::find($id);

        if (! $kosan) {
            return response()->json([
                'success' => false,
                'message' => 'Kosan tidak ditemukan',
            ], 404);
        }

        if ($kosan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus kosan ini',
            ], 403);
        }

        $kosan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kosan berhasil dihapus',
        ]);
    }
}

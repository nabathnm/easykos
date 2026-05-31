<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('user.dashboard') }}" class="nb-btn py-1 px-2 text-base">Pesanan Saya</a>
            <span class="nb-kicker">/</span>
            <span class="text-3xl font-black leading-none">Detail Pemesanan</span>
            <span class="nb-kicker">#{{ str_pad($pemesanan->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </x-slot>

    <div class="pt-6">
        <div class="nb-shell max-w-3xl space-y-6">
            @if(session('success'))
                <div class="nb-flash nb-flash-success">{{ session('success') }}</div>
            @endif

            @php
                $statusConfig = [
                    'pending' => ['label' => 'Menunggu Persetujuan', 'tone' => 'bg-yellow-200'],
                    'disetujui' => ['label' => 'Disetujui', 'tone' => 'bg-green-200'],
                    'ditolak' => ['label' => 'Ditolak', 'tone' => 'bg-red-200'],
                    'dibatalkan' => ['label' => 'Dibatalkan', 'tone' => 'bg-gray-200'],
                ];
                $sc = $statusConfig[$pemesanan->status] ?? $statusConfig['pending'];
            @endphp

            <section class="nb-card p-5 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-4xl font-black leading-none">{{ $sc['label'] }}</p>
                        <p class="nb-kicker mt-1">
                            @if($pemesanan->status === 'pending')
                                Menunggu konfirmasi dari pemilik kosan.
                            @elseif($pemesanan->status === 'disetujui')
                                Pesanan sudah disetujui.
                            @elseif($pemesanan->status === 'ditolak')
                                Pesanan ditolak oleh pemilik kosan.
                            @else
                                Pesanan ini sudah dibatalkan.
                            @endif
                        </p>
                    </div>
                    <span class="nb-kicker border-2 border-black px-2 py-1 {{ $sc['tone'] }}">{{ ucfirst($pemesanan->status) }}</span>
                </div>
            </section>

            <section class="nb-card p-5 md:p-6">
                <h3 class="text-3xl font-black leading-none">Kosan yang Dipesan</h3>
                <div class="mt-5 grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)] lg:items-stretch">
                    <div class="overflow-hidden border-2 border-black bg-gray-200">
                        @if($pemesanan->kosan->fotoUtama)
                            <img src="{{ asset('storage/' . $pemesanan->kosan->fotoUtama->foto) }}" alt="{{ $pemesanan->kosan->nama_kosan }}" class="h-full min-h-56 w-full object-cover">
                        @else
                            <div class="flex h-full min-h-56 items-center justify-center px-4 text-center">
                                <p class="text-2xl font-black">Foto belum tersedia</p>
                            </div>
                        @endif
                    </div>
                    <div class="nb-card-soft p-4 md:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('kosan.show', $pemesanan->kosan->id ?? $pemesanan->kosan_id) }}" class="block text-3xl font-black leading-none">
                                    {{ $pemesanan->kosan->nama_kosan }}
                                </a>
                                <p class="mt-2 text-lg font-medium">{{ $pemesanan->kosan->alamat }}, {{ $pemesanan->kosan->kota }}</p>
                            </div>
                            <span class="nb-kicker border-2 border-black px-2 py-1">
                                {{ ucfirst($pemesanan->kosan->tipe) }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="nb-card bg-white p-3">
                                <p class="nb-kicker">Harga</p>
                                <p class="mt-1 text-2xl font-black">Rp {{ number_format($pemesanan->kosan->harga_per_bulan, 0, ',', '.') }}</p>
                                <p class="nb-kicker mt-1">per bulan</p>
                            </div>
                            <div class="nb-card bg-white p-3">
                                <p class="nb-kicker">Pemilik</p>
                                <p class="mt-1 text-2xl font-black">{{ $pemesanan->kosan->pemilik->nama ?? '-' }}</p>
                                @if($pemesanan->kosan->pemilik->no_telepon)
                                    <p class="nb-kicker mt-1">{{ $pemesanan->kosan->pemilik->no_telepon }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="nb-card p-5 md:p-6">
                <h3 class="text-3xl font-black leading-none">Rincian</h3>
                <div class="mt-5 space-y-3 text-lg font-medium">
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>No. Pemesanan</span>
                        <span class="font-black">#{{ str_pad($pemesanan->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>Tanggal Pesan</span>
                        <span class="font-black">{{ $pemesanan->created_at->translatedFormat('d F Y, H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>Tanggal Masuk</span>
                        <span class="font-black">{{ \Carbon\Carbon::parse($pemesanan->tanggal_masuk)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>Durasi</span>
                        <span class="font-black">{{ $pemesanan->durasi_bulan }} bulan</span>
                    </div>
                    @if($pemesanan->catatan)
                        <div>
                            <span class="nb-kicker">Catatan</span>
                            <p class="nb-card-soft mt-2 p-3 text-lg font-medium">{{ $pemesanan->catatan }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="nb-card p-5 md:p-6">
                <h3 class="text-3xl font-black leading-none">Ringkasan Biaya</h3>
                <div class="mt-5 space-y-3 text-lg font-medium">
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>Harga per bulan</span>
                        <span class="font-black">Rp {{ number_format($pemesanan->kosan->harga_per_bulan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-black pb-2">
                        <span>Durasi</span>
                        <span class="font-black">{{ $pemesanan->durasi_bulan }} bulan</span>
                    </div>
                    <div class="flex items-center justify-between text-2xl font-black">
                        <span>Total</span>
                        <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            <div class="flex gap-3">
                <a href="{{ route('user.dashboard') }}" class="nb-btn flex-1">Kembali</a>
                @if($pemesanan->status === 'pending')
                    <form method="POST" action="{{ route('user.pemesanan.destroy', $pemesanan->id) }}" class="flex-1" onsubmit="return confirm('Yakin ingin membatalkan pemesanan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="nb-btn nb-btn-danger w-full">Batalkan Pemesanan</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

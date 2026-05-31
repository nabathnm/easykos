<x-app-layout>
    <x-slot name="header">
        <p class="nb-kicker">User</p>
        <h2 class="text-3xl font-black leading-none md:text-4xl">Pesanan Saya</h2>
    </x-slot>

    <div class="pt-6">
        <div class="nb-shell">
            @php
                $totalPesanan = $pemesanans->count();
                $pending = $pemesanans->where('status', 'pending')->count();
                $disetujui = $pemesanans->where('status', 'disetujui')->count();
                $ditolak = $pemesanans->where('status', 'ditolak')->count();
            @endphp

            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <article class="nb-card p-4 text-center">
                    <p class="text-3xl font-black md:text-4xl">{{ $totalPesanan }}</p>
                    <p class="nb-kicker mt-1">Total</p>
                </article>
                <article class="nb-card p-4 text-center">
                    <p class="text-3xl font-black md:text-4xl">{{ $pending }}</p>
                    <p class="nb-kicker mt-1">Menunggu</p>
                </article>
                <article class="nb-card p-4 text-center">
                    <p class="text-3xl font-black md:text-4xl">{{ $disetujui }}</p>
                    <p class="nb-kicker mt-1">Disetujui</p>
                </article>
                <article class="nb-card p-4 text-center">
                    <p class="text-3xl font-black md:text-4xl">{{ $ditolak }}</p>
                    <p class="nb-kicker mt-1">Ditolak</p>
                </article>
            </section>

            @if($pemesanans->count())
                <section class="mt-6 space-y-4">
                    @foreach($pemesanans as $pemesanan)
                        @php
                            $status = [
                                'pending' => 'bg-yellow-200',
                                'disetujui' => 'bg-green-200',
                                'ditolak' => 'bg-red-200',
                                'dibatalkan' => 'bg-gray-200',
                            ][$pemesanan->status] ?? 'bg-gray-200';
                        @endphp

                        <a href="{{ route('user.pemesanan.show', $pemesanan->id) }}" class="nb-card nb-card-hover block p-4 md:p-5">
                            <div class="grid gap-4 md:grid-cols-[160px_minmax(0,1fr)] md:items-start">
                                <div class="h-48 w-full shrink-0 overflow-hidden border-2 border-black bg-gray-200 md:h-28 md:w-40">
                                    @if($pemesanan->kosan && $pemesanan->kosan->fotoUtama)
                                        <img src="{{ asset('storage/' . $pemesanan->kosan->fotoUtama->foto) }}"
                                             alt="{{ $pemesanan->kosan->nama_kosan }}"
                                             class="h-full w-full object-cover">
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="text-2xl font-black leading-none md:text-3xl">{{ $pemesanan->kosan->nama_kosan ?? 'Kosan Dihapus' }}</p>
                                            <p class="mt-1 text-lg font-medium">
                                                {{ $pemesanan->kosan->alamat ?? '' }}{{ ($pemesanan->kosan && $pemesanan->kosan->alamat) ? ',' : '' }} {{ $pemesanan->kosan->kota ?? '' }}
                                            </p>
                                        </div>
                                        <span class="nb-kicker border-2 border-black px-2 py-1 {{ $status }}">
                                            {{ ucfirst($pemesanan->status) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-lg font-medium">
                                        <span>{{ \Carbon\Carbon::parse($pemesanan->tanggal_masuk)->translatedFormat('d M Y') }}</span>
                                        <span>{{ $pemesanan->durasi_bulan }} bulan</span>
                                        <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </section>
            @else
                <section class="nb-card mt-6 p-10 text-center">
                    <p class="text-4xl font-black">Belum Ada Pesanan</p>
                    <p class="mt-2 text-lg font-medium">Mulai cari kosan dan ajukan pemesanan pertama.</p>
                    <a href="{{ route('home') }}" class="nb-btn nb-btn-primary mt-4">Cari Kosan</a>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>

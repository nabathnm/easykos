<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" class="nb-btn py-1 px-2 text-base">Beranda</a>
            <span class="nb-kicker">/</span>
            <span class="text-2xl font-black leading-none">{{ $kosan->nama_kosan }}</span>
        </div>
    </x-slot>

    <div class="pt-6">
        <div class="nb-shell space-y-6">
            <section x-data="{ active: 0 }" class="nb-card overflow-hidden">
                @if($kosan->fotos->count())
                    <div class="relative">
                        @foreach($kosan->fotos as $i => $foto)
                            <img x-show="active === {{ $i }}"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 src="{{ asset('storage/' . $foto->foto) }}"
                                 alt="{{ $kosan->nama_kosan }}"
                                 class="h-72 w-full object-cover md:h-[420px]">
                        @endforeach

                        @if($kosan->fotos->count() > 1)
                            <button @click="active = active > 0 ? active - 1 : {{ $kosan->fotos->count() - 1 }}"
                                    class="nb-btn absolute left-3 top-1/2 -translate-y-1/2 py-2 px-3 text-base"
                                    aria-label="Foto sebelumnya">
                                ‹
                            </button>
                            <button @click="active = active < {{ $kosan->fotos->count() - 1 }} ? active + 1 : 0"
                                    class="nb-btn absolute right-3 top-1/2 -translate-y-1/2 py-2 px-3 text-base"
                                    aria-label="Foto berikutnya">
                                ›
                            </button>
                            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                                @foreach($kosan->fotos as $i => $foto)
                                    <button @click="active = {{ $i }}"
                                            class="h-3 border-2 border-black transition-all"
                                            :class="active === {{ $i }} ? 'w-8 bg-black' : 'w-3 bg-white'"
                                            aria-label="Pilih foto {{ $i + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex h-72 items-center justify-center bg-gray-200 text-3xl font-black md:h-[420px]">
                        Tidak Ada Foto
                    </div>
                @endif
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <section class="nb-card p-5 md:p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="nb-kicker border-2 border-black px-2 py-1 {{ $kosan->tipe === 'putra' ? 'bg-blue-200' : ($kosan->tipe === 'putri' ? 'bg-pink-200' : 'bg-yellow-200') }}">
                                {{ ucfirst($kosan->tipe) }}
                            </span>
                            <span class="nb-kicker border-2 border-black px-2 py-1 {{ $kosan->status === 'aktif' ? 'bg-green-200' : 'bg-gray-200' }}">
                                {{ ucfirst($kosan->status) }}
                            </span>
                        </div>

                        <h1 class="mt-3 text-4xl font-black leading-none">{{ $kosan->nama_kosan }}</h1>
                        <p class="mt-2 text-lg font-medium">{{ $kosan->alamat }}, {{ $kosan->kota }}</p>

                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-xl {{ $i <= round($ratingRata ?? 0) ? 'text-amber-500' : 'text-gray-300' }}">★</span>
                                @endfor
                            </div>
                            <span class="text-lg font-bold">{{ number_format($ratingRata ?? 0, 1) }}</span>
                            <span class="nb-kicker">({{ $totalUlasan }} ulasan)</span>
                        </div>
                    </section>

                    <section class="nb-card p-5 md:p-6">
                        <h2 class="text-3xl font-black leading-none">Deskripsi</h2>
                        <p class="mt-3 whitespace-pre-line text-lg font-medium">{{ $kosan->deskripsi }}</p>
                    </section>

                    @if($kosan->fasilitas && count($kosan->fasilitas))
                        <section class="nb-card p-5 md:p-6">
                            <h2 class="text-3xl font-black leading-none">Fasilitas</h2>
                            <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3">
                                @foreach($kosan->fasilitas as $f)
                                    <div class="nb-card-soft px-3 py-2 text-lg font-semibold">
                                        {{ ucfirst($f) }}
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="nb-card p-5 md:p-6">
                        <h2 class="text-3xl font-black leading-none">Informasi Kamar</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="nb-card-soft p-4 text-center">
                                <p class="text-4xl font-black">{{ $kosan->jumlah_kamar }}</p>
                                <p class="nb-kicker mt-1">Total</p>
                            </div>
                            <div class="nb-card-soft p-4 text-center">
                                <p class="text-4xl font-black">{{ $kosan->kamar_tersedia }}</p>
                                <p class="nb-kicker mt-1">Tersedia</p>
                            </div>
                        </div>
                    </section>

                    <section class="nb-card p-5 md:p-6">
                        <h2 class="text-3xl font-black leading-none">Ulasan</h2>
                        <p class="nb-kicker mt-1">{{ $totalUlasan }} ulasan</p>

                        @if($kosan->ulasans->count())
                            <div class="mt-4 space-y-3">
                                @foreach($kosan->ulasans->sortByDesc('created_at') as $ulasan)
                                    <article class="nb-card-soft p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xl font-black leading-none">{{ $ulasan->user->nama ?? 'Anonim' }}</p>
                                                <p class="mt-1 text-lg">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <span class="{{ $i <= $ulasan->rating ? 'text-amber-500' : 'text-gray-300' }}">★</span>
                                                    @endfor
                                                </p>
                                            </div>
                                            <span class="nb-kicker">{{ $ulasan->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if($ulasan->komentar)
                                            <p class="mt-2 text-lg font-medium">{{ $ulasan->komentar }}</p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="nb-card-soft mt-4 p-4 text-center">
                                <p class="text-xl font-semibold">Belum ada ulasan.</p>
                            </div>
                        @endif

                        @auth
                            @if(Auth::user()->role === 'user')
                                @if($sudahUlasan)
                                    <p class="nb-kicker mt-4">Kamu sudah memberikan ulasan untuk kosan ini.</p>
                                @elseif($bisaUlas)
                                    <form action="{{ route('user.kosan.ulasan', $kosan->id) }}" method="POST" class="mt-5 space-y-3">
                                        @csrf
                                        <div x-data="{ rating: 0, hover: 0 }">
                                            <label class="nb-label">Rating</label>
                                            <div class="flex gap-1 text-3xl">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <button type="button"
                                                            @click="rating = {{ $i }}"
                                                            @mouseenter="hover = {{ $i }}"
                                                            @mouseleave="hover = 0"
                                                            class="leading-none">
                                                        <span :class="(hover || rating) >= {{ $i }} ? 'text-amber-500' : 'text-gray-300'">★</span>
                                                    </button>
                                                @endfor
                                            </div>
                                            <input type="hidden" name="rating" :value="rating">
                                        </div>

                                        <div>
                                            <label class="nb-label">Komentar</label>
                                            <textarea name="komentar" rows="3" class="nb-textarea" placeholder="Tulis pengalaman kamu..."></textarea>
                                        </div>

                                        <button type="submit" class="nb-btn nb-btn-primary">Kirim Ulasan</button>
                                    </form>
                                @else
                                    <p class="nb-kicker mt-4">Ulasan tersedia setelah pemesanan kamu disetujui.</p>
                                @endif
                            @endif
                        @endauth
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="nb-card p-5 md:p-6">
                        <p class="text-4xl font-black leading-none">
                            Rp {{ number_format($kosan->harga_per_bulan, 0, ',', '.') }}
                        </p>
                        <p class="nb-kicker mt-1">per bulan</p>

                        <div class="mt-4 space-y-2 text-lg font-medium">
                            <div class="flex justify-between border-b-2 border-black pb-2">
                                <span>Tipe</span>
                                <span class="font-black">{{ ucfirst($kosan->tipe) }}</span>
                            </div>
                            <div class="flex justify-between border-b-2 border-black pb-2">
                                <span>Kamar Tersedia</span>
                                <span class="font-black">{{ $kosan->kamar_tersedia }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pemilik</span>
                                <span class="font-black">{{ $kosan->pemilik->nama ?? '-' }}</span>
                            </div>
                        </div>

                        @auth
                            @if(Auth::user()->role === 'user' && $kosan->kamar_tersedia > 0)
                                <a href="{{ route('user.pemesanan.create', ['kosan_id' => $kosan->id]) }}" class="nb-btn nb-btn-primary mt-5 w-full">
                                    Ajukan Pemesanan
                                </a>
                            @elseif($kosan->kamar_tersedia <= 0)
                                <div class="nb-card-soft mt-5 p-3 text-center text-lg font-black">Kamar Penuh</div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="nb-btn nb-btn-primary mt-5 w-full">
                                Masuk untuk Memesan
                            </a>
                        @endauth
                    </section>

                    <section class="nb-card p-5 md:p-6">
                        <h3 class="text-2xl font-black leading-none">Kontak Pemilik</h3>
                        <p class="mt-2 text-xl font-semibold">{{ $kosan->pemilik->nama ?? '-' }}</p>
                        @if($kosan->pemilik->no_telepon)
                            <p class="nb-kicker mt-1">{{ $kosan->pemilik->no_telepon }}</p>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

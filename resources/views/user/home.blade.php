<x-app-layout>
    <x-slot name="header">
        <p class="nb-kicker">Daftar</p>
        <h2 class="text-4xl font-black leading-none">Beranda Kosan</h2>
    </x-slot>

    <div class="pt-6">
        <div class="nb-shell">
            <form method="GET" action="{{ route('kosan.search') }}" class="nb-card p-4 md:p-5" x-data="{ showFilters: false }">
                <div class="flex flex-col gap-3 md:flex-row">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kosan, kota, atau alamat..."
                           id="q" class="nb-input flex-1" />
                    <div class="flex gap-2">
                        <button type="button" @click="showFilters = !showFilters" 
                                :class="showFilters ? 'bg-yellow-200' : ''"
                                class="nb-btn nb-btn-secondary whitespace-nowrap">
                            <span x-text="showFilters ? 'Tutup Filter' : 'Filter'"></span>
                        </button>
                        <button type="submit" class="nb-btn nb-btn-primary whitespace-nowrap">Cari</button>
                    </div>
                </div>

                <div x-show="showFilters" x-transition class="mt-5 border-t-2 border-black pt-5">
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Tipe Kos -->
                        <div>
                            <p class="nb-kicker mb-2">Tipe Kos</p>
                            <select name="tipe" class="nb-select">
                                <option value="">Semua Tipe</option>
                                <option value="putra" {{ request('tipe') == 'putra' ? 'selected' : '' }}>Putra</option>
                                <option value="putri" {{ request('tipe') == 'putri' ? 'selected' : '' }}>Putri</option>
                                <option value="campur" {{ request('tipe') == 'campur' ? 'selected' : '' }}>Campur</option>
                            </select>
                        </div>

                        <!-- Harga Max -->
                        <div>
                            <p class="nb-kicker mb-2">Harga Maksimal (/bulan)</p>
                            <input type="number" name="harga_max" value="{{ request('harga_max') }}" placeholder="Contoh: 1500000" class="nb-input">
                        </div>

                        <!-- Fasilitas (Full Row) -->
                        <div class="md:col-span-2">
                            <p class="nb-kicker mb-4 border-b-2 border-black pb-1 inline-block">Fasilitas</p>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                                @foreach($fasilitasList as $f)
                                    <label class="flex items-start gap-2 cursor-pointer group">
                                        <div class="relative flex-shrink-0 mt-1">
                                            <input type="checkbox" name="fasilitas[]" value="{{ $f->nama_fasilitas }}" 
                                                   {{ is_array(request('fasilitas')) && in_array($f->nama_fasilitas, request('fasilitas')) ? 'checked' : '' }}
                                                   class="w-5 h-5 border-2 border-black rounded-none checked:bg-pink-500 appearance-none bg-white">
                                            <svg class="absolute w-3 h-3 text-white pointer-events-none hidden group-has-[:checked]:block left-1 top-1" 
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <span class="text-base font-bold leading-tight group-hover:underline">{{ $f->nama_fasilitas }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <a href="{{ route('home') }}" class="text-sm font-bold text-gray-500 hover:text-black mr-4 flex items-center">Reset Filter</a>
                        <button type="submit" class="nb-btn nb-btn-secondary py-2 px-6">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($kosans as $kosan)
                    <a href="{{ route('kosan.show', $kosan) }}"
                        class="nb-card nb-card-hover overflow-hidden">
                        @if(isset($kosan->foto_utama))
                            <img src="{{ asset('storage/' . $kosan->foto_utama->foto) }}" alt="{{ $kosan->nama_kosan }}"
                                 class="h-48 w-full object-cover">
                        @else
                            <div class="flex h-48 items-center justify-center bg-[#E5E7EB] text-xl font-bold">
                                Tidak Ada Foto
                            </div>
                        @endif

                        <div class="p-4">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span
                                    class="nb-kicker border-2 border-black px-2 py-1 {{ $kosan->tipe === 'putra' ? 'bg-blue-200' : ($kosan->tipe === 'putri' ? 'bg-pink-200' : 'bg-yellow-200') }}">
                                    {{ ucfirst($kosan->tipe) }}
                                </span>
                                <span class="nb-kicker">{{ $kosan->kamar_tersedia }} kamar</span>
                            </div>
                            <h3 class="text-3xl font-black leading-none">{{ $kosan->nama_kosan }}</h3>
                            <p class="mt-2 text-lg font-medium">
                                {{ $kosan->kota }}
                            </p>
                            <p class="mt-2 text-2xl font-black">
                                Rp {{ number_format($kosan->harga_per_bulan, 0, ',', '.') }}
                                <span class="nb-kicker">/bulan</span>
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="nb-card col-span-full p-10 text-center">
                        <p class="nb-kicker">Kosong</p>
                        <p class="mt-2 text-4xl font-black">Belum Ada Kosan Tersedia</p>
                        <p class="mt-2 text-xl font-medium">Coba ubah kata kunci pencarian.</p>
                    </div>
                @endforelse
            </div>

            @if($kosans->hasPages())
                <div class="mt-8">{{ $kosans->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

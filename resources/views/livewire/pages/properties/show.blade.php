<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Property;
use Illuminate\Support\Str;
use Illuminate\View\View;

new #[Layout('layouts.landing')] class extends Component {
    
    public Property $property;
    public $schemaData = [];
    public $allPhotos = []; 

    // --- LOGIC KALKULATOR (Variable) ---
    public $harga_properti = 0;
    public $dp_persen = 20;     
    public $bunga_persen = 8;   
    public $tenor_tahun = 15;   
    public $cicilan_bulan = 0;
    public $total_dp = 0;
    public $pokok_pinjaman = 0;

    public function mount($slug)
    {
        $this->property = Property::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $this->harga_properti = $this->property->price;
        $this->hitungKPR(); // Hitung awal

        // Setup Galeri
        $this->allPhotos[] = $this->getPhotoUrl($this->property->photo);
        if (!empty($this->property->gallery) && is_array($this->property->gallery)) {
            foreach($this->property->gallery as $gal) {
                $this->allPhotos[] = $this->getPhotoUrl($gal);
            }
        }

        // SEO Schema
        $this->schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->property->title,
            'description' => Str::limit(strip_tags($this->property->description), 160),
            'image' => $this->allPhotos,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => $this->property->price,
            ],
        ];
    }

    public function getPhotoUrl($path) {
        if (!$path) return asset('images/default-house.jpg');
        return Str::startsWith($path, 'http') ? $path : asset('storage/'.$path);
    }

    // --- PERBAIKAN LOGIC HITUNG (ANTI ERROR KOSONG) ---
    public function hitungKPR()
    {
        // 1. SANITASI: Jika input kosong/null, anggap 0
        // (float) akan mengubah "" menjadi 0 secara otomatis
        $dp_safe = is_numeric($this->dp_persen) ? (float)$this->dp_persen : 0;
        $bunga_safe = is_numeric($this->bunga_persen) ? (float)$this->bunga_persen : 0;
        $tenor_safe = is_numeric($this->tenor_tahun) ? (float)$this->tenor_tahun : 0;

        // 2. Hitung Nominal DP
        $this->total_dp = $this->harga_properti * ($dp_safe / 100);
        
        // 3. Hitung Pokok Pinjaman
        $this->pokok_pinjaman = $this->harga_properti - $this->total_dp;
        
        // 4. Hitung Cicilan (Cegah pembagian nol)
        if ($this->pokok_pinjaman > 0 && $bunga_safe > 0 && $tenor_safe > 0) {
            $rate_bulan = ($bunga_safe / 100) / 12;
            $bulan_tenor = $tenor_safe * 12;
            
            // Rumus PMT Anuitas
            $this->cicilan_bulan = $this->pokok_pinjaman * ($rate_bulan / (1 - pow(1 + $rate_bulan, -$bulan_tenor)));
        } else {
            $this->cicilan_bulan = 0;
        }
    }

    // Listener Livewire: Setiap user ngetik, hitung ulang
    public function updated($property)
    {
        // Cek jika yang diubah adalah variabel kalkulator
        if (in_array($property, ['dp_persen', 'bunga_persen', 'tenor_tahun'])) {
            $this->hitungKPR();
        }
    }

    public function with(): array
    {
        $text = "Halo, saya tertarik dengan: {$this->property->title}";
        $whatsappUrl = "https://wa.me/6281234567890?text=" . urlencode($text);
        return ['whatsappUrl' => $whatsappUrl];
    }

    public function rendering(View $view): void
    {
        $view->layoutData([
            'title' => $this->property->title,
            'description' => Str::limit(strip_tags($this->property->description), 155),
            'image' => $this->allPhotos[0] ?? null
        ]);
    }
}; ?>

<div class="bg-gray-50 min-h-screen font-sans pb-12">
    
    <x-slot name="head">
        <script type="application/ld+json">
            {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    </x-slot>

    {{-- NAVBAR PUTIH + BURGER (FIXED) --}}
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white text-gray-800 sticky top-0 z-50 shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                        <img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-12 w-auto object-contain">
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-8 font-bold text-sm tracking-wide">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>HOME</a>
                    <a href="{{ route('developers') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>DEVELOPERS</a>
                    <a href="{{ route('kpr') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>KALKULATOR KPR</a>
                </div>
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-gray-600 hover:text-[#0A192F] focus:outline-none p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white border-t border-gray-100 shadow-xl absolute w-full z-50" style="display: none;">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <a href="{{ route('home') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Home</a>
                <a href="{{ route('developers') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Developers</a>
                <a href="{{ route('kpr') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Kalkulator KPR</a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="{{ route('login') }}" class="block px-3 py-2 text-sm text-gray-500 hover:text-[#0A192F]">Masuk / Login Agen</a>
            </div>
        </div>
    </nav>

    {{-- HERO SLIDER (Mobile Height 300px) --}}
    <div x-data="{ activeSlide: 0, slides: @js($allPhotos), next() { this.activeSlide = (this.activeSlide === this.slides.length - 1) ? 0 : this.activeSlide + 1 }, prev() { this.activeSlide = (this.activeSlide === 0) ? this.slides.length - 1 : this.activeSlide - 1 } }" x-init="setInterval(() => next(), 4000)" class="relative h-[300px] md:h-[550px] bg-gray-900 overflow-hidden group">
        <template x-for="(slide, index) in slides" :key="index">
            <img x-show="activeSlide === index" x-transition:enter="transition duration-1000" x-transition:leave="transition duration-1000" :src="slide" class="absolute inset-0 w-full h-full object-cover">
        </template>
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-12 max-w-7xl mx-auto z-10">
            <div class="flex gap-3 mb-4"><span class="bg-[#D4AF37] text-white text-xs font-bold px-3 py-1 rounded shadow uppercase tracking-wider">{{ $property->listing_type }}</span><span class="bg-white/20 backdrop-blur text-white text-xs font-bold px-3 py-1 rounded shadow uppercase tracking-wider border border-white/30">{{ $property->property_type }}</span></div>
            <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight mb-2 drop-shadow-lg">{{ $property->title }}</h1>
            <p class="text-gray-100 text-lg flex items-center gap-2 font-medium drop-shadow-lg"><span>📍</span> {{ $property->address }}, {{ $property->district }}, {{ $property->city }}</p>
        </div>
        <div class="absolute bottom-6 right-6 md:right-12 flex gap-2 z-20"><template x-for="(slide, index) in slides" :key="index"><button @click="activeSlide = index" class="w-2 h-2 rounded-full transition-all duration-300" :class="activeSlide === index ? 'bg-[#D4AF37] w-6' : 'bg-white/50 hover:bg-white'"></button></template></div>
    </div>

    {{-- KONTEN --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 -mt-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                {{-- SPESIFIKASI --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-[#0A192F] mb-6 flex items-center gap-2"><span class="w-1 h-6 bg-[#D4AF37] rounded-full"></span> Spesifikasi Lengkap</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-8 gap-x-4">
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">K. Tidur</span><span class="text-xl font-bold text-[#0A192F]">🛏️ {{ $property->bedrooms }} {{ $property->maid_bedrooms > 0 ? '+'.$property->maid_bedrooms : '' }}</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">K. Mandi</span><span class="text-xl font-bold text-[#0A192F]">🚿 {{ $property->bathrooms }} {{ $property->maid_bathrooms > 0 ? '+'.$property->maid_bathrooms : '' }}</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">L. Tanah</span><span class="text-xl font-bold text-[#0A192F]">📐 {{ $property->land_area }} m²</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">L. Bangunan</span><span class="text-xl font-bold text-[#0A192F]">🏠 {{ $property->building_area }} m²</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Listrik</span><span class="font-bold text-gray-800">⚡ {{ $property->electricity }} W</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Air</span><span class="font-bold text-gray-800">💧 {{ $property->water_source ?? '-' }}</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Garasi</span><span class="font-bold text-gray-800">🚗 {{ $property->garage_size }} Mobil</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Carport</span><span class="font-bold text-gray-800">🚘 {{ $property->carport_size }} Mobil</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Hadap</span><span class="font-bold text-gray-800">🧭 {{ $property->orientation ?? '-' }}</span></div>
                        <div><span class="text-xs text-gray-400 font-bold uppercase block mb-1">Sertifikat</span><span class="font-bold text-gray-800">📜 {{ $property->certificate }}</span></div>
                    </div>
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Fasilitas Lainnya</h4>
                        <div class="flex flex-wrap gap-2">
                            @if($property->has_smart_home) <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">📱 Smart Home</span> @endif
                            @if($property->has_canopy) <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">☂️ Kanopi</span> @endif
                            @if($property->has_fence) <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">🚧 Pagar</span> @endif
                            @if($property->has_pool) <span class="bg-cyan-50 text-cyan-700 px-3 py-1 rounded-full text-xs font-bold">🏊 Kolam Renang</span> @endif
                            @if($property->is_hook) <span class="bg-amber-50 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">🏗️ Posisi Hook</span> @endif
                            <span class="bg-gray-50 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">{{ $property->furnishing }}</span>
                        </div>
                    </div>
                </div>

                {{-- GALERI --}}
                @if(count($allPhotos) > 1)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <h3 class="text-xl font-bold text-[#0A192F] mb-6">Galeri Foto</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4" x-data>
                            @foreach($allPhotos as $index => $img)
                                <button @click="window.scrollTo({top: 0, behavior: 'smooth'}); document.querySelector('[x-data]').__x.$data.activeSlide = {{ $index }}" class="relative h-32 rounded-lg overflow-hidden group cursor-pointer shadow-sm hover:shadow-md transition border-2 hover:border-[#D4AF37]"><img src="{{ $img }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110"></button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- DESKRIPSI --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-[#0A192F] mb-6">Deskripsi</h3>
                    <div class="prose max-w-none text-gray-600 leading-relaxed whitespace-pre-line">{{ $property->description }}</div>
                </div>

                {{-- KALKULATOR SEDERHANA (SAFE MODE) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-[#0A192F] mb-6">Estimasi Cicilan</h3>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div><label class="block text-xs font-bold text-gray-500 uppercase mb-2">DP (%)</label><input type="number" wire:model.live="dp_persen" class="w-full rounded-lg border-gray-300 font-bold focus:ring-[#D4AF37] focus:border-[#D4AF37]"></div>
                            <div><label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bunga (%)</label><input type="number" step="0.1" wire:model.live="bunga_persen" class="w-full rounded-lg border-gray-300 font-bold focus:ring-[#D4AF37] focus:border-[#D4AF37]"></div>
                            <div><label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tenor (Thn)</label><input type="number" wire:model.live="tenor_tahun" class="w-full rounded-lg border-gray-300 font-bold focus:ring-[#D4AF37] focus:border-[#D4AF37]"></div>
                        </div>
                        <div class="text-center border-t border-gray-200 pt-4"><span class="text-sm text-[#0A192F] font-bold block uppercase tracking-wider">Angsuran per Bulan</span><span class="text-3xl font-black text-[#D4AF37]">Rp {{ number_format($cicilan_bulan, 0, ',', '.') }}</span></div>
                        <div class="mt-4 text-center"><a href="{{ route('kpr', ['price' => $harga_properti]) }}" class="text-xs text-blue-600 font-bold hover:underline">Buka Kalkulator Pro →</a></div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-lg border-t-4 border-[#D4AF37] p-6 sticky top-24">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4 text-center">Penawaran Terbaik</p>
                    <div class="text-center mb-6 border-b border-gray-100 pb-6"><p class="text-3xl font-black text-[#0A192F]">Rp {{ number_format($property->price, 0, ',', '.') }}</p></div>
                    <a href="{{ $whatsappUrl }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold py-3.5 px-4 rounded-xl transition shadow hover:shadow-lg"><span>Hubungi via WhatsApp</span></a>
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="bg-white text-gray-600 pt-16 pb-8 border-t border-gray-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center mb-4"><img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-10 w-auto"></div>
            <div class="mb-6 flex justify-center gap-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-[#D4AF37]">Home</a>
                <a href="{{ route('kpr') }}" class="hover:text-[#D4AF37]">Kalkulator KPR</a>
                <a href="{{ route('developers') }}" class="hover:text-[#D4AF37]">Developers</a>
                <a href="{{ route('login') }}" class="hover:text-[#D4AF37]">Agent Login</a>
            </div>
            <p class="opacity-60 text-sm">© {{ date('Y') }} Website Roov. All rights reserved.</p>
        </div>
    </footer>
</div>
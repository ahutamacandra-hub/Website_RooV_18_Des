<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Property;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('layouts.landing')] class extends Component {
    use WithPagination; 

    public $keyword = '';
    public $listing_type = ''; 
    public $property_type = ''; 
    public $region = '';        
    
    public $min_price = '';
    public $max_price = '';
    public $min_bedroom = '';
    public $min_bathroom = '';

    public $sort_by = 'terbaru'; 

    public function updated($property) { $this->resetPage(); }

    public function with(): array
    {
        $scoutIds = [];
        if ($this->keyword) {
            $scoutIds = Property::search($this->keyword)->keys();
        }

        $query = Property::where('is_active', true);

        if ($this->keyword) {
            $query->whereIn('id', $scoutIds);
        }

        if ($this->listing_type) {
            if ($this->listing_type === 'dijual') {
                $query->whereIn('listing_type', ['secondary', 'primary', 'jual']);
            } elseif ($this->listing_type === 'disewa') {
                $query->whereIn('listing_type', ['sewa', 'disewa']);
            }
        }
        if ($this->property_type) $query->where('property_type', $this->property_type);
        
        if ($this->region) {
            $kecamatan = match($this->region) {
                'pusat' => ['Bubutan', 'Genteng', 'Simokerto', 'Tegalsari'],
                'timur' => ['Gubeng', 'Gunung Anyar', 'Tambaksari', 'Mulyorejo', 'Rungkut', 'Sukolilo', 'Tenggilis Mejoyo'],
                'barat' => ['Benowo', 'Babat Jerawat', 'Lakarsantri', 'Sambikerep', 'Tandes', 'Wiyung', 'Karangpilang'],
                'selatan' => ['Wonocolo', 'Wiyung', 'Sukomanunggal', 'Sawahan', 'Karangpilang', 'Jambangan', 'Gayungan'],
                'utara' => ['Kenjeran', 'Bulak', 'Semampir', 'Pabean Cantikan', 'Krembangan'],
                default => []
            };
            if (!empty($kecamatan)) $query->whereIn('district', $kecamatan);
        }

        if ($this->min_price) $query->where('price', '>=', $this->min_price);
        if ($this->max_price) $query->where('price', '<=', $this->max_price);
        if ($this->min_bedroom) $query->where('bedrooms', '>=', $this->min_bedroom);
        if ($this->min_bathroom) $query->where('bathrooms', '>=', $this->min_bathroom);

        switch ($this->sort_by) {
            case 'termurah': $query->orderBy('price', 'asc'); break;
            case 'termahal': $query->orderBy('price', 'desc'); break;
            default: $query->latest(); break;
        }

        return [
            'properties' => $query->paginate(9),
        ];
    }

    public function rendering(View $view): void
    {
        $view->layoutData([
            'title' => 'Website Roov - Cari Properti Impian',
            'description' => 'Portal properti terlengkap dengan filter wilayah Surabaya.',
        ]);
    }
}; ?>

<div class="bg-gray-50 min-h-screen font-sans">

    {{-- 1. NAVBAR PUTIH (LOGO PNG ASLI) --}}
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white text-gray-800 sticky top-0 z-40 shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                {{-- LOGO PNG MURNI --}}
                <div class="flex items-center">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                        {{-- Menggunakan Logo.png langsung --}}
                        {{-- h-12 = 48px, ukuran standar navbar yang proporsional --}}
                        <img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-12 w-auto object-contain">
                    </a>
                </div>

                {{-- DESKTOP MENU --}}
                <div class="hidden md:flex items-center gap-8 font-bold text-sm tracking-wide">
                    <button wire:click="$set('listing_type', 'dijual')" class="{{ $listing_type == 'dijual' ? 'text-roov-gold' : 'text-gray-600 hover:text-roov-navy' }} transition">DIJUAL</button>
                    <button wire:click="$set('listing_type', 'disewa')" class="{{ $listing_type == 'disewa' ? 'text-roov-gold' : 'text-gray-600 hover:text-roov-navy' }} transition">DISEWA</button>
                    <a href="{{ route('developers') }}" class="text-gray-600 hover:text-roov-navy transition" wire:navigate>DEVELOPERS</a>
                    <a href="{{ route('kpr') }}" class="text-gray-600 hover:text-roov-navy transition" wire:navigate>KALKULATOR KPR</a>
                </div>

                {{-- MOBILE BURGER --}}
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-gray-600 hover:text-roov-navy focus:outline-none p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- MOBILE MENU DROPDOWN --}}
        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-xl absolute w-full z-50" style="display: none;">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <button wire:click="$set('listing_type', 'dijual'); mobileMenuOpen = false" class="block w-full text-left px-3 py-2 rounded-md text-base font-bold hover:bg-gray-50 {{ $listing_type == 'dijual' ? 'text-roov-gold' : 'text-gray-800' }}">Dijual</button>
                <button wire:click="$set('listing_type', 'disewa'); mobileMenuOpen = false" class="block w-full text-left px-3 py-2 rounded-md text-base font-bold hover:bg-gray-50 {{ $listing_type == 'disewa' ? 'text-roov-gold' : 'text-gray-800' }}">Disewa</button>
                <a href="{{ route('developers') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Developers</a>
                <a href="{{ route('kpr') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Kalkulator KPR</a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="{{ route('login') }}" class="block px-3 py-2 text-sm text-gray-500 hover:text-roov-navy">Masuk / Login Agen</a>
            </div>
        </div>
    </nav>

    {{-- 2. FLOATING WHATSAPP --}}
    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Roov%20Property,%20saya%20tertarik%20tanya-tanya%20properti" 
       target="_blank"
       class="fixed bottom-6 right-6 z-50 bg-[#25D366] hover:bg-[#20bd5a] text-white p-3 md:p-4 rounded-full shadow-2xl transition-all duration-300 hover:scale-110 flex items-center justify-center group border-2 border-white">
        <svg class="w-7 h-7 md:w-8 md:h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

    {{-- 3. HERO SECTION (NAVY - Kontras) --}}
    <div class="relative bg-roov-navy py-12 md:py-20 overflow-hidden">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-2 tracking-tight">
                    Temukan Properti <span class="text-[#D4AF37]">Impian</span>
                </h1>
                <p class="text-gray-300 text-sm md:text-base">Ribuan properti eksklusif siap huni dan investasi terbaik.</p>
            </div>

            {{-- BOX FILTER --}}
            <div x-data="{ advanced: false }" class="bg-white p-6 rounded-2xl shadow-2xl border-t-4 border-[#D4AF37] max-w-5xl mx-auto transition-all duration-300">
                
                {{-- Tabs --}}
                <div class="flex gap-6 mb-6 border-b border-gray-100 pb-2 overflow-x-auto scrollbar-hide">
                    <button wire:click="$set('listing_type', '')" class="text-sm font-bold uppercase tracking-wider pb-2 border-b-2 whitespace-nowrap {{ $listing_type == '' ? 'border-[#0A192F] text-[#0A192F]' : 'border-transparent text-gray-400 hover:text-gray-600' }}">Semua</button>
                    <button wire:click="$set('listing_type', 'dijual')" class="text-sm font-bold uppercase tracking-wider pb-2 border-b-2 whitespace-nowrap {{ $listing_type == 'dijual' ? 'border-[#0A192F] text-[#0A192F]' : 'border-transparent text-gray-400 hover:text-gray-600' }}">Dijual</button>
                    <button wire:click="$set('listing_type', 'disewa')" class="text-sm font-bold uppercase tracking-wider pb-2 border-b-2 whitespace-nowrap {{ $listing_type == 'disewa' ? 'border-[#0A192F] text-[#0A192F]' : 'border-transparent text-gray-400 hover:text-gray-600' }}">Disewa</button>
                </div>

                {{-- FILTER UTAMA --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cari Lokasi / Project</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                            <input type="text" wire:model.live.debounce.500ms="keyword" placeholder="Cth: Citraland, Pakuwon..." class="w-full pl-9 rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37] h-11">
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tipe Properti</label>
                        <select wire:model.live="property_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37] h-11">
                            <option value="">Semua Tipe</option>
                            <option value="rumah">Rumah</option>
                            <option value="apartemen">Apartemen</option>
                            <option value="ruko">Ruko</option>
                            <option value="tanah">Tanah</option>
                        </select>
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button @click="advanced = !advanced" type="button" class="flex-1 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-4 rounded-lg transition h-11 border border-gray-200 text-sm">
                            <span x-text="advanced ? 'Tutup Filter' : 'Filter Lengkap'">Filter Lengkap</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="advanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- FILTER LANJUTAN --}}
                <div x-show="advanced" x-collapse class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-4" style="display: none;">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Wilayah Surabaya</label>
                        <select wire:model.live="region" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37]">
                            <option value="">Seluruh Surabaya</option>
                            <option value="barat">Surabaya Barat</option>
                            <option value="timur">Surabaya Timur</option>
                            <option value="pusat">Surabaya Pusat</option>
                            <option value="selatan">Surabaya Selatan</option>
                            <option value="utara">Surabaya Utara</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Harga Min</label>
                        <input type="number" wire:model.live.debounce.500ms="min_price" placeholder="Min" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Harga Max</label>
                        <input type="number" wire:model.live.debounce.500ms="max_price" placeholder="Max" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37]">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">K. Tidur</label>
                            <select wire:model.live="min_bedroom" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">-</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">K. Mandi</label>
                            <select wire:model.live="min_bathroom" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">-</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- HASIL PENCARIAN --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#0A192F]">Properti Pilihan</h2>
                <p class="text-gray-500 text-sm">Menampilkan {{ $properties->total() }} properti.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 font-medium">Urutkan:</span>
                <select wire:model.live="sort_by" class="rounded-lg border-gray-300 text-sm focus:border-[#D4AF37] focus:ring-[#D4AF37] py-2 pl-3 pr-8">
                    <option value="terbaru">Terbaru</option>
                    <option value="termurah">Harga Termurah</option>
                    <option value="termahal">Harga Termahal</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($properties as $prop)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 group flex flex-col">
                    <div class="relative h-64 overflow-hidden">
                        <a href="{{ route('properties.show', $prop->slug) }}" wire:navigate>
                            <img src="{{ Str::startsWith($prop->photo, 'http') ? $prop->photo : ($prop->photo ? asset('storage/'.$prop->photo) : asset('images/default-house.jpg')) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                        </a>
                        <div class="absolute top-4 left-4">
                            <span class="bg-[#0A192F]/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">{{ $prop->listing_type }}</span>
                        </div>
                        <div class="absolute bottom-4 left-4 bg-white/95 px-3 py-1 rounded-lg shadow-md">
                            <span class="text-[#0A192F] font-black text-lg">Rp {{ number_format($prop->price / 1000000, 0) }} Juta</span>
                        </div>
                    </div>

                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-[#D4AF37] uppercase tracking-wider">{{ $prop->property_type }}</span>
                            </div>
                            <a href="{{ route('properties.show', $prop->slug) }}" class="block mb-2" wire:navigate>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#D4AF37] transition line-clamp-1">{{ $prop->title }}</h3>
                            </a>
                            <p class="text-gray-500 text-sm flex items-center mb-4"><span>📍</span> {{ $prop->district }}, {{ $prop->city }}</p>
                            
                            <div class="grid grid-cols-3 gap-2 border-t border-gray-100 pt-4 mb-4">
                                <div class="text-center border-r border-gray-100">
                                    <span class="block text-gray-400 text-[10px] uppercase font-bold">K. Tidur</span><span class="font-bold text-gray-800">{{ $prop->bedrooms }}</span>
                                </div>
                                <div class="text-center border-r border-gray-100">
                                    <span class="block text-gray-400 text-[10px] uppercase font-bold">K. Mandi</span><span class="font-bold text-gray-800">{{ $prop->bathrooms }}</span>
                                </div>
                                <div class="text-center">
                                    <span class="block text-gray-400 text-[10px] uppercase font-bold">Luas</span><span class="font-bold text-gray-800">{{ $prop->land_area }}m²</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('properties.show', $prop->slug) }}" class="w-full block text-center bg-gray-50 border border-gray-200 text-[#0A192F] font-bold py-2 rounded-lg hover:bg-[#0A192F] hover:text-white transition" wire:navigate>Lihat Detail</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <span class="text-6xl block mb-4">🔍</span>
                    <h3 class="text-xl font-bold text-gray-800">Tidak ada properti ditemukan</h3>
                    <button wire:click="resetPage" class="mt-4 text-[#D4AF37] font-bold hover:underline">Reset Filter</button>
                </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $properties->links() }}
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="bg-[#0A192F] text-white pt-16 pb-8 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold tracking-tight mb-4">ROOV<span class="text-[#D4AF37]">PROPERTY</span></h2>
            
            <div class="mb-6 flex justify-center gap-6 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <a href="{{ route('kpr') }}" class="hover:text-white">Kalkulator KPR</a>
                <a href="{{ route('developers') }}" class="hover:text-white">Developers</a>
                <a href="{{ route('login') }}" class="hover:text-white">Agent Login</a>
            </div>

            <p class="opacity-60 text-sm">© {{ date('Y') }} Website Roov. All rights reserved.</p>
        </div>
    </footer>
</div>
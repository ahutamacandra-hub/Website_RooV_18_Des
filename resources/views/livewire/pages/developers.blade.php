<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\View\View;

new #[Layout('layouts.landing')] class extends Component {
    public $developers = [
        ['name' => 'Ciputra Group', 'desc' => 'Pengembang Citraland & Proyek Elit.', 'keyword' => 'Citraland'],
        ['name' => 'Pakuwon Group', 'desc' => 'Raja Mall & Apartemen Mewah.', 'keyword' => 'Pakuwon'],
        ['name' => 'Sinarmas Land', 'desc' => 'Pengembang Kota Mandiri.', 'keyword' => 'Sinarmas'],
        ['name' => 'Intiland', 'desc' => 'Inovasi Properti Modern.', 'keyword' => 'Graha Famili'],
        ['name' => 'Summarecon', 'desc' => 'Hunian Terpadu & Asri.', 'keyword' => 'Summarecon'],
        ['name' => 'Pilar Mas', 'desc' => 'Hunian Keluarga Nyaman.', 'keyword' => 'Pilar'],
    ];

    public function rendering(View $view): void
    {
        $view->layoutData([
            'title' => 'Rekanan Developer Resmi - Roov Property',
            'description' => 'Daftar developer properti terpercaya di Surabaya: Ciputra, Pakuwon, Sinarmas, Intiland.',
        ]);
    }
}; ?>

<div class="bg-gray-50 min-h-screen font-sans">
    
    {{-- NAVBAR PUTIH + LOGO PNG --}}
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
                    <a href="{{ route('developers') }}" class="text-[#D4AF37] transition" wire:navigate>DEVELOPERS</a>
                    <a href="{{ route('kpr') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>KALKULATOR KPR</a>
                </div>
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-gray-600 hover:text-[#0A192F] focus:outline-none p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-xl absolute w-full z-50" style="display: none;">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <a href="{{ route('home') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Home</a>
                <a href="{{ route('developers') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-[#D4AF37] hover:bg-gray-50">Developers</a>
                <a href="{{ route('kpr') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Kalkulator KPR</a>
            </div>
        </div>
    </nav>

    {{-- FLOATING WA --}}
    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Roov%20Property,%20saya%20tertarik%20tanya-tanya%20developer" target="_blank" class="fixed bottom-6 right-6 z-50 bg-[#25D366] hover:bg-[#20bd5a] text-white p-3 md:p-4 rounded-full shadow-2xl transition-all duration-300 hover:scale-110 flex items-center justify-center group border-2 border-white"><svg class="w-7 h-7 md:w-8 md:h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>

    {{-- HERO SECTION --}}
    <div class="relative bg-[#0A192F] py-16 md:py-20 overflow-hidden text-center px-4">
        <div class="relative z-10 max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">Rekanan Developer <span class="text-[#D4AF37]">Resmi</span></h1>
            <p class="text-gray-300 text-lg">Kami bekerja sama dengan pengembang properti terpercaya untuk memberikan hunian (Primary) terbaik bagi Anda.</p>
        </div>
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10 pointer-events-none">
            <div class="absolute -top-24 -left-24 w-64 h-64 rounded-full bg-[#D4AF37] blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 rounded-full bg-blue-500 blur-3xl"></div>
        </div>
    </div>
    
    {{-- GRID --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 -mt-8 relative z-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            @foreach($developers as $dev)
                <a href="{{ route('home', ['keyword' => $dev['keyword']]) }}" wire:navigate class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl border border-gray-100 transition-all duration-300 hover:-translate-y-2 flex flex-col items-center text-center relative overflow-hidden">
                    <div class="h-24 w-full bg-gray-50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-[#0A192F]/5 transition-colors"><span class="text-4xl text-gray-300 group-hover:text-[#0A192F] transition-colors">🏢</span></div>
                    <h3 class="text-xl font-bold text-[#0A192F] mb-2 group-hover:text-[#D4AF37] transition-colors">{{ $dev['name'] }}</h3>
                    <p class="text-sm text-gray-500 mb-6 line-clamp-2">{{ $dev['desc'] }}</p>
                    <span class="inline-flex items-center gap-2 text-sm font-bold text-[#0A192F] group-hover:text-[#D4AF37] transition-colors">Lihat Project <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></span>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-[#0A192F] to-[#D4AF37] transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- FOOTER PUTIH + LOGO PNG --}}
    <footer class="bg-white text-gray-600 pt-16 pb-8 border-t border-gray-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-10 w-auto">
            </div>
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
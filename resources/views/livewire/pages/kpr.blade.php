<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Http\Request;
use Illuminate\View\View;

new #[Layout('layouts.landing')] class extends Component {
    
    // INPUT UTAMA
    public $price_formatted = ''; 
    public $price = 0;            
    public $dp_percent = 20;
    public $tenor_years = 15;
    
    // BUNGA BERJENJANG
    public $fix_rates = []; 
    public $rate_float = 11.0;   
    
    // EXTRA PAYMENT
    public $extra_payments = [];

    // HASIL & ERROR
    public $schedule = []; 
    public $summary = [];  
    public $calculated = false;
    public $error_message = '';

    public function mount(Request $request)
    {
        $urlPrice = $request->query('price', 1000000000);
        $this->price = $urlPrice;
        $this->price_formatted = number_format($this->price, 0, ',', '.');

        $this->fix_rates = [['rate' => 4.5, 'years' => 3]];
        $this->extra_payments = [];
    }

    public function updatedPriceFormatted($value)
    {
        $numericValue = preg_replace('/[^0-9]/', '', $value);
        $this->price = (int) $numericValue;
        $this->price_formatted = $this->price > 0 ? number_format($this->price, 0, ',', '.') : '';
    }

    public function updated($name, $value)
    {
        $this->error_message = '';
        $this->calculated = false;

        // Validasi Format Rupiah di Extra Payment
        if (str_contains($name, 'extra_payments') && str_contains($name, 'amount_formatted')) {
            $parts = explode('.', $name); 
            if(isset($parts[1])) {
                $index = $parts[1];
                $numeric = (int) preg_replace('/[^0-9]/', '', $value);
                $this->extra_payments[$index]['amount'] = $numeric;
                $this->extra_payments[$index]['amount_formatted'] = $numeric > 0 ? number_format($numeric, 0, ',', '.') : '';
            }
        }
    }

    public function addRateTier() { $this->fix_rates[] = ['rate' => 5.0, 'years' => 1]; }
    public function removeRateTier($index) { unset($this->fix_rates[$index]); $this->fix_rates = array_values($this->fix_rates); }
    public function addPayment() { $this->extra_payments[] = ['month' => 12, 'amount' => 0, 'amount_formatted' => '', 'penalty' => 0]; }
    public function removePayment($index) { unset($this->extra_payments[$index]); $this->extra_payments = array_values($this->extra_payments); }

    // --- HITUNG KPR (SAFE MODE) ---
    public function calculate()
    {
        $this->error_message = '';
        $this->schedule = [];
        
        // 1. SANITASI INPUT UTAMA
        $dp_safe = is_numeric($this->dp_percent) ? (float)$this->dp_percent : 0;
        $tenor_safe = is_numeric($this->tenor_years) ? (float)$this->tenor_years : 0;

        if ($this->price <= 0 || $tenor_safe <= 0) {
            $this->error_message = "Harga properti dan Tenor tidak boleh kosong atau nol.";
            return;
        }

        // 2. Validasi Total Tahun Fix Rate
        $totalFixYears = 0;
        foreach($this->fix_rates as $tier) {
            $totalFixYears += (int) $tier['years'];
        }

        if ($totalFixYears > $tenor_safe) {
            $this->error_message = "Total masa bunga fix ($totalFixYears tahun) melebihi tenor pinjaman ($tenor_safe tahun). Mohon kurangi tahun pada bunga berjenjang.";
            return; 
        }

        // 3. Lanjut Perhitungan
        $this->calculated = true;
        
        $loan_amount = $this->price - ($this->price * ($dp_safe / 100));
        $balance = $loan_amount;
        $total_months = $tenor_safe * 12;
        
        $total_interest_paid = 0;

        // Peta Bunga
        $rate_schedule = [];
        $current_month_pointer = 1;

        foreach($this->fix_rates as $tier) {
            $duration_months = $tier['years'] * 12;
            for($i = 0; $i < $duration_months; $i++) {
                if($current_month_pointer <= $total_months) {
                    $rate_schedule[$current_month_pointer] = $tier['rate'];
                    $current_month_pointer++;
                }
            }
        }

        for($i = $current_month_pointer; $i <= $total_months; $i++) {
            $rate_schedule[$i] = $this->rate_float;
        }

        // Peta Top Up
        $topup_map = [];
        foreach($this->extra_payments as $pay) {
            if($pay['amount'] > 0 && $pay['month'] > 0 && $pay['month'] <= $total_months) {
                $topup_map[$pay['month']] = $pay;
            }
        }

        $current_rate = $rate_schedule[1] ?? $this->rate_float;
        $monthly_payment = $this->pmt($balance, $current_rate, $total_months);

        for ($month = 1; $month <= $total_months; $month++) {
            
            $this_month_rate = $rate_schedule[$month];
            $prev_month_rate = ($month > 1) ? $rate_schedule[$month-1] : $this_month_rate;

            if ($month > 1 && $this_month_rate != $prev_month_rate) {
                $remaining_tenor = $total_months - $month + 1;
                $monthly_payment = $this->pmt($balance, $this_month_rate, $remaining_tenor);
            }

            $interest_portion = $balance * (($this_month_rate / 100) / 12);
            $principal_portion = $monthly_payment - $interest_portion;

            $extra_pay = 0;
            $penalty_pay = 0;
            $is_topup = false;

            if (isset($topup_map[$month])) {
                $topup_data = $topup_map[$month];
                $extra_pay = $topup_data['amount'];
                $penalty_pay = $extra_pay * ($topup_data['penalty'] / 100);
                $is_topup = true;
            }

            if (($principal_portion + $extra_pay) > $balance) {
                $principal_portion = $balance - $extra_pay; 
                if($principal_portion < 0) {
                    $extra_pay = $balance;
                    $principal_portion = 0;
                }
                $monthly_payment = $principal_portion + $interest_portion;
            }

            $balance = $balance - $principal_portion - $extra_pay;
            
            $this->schedule[] = [
                'month' => $month,
                'year' => ceil($month / 12),
                'rate' => $this_month_rate,
                'payment' => $monthly_payment,
                'interest' => $interest_portion,
                'principal' => $principal_portion,
                'extra' => $extra_pay,
                'penalty' => $penalty_pay,
                'balance' => max(0, $balance)
            ];

            $total_interest_paid += $interest_portion;

            if ($is_topup && $balance > 0) {
                $remaining_tenor = $total_months - $month;
                $monthly_payment = $this->pmt($balance, $this_month_rate, $remaining_tenor);
            }

            if ($balance <= 100) break; 
        }

        $this->summary = [
            'loan_amount' => $loan_amount,
            'total_interest' => $total_interest_paid,
            'total_paid' => $loan_amount + $total_interest_paid + array_sum(array_column($this->schedule, 'penalty')),
            'last_month' => count($this->schedule)
        ];
    }

    private function pmt($principal, $rate_yearly, $months) {
        if ($rate_yearly <= 0) return $principal / $months;
        $rate_monthly = ($rate_yearly / 100) / 12;
        if ($months <= 0) return 0;
        return $principal * ($rate_monthly / (1 - pow(1 + $rate_monthly, -$months)));
    }

    public function rendering(View $view): void { $view->layoutData(['title' => 'Kalkulator KPR Pro - Roov Property', 'description' => 'Hitung simulasi cicilan rumah dengan bunga fix, floating, dan top up.']); }
}; ?>

<div class="bg-gray-50 min-h-screen font-sans pb-12">
    
    {{-- NAVBAR PUTIH --}}
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white text-gray-800 sticky top-0 z-50 shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center"><a href="{{ route('home') }}" wire:navigate class="flex items-center"><img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-12 w-auto object-contain"></a></div>
                <div class="hidden md:flex items-center gap-8 font-bold text-sm tracking-wide">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>HOME</a>
                    <a href="{{ route('developers') }}" class="text-gray-600 hover:text-[#0A192F] transition" wire:navigate>DEVELOPERS</a>
                    <a href="{{ route('kpr') }}" class="text-[#D4AF37] transition" wire:navigate>KALKULATOR KPR</a>
                </div>
                <div class="flex md:hidden"><button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-gray-600 hover:text-[#0A192F] focus:outline-none p-2"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg></button></div>
            </div>
        </div>
        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-100 shadow-xl absolute w-full z-50" style="display: none;">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <a href="{{ route('home') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Home</a>
                <a href="{{ route('developers') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-gray-800 hover:bg-gray-50">Developers</a>
                <a href="{{ route('kpr') }}" wire:navigate class="block w-full text-left px-3 py-2 rounded-md text-base font-bold text-[#D4AF37] hover:bg-gray-50">Kalkulator KPR</a>
            </div>
        </div>
    </nav>

    {{-- HEADER --}}
    <div class="bg-[#0A192F] pt-10 pb-20 text-center px-4">
        <h1 class="text-3xl font-extrabold text-white mb-2">Kalkulator KPR Pro</h1>
        <p class="text-gray-400">Simulasi cicilan KPR Berjenjang & Top Up Sebagian.</p>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-10 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- INPUT FORM --}}
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-[#D4AF37]">
                    <h3 class="font-bold text-[#0A192F] mb-4 border-b pb-2">1. Data Pinjaman</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga Properti (Rp)</label>
                            <input type="text" wire:model.live="price_formatted" class="w-full rounded-lg border-gray-300 focus:ring-[#D4AF37] font-bold text-lg" placeholder="0">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">DP (%)</label>
                                <input type="number" wire:model="dp_percent" class="w-full rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tenor (Thn)</label>
                                <input type="number" wire:model="tenor_years" class="w-full rounded-lg border-gray-300">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. BUNGA BERJENJANG --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-[#0A192F]">2. Bunga Berjenjang</h3>
                        <button wire:click="addRateTier" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 font-bold">+ Jenjang</button>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        @foreach($fix_rates as $index => $tier)
                            <div class="flex gap-2 items-center">
                                <div class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full text-xs font-bold text-gray-500">{{ $index + 1 }}</div>
                                <div class="flex-1 grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[10px] text-gray-400 block">Bunga Fix (%)</label>
                                        <input type="number" step="0.1" wire:model="fix_rates.{{ $index }}.rate" class="w-full rounded border-gray-300 text-sm py-1">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-400 block">Selama (Thn)</label>
                                        <input type="number" wire:model="fix_rates.{{ $index }}.years" class="w-full rounded border-gray-300 text-sm py-1">
                                    </div>
                                </div>
                                <button wire:click="removeRateTier({{ $index }})" class="text-red-400 hover:text-red-600 font-bold px-1">x</button>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <label class="block text-xs font-bold text-blue-800 uppercase mb-1">Setelah Fix Berakhir (Floating)</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🌊</span>
                            <input type="number" step="0.1" wire:model="rate_float" class="w-full rounded border-blue-200 text-sm focus:ring-blue-500">
                            <span class="text-sm font-bold text-blue-800">%</span>
                        </div>
                    </div>
                </div>

                {{-- 3. EXTRA PAYMENT --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-green-500">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-[#0A192F]">3. Extra Payment</h3>
                        <button wire:click="addPayment" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 font-bold">+ Tambah</button>
                    </div>

                    @if(empty($extra_payments))
                        <p class="text-center text-gray-400 text-xs py-2 italic">Belum ada rencana top up.</p>
                    @endif

                    <div class="space-y-4">
                        @foreach($extra_payments as $index => $payment)
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 relative">
                                <button wire:click="removePayment({{ $index }})" class="absolute top-1 right-2 text-red-400 hover:text-red-600 font-bold text-xs">x</button>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <div>
                                        <label class="text-[10px] text-gray-500 block">Bulan Ke-</label>
                                        <input type="number" min="1" max="{{ $tenor_years * 12 }}" wire:model="extra_payments.{{ $index }}.month" class="w-full rounded border-gray-300 text-sm py-1 placeholder-gray-300" placeholder="Cth: 12">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 block">Denda (%)</label>
                                        <input type="number" step="0.1" wire:model="extra_payments.{{ $index }}.penalty" class="w-full rounded border-gray-300 text-sm py-1">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-500 block">Jumlah Top Up (Rp)</label>
                                    <input type="text" 
                                           wire:model.live="extra_payments.{{ $index }}.amount_formatted" 
                                           class="w-full rounded border-gray-300 text-sm py-1 font-bold focus:ring-green-500" 
                                           placeholder="0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PESAN ERROR --}}
                @if($error_message)
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg border border-red-200 text-sm font-bold animate-pulse">
                        ⚠️ {{ $error_message }}
                    </div>
                @endif

                <button wire:click="calculate" class="w-full bg-[#D4AF37] hover:bg-yellow-500 text-white font-bold py-4 rounded-xl shadow-lg transform active:scale-95 transition text-lg">
                    HITUNG SEKARANG 🚀
                </button>

            </div>

            {{-- HASIL --}}
            <div class="lg:col-span-8">
                @if($calculated)
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                                <span class="text-xs text-gray-400 font-bold uppercase">Pokok Pinjaman</span>
                                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($summary['loan_amount'], 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-500">
                                <span class="text-xs text-gray-400 font-bold uppercase">Total Bunga</span>
                                <p class="text-xl font-bold text-red-600">Rp {{ number_format($summary['total_interest'], 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
                                <span class="text-xs text-gray-400 font-bold uppercase">Lama Angsuran</span>
                                <p class="text-xl font-bold text-green-600">{{ $summary['last_month'] }} Bulan</p>
                                <p class="text-xs text-gray-400">Hemat {{ ($tenor_years * 12) - $summary['last_month'] }} bulan</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                            <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                                <h3 class="font-bold text-gray-700">Rincian Pembayaran Bulanan</h3>
                            </div>
                            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-4 py-3 text-center">Bln</th>
                                            <th class="px-4 py-3">Bunga</th>
                                            <th class="px-4 py-3">Cicilan Total</th>
                                            <th class="px-4 py-3">Porsi Pokok</th>
                                            <th class="px-4 py-3">Porsi Bunga</th>
                                            <th class="px-4 py-3 text-right">Sisa Hutang</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($schedule as $row)
                                            <tr class="hover:bg-gray-50 {{ $row['extra'] > 0 ? 'bg-green-50' : '' }}">
                                                <td class="px-4 py-3 text-center font-mono text-gray-500">{{ $row['month'] }}</td>
                                                <td class="px-4 py-3 text-xs">
                                                    <span class="bg-gray-200 px-2 py-0.5 rounded font-bold {{ $row['rate'] > 10 ? 'text-red-600' : 'text-blue-600' }}">{{ $row['rate'] }}%</span>
                                                </td>
                                                <td class="px-4 py-3 font-bold text-[#0A192F]">
                                                    Rp {{ number_format($row['payment'], 0, ',', '.') }}
                                                    @if($row['extra'] > 0)
                                                        <div class="text-xs text-green-600 font-bold mt-1">
                                                            + Top Up: {{ number_format($row['extra'], 0, ',', '.') }}
                                                        </div>
                                                        @if($row['penalty'] > 0)
                                                            <div class="text-[10px] text-red-500">
                                                                (Denda: {{ number_format($row['penalty'], 0) }})
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-gray-600">{{ number_format($row['principal'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-red-500">{{ number_format($row['interest'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-right font-bold text-gray-800">
                                                    Rp {{ number_format($row['balance'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-12 text-center h-full flex flex-col items-center justify-center text-gray-400">
                        <span class="text-6xl mb-4">🧮</span>
                        <h3 class="text-xl font-bold text-gray-600">Mulai Simulasi</h3>
                        <p class="text-sm">Masukkan detail properti dan rencana pembayaran Anda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <footer class="bg-white text-gray-600 pt-16 pb-8 border-t border-gray-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo Roov" class="h-10 w-auto">
            </div>
            <p class="opacity-75 text-sm">&copy; {{ date('Y') }} Website Roov. All rights reserved.</p>
        </div>
    </footer>
</div>
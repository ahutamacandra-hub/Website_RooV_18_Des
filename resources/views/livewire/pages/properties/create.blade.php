<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads; 
use Livewire\Attributes\Layout; 
use App\Models\Property;
use Illuminate\Support\Str;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $title, $description, $price;
    public $listing_type = 'primary', $property_type = 'rumah'; 
    public $bedrooms, $bathrooms, $land_area, $building_area, $floor_count, $certificate, $electricity;
    
    // Fitur Baru V3
    public $maid_bedrooms, $maid_bathrooms;
    public $orientation, $furnishing, $is_hook = false;

    // Fitur V2
    public $has_pool = false, $has_carport = false, $has_garden = false;

    public $city, $district, $address, $google_maps_link, $photo;

    public function store()
    {
        $this->validate([
            'title' => 'required|min:5',
            'price' => 'required|numeric',
            'description' => 'required',
            'listing_type' => 'required',
            'city' => 'required',
            'address' => 'required',
            'photo' => 'nullable|image|max:2048', 
        ]);

        $photoPath = $this->photo ? $this->photo->store('properties', 'public') : null;

        Property::create([
            'user_id' => auth()->id(), 
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . rand(1000, 9999),
            'description' => $this->description,
            'price' => $this->price,
            'listing_type' => $this->listing_type,
            'property_type' => $this->property_type,
            'bedrooms' => $this->bedrooms,
            'maid_bedrooms' => $this->maid_bedrooms,
            'bathrooms' => $this->bathrooms,
            'maid_bathrooms' => $this->maid_bathrooms,
            'land_area' => $this->land_area,
            'building_area' => $this->building_area,
            'floor_count' => $this->floor_count,
            'certificate' => $this->certificate,
            'electricity' => $this->electricity,
            'orientation' => $this->orientation,
            'furnishing' => $this->furnishing,
            'is_hook' => $this->is_hook,
            'has_pool' => $this->has_pool,
            'has_carport' => $this->has_carport,
            'has_garden' => $this->has_garden,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'google_maps_link' => $this->google_maps_link,
            'photo' => $photoPath,
        ]);

        session()->flash('message', 'Properti berhasil ditambahkan!');
        return $this->redirect('/properties/create');
    }
}; ?>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Upload Properti (Lengkap)</h2>

            @if (session()->has('message'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded mb-6 border border-green-200">{{ session('message') }}</div>
            @endif

            <form wire:submit="store" class="space-y-8">
                
                {{-- 1. Info Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="label">Judul Iklan</label>
                        <input type="text" wire:model="title" class="input-form" placeholder="Rumah Mewah 2 Lantai di Pakuwon">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label">Harga (Rp)</label>
                        <input type="number" wire:model="price" class="input-form">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Tipe Listing</label>
                            <select wire:model="listing_type" class="input-form">
                                <option value="primary">Primary (Baru)</option>
                                <option value="secondary">Secondary (Bekas)</option>
                                <option value="lelang">Lelang</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Jenis Properti</label>
                            <select wire:model="property_type" class="input-form">
                                <option value="rumah">Rumah</option>
                                <option value="apartemen">Apartemen</option>
                                <option value="ruko">Ruko</option>
                                <option value="tanah">Tanah</option>
                                <option value="gudang">Gudang</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. Spesifikasi Ruangan --}}
                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Spesifikasi Ruangan & Dimensi</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="label">K. Tidur Utama</label><input type="number" wire:model="bedrooms" class="input-form"></div>
                        <div><label class="label">K. Mandi Utama</label><input type="number" wire:model="bathrooms" class="input-form"></div>
                        <div><label class="label">K. Pembantu</label><input type="number" wire:model="maid_bedrooms" class="input-form"></div>
                        <div><label class="label">KM. Pembantu</label><input type="number" wire:model="maid_bathrooms" class="input-form"></div>
                        
                        <div class="mt-2"><label class="label">L. Tanah (m²)</label><input type="number" wire:model="land_area" class="input-form"></div>
                        <div class="mt-2"><label class="label">L. Bangunan (m²)</label><input type="number" wire:model="building_area" class="input-form"></div>
                        <div class="mt-2"><label class="label">Jml Lantai</label><input type="number" wire:model="floor_count" class="input-form"></div>
                        <div class="mt-2"><label class="label">Listrik (Watt)</label><input type="number" wire:model="electricity" class="input-form"></div>
                    </div>
                </div>

                {{-- 3. Detail & Legalitas --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="label">Sertifikat</label>
                        <select wire:model="certificate" class="input-form">
                            <option value="">Pilih...</option>
                            <option value="SHM">SHM (Hak Milik)</option>
                            <option value="HGB">HGB (Guna Bangunan)</option>
                            <option value="Strata Title">Strata Title</option>
                            <option value="Surat Ijo">Surat Ijo</option>
                            <option value="Petok D">Petok D / Girik</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Arah Hadap</label>
                        <select wire:model="orientation" class="input-form">
                            <option value="">Pilih...</option>
                            <option value="Utara">Utara</option>
                            <option value="Selatan">Selatan</option>
                            <option value="Timur">Timur</option>
                            <option value="Barat">Barat</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Furnishing</label>
                        <select wire:model="furnishing" class="input-form">
                            <option value="">Pilih...</option>
                            <option value="Unfurnished">Unfurnished (Kosong)</option>
                            <option value="Semi Furnished">Semi Furnished</option>
                            <option value="Full Furnished">Full Furnished</option>
                        </select>
                    </div>
                </div>

                {{-- 4. Fasilitas Checkbox --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="font-bold text-gray-700 mb-3">Fasilitas & Keunggulan</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="has_pool" class="w-5 h-5 text-indigo-600 rounded"><span>🏊 Kolam Renang</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="has_carport" class="w-5 h-5 text-indigo-600 rounded"><span>🚗 Carport/Garasi</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="has_garden" class="w-5 h-5 text-indigo-600 rounded"><span>🌳 Taman</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_hook" class="w-5 h-5 text-indigo-600 rounded"><span>📐 Posisi Hook</span>
                        </label>
                    </div>
                </div>

                {{-- 5. Lokasi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="label">Kota/Kabupaten</label><input type="text" wire:model="city" class="input-form"></div>
                    <div><label class="label">Kecamatan</label><input type="text" wire:model="district" class="input-form"></div>
                </div>

                <div>
                    <label class="label">Deskripsi Lengkap</label>
                    <textarea wire:model="description" rows="4" class="input-form"></textarea>
                </div>
                
                <div>
                    <label class="label">Foto Utama</label>
                    <input type="file" wire:model="photo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 shadow-lg transform hover:-translate-y-0.5 transition w-full md:w-auto">
                        SIMPAN PROPERTI LENGKAP
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
        .input-form { width: 100%; border-radius: 0.5rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .input-form:focus { border-color: #6366F1; ring: #6366F1; }
    </style>
</div>
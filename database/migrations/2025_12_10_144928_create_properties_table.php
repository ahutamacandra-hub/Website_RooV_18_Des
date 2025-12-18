<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // --- Informasi Dasar ---
            $table->string('title'); // Judul Listing (Contoh: Rumah Mewah di Pakuwon)
            $table->string('slug')->unique(); // URL SEO (Contoh: rumah-mewah-di-pakuwon)
            $table->text('description'); // Deskripsi lengkap
            $table->unsignedBigInteger('price'); // Harga (gunakan BigInteger untuk angka besar)

            // --- Kategori & Tipe (Filter Utama) ---
            // listing_type: 'primary' (Baru), 'secondary' (Bekas), 'lelang' (Lelang)
            $table->enum('listing_type', ['primary', 'secondary', 'lelang'])->index();

            // property_type: 'rumah', 'tanah', 'apartemen', 'ruko', 'gudang'
            $table->enum('property_type', ['rumah', 'tanah', 'apartemen', 'ruko', 'gudang'])->index();

            // --- Spesifikasi (Filter Lanjutan) ---
            $table->integer('bedrooms')->nullable(); // Kamar Tidur
            $table->integer('bathrooms')->nullable(); // Kamar Mandi
            $table->integer('land_area')->nullable(); // Luas Tanah (m2)
            $table->integer('building_area')->nullable(); // Luas Bangunan (m2)
            $table->integer('floor_count')->nullable(); // Jumlah Lantai
            $table->string('certificate')->nullable(); // SHM, HGB, dll

            // --- Lokasi (Penting untuk SEO & Pencarian) ---
            $table->string('city')->index(); // Kota (Surabaya, Sidoarjo)
            $table->string('district')->index(); // Kecamatan (Sukolilo, Gubeng)
            $table->text('address'); // Alamat lengkap
            $table->string('google_maps_link')->nullable(); // Link Google Maps

            // --- SEO Meta Tags (Wajib untuk Google) ---
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            // --- Status & User ---
            $table->boolean('is_active')->default(true); // Untuk menyembunyikan listing
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa yang upload

            $table->timestamps();

            // Optimasi Shared Hosting: Indexing manual untuk pencarian cepat tanpa Redis/Elasticsearch
            $table->index(['price', 'listing_type', 'property_type']); // Composite index untuk filter umum
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

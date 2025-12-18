<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Menambah kolom fitur spesifik (Boolean = Ya/Tidak)
            $table->boolean('has_pool')->default(false)->after('certificate'); // Kolam Renang
            $table->boolean('has_carport')->default(false)->after('has_pool'); // Carport/Garasi
            $table->boolean('has_garden')->default(false)->after('has_carport'); // Taman

            // Menambah kolom listrik agar lengkap sekalian
            $table->integer('electricity')->nullable()->after('has_garden'); // Watt
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['has_pool', 'has_carport', 'has_garden', 'electricity']);
        });
    }
};

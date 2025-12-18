<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Arah Hadap & Furnish
            $table->string('orientation')->nullable()->after('electricity');
            $table->string('furnishing')->nullable()->after('orientation');

            // Fitur Tambahan (Baru)
            $table->string('water_source')->nullable()->after('furnishing'); // PDAM/Sumur
            $table->integer('garage_size')->default(0)->after('water_source'); // Kapasitas Garasi (Mobil)
            $table->integer('carport_size')->default(0)->after('garage_size'); // Kapasitas Carport (Mobil)

            // Boolean Features (Ada/Tidak)
            $table->boolean('is_hook')->default(false)->after('carport_size');
            $table->boolean('has_canopy')->default(false)->after('is_hook');
            $table->boolean('has_smart_home')->default(false)->after('has_canopy');
            $table->boolean('has_fence')->default(false)->after('has_smart_home'); // Pagar

            // Kamar Pembantu
            $table->integer('maid_bedrooms')->default(0)->after('bedrooms');
            $table->integer('maid_bathrooms')->default(0)->after('bathrooms');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'orientation',
                'furnishing',
                'water_source',
                'garage_size',
                'carport_size',
                'is_hook',
                'has_canopy',
                'has_smart_home',
                'has_fence',
                'maid_bedrooms',
                'maid_bathrooms'
            ]);
        });
    }
};

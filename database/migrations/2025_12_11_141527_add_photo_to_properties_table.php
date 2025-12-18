<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Kolom Foto Utama (Thumbnail)
            $table->string('photo')->nullable()->after('title');

            // Kolom Galeri (Menyimpan banyak link foto dalam format JSON)
            $table->text('gallery')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['photo', 'gallery']);
        });
    }
};

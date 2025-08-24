<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('provinsi_id')->nullable();     // Menambah kolom provinsi_id (untuk ID Provinsi)
            $table->string('kabupaten_id')->nullable();    // Menambah kolom kabupaten_id (untuk ID Kabupaten)
            $table->string('kecamatan_id')->nullable();    // Menambah kolom kecamatan_id (untuk ID Kecamatan)
            $table->string('provinsi_name')->nullable();   // Menambah kolom provinsi_name (untuk nama Provinsi)
            $table->string('kabupaten_name')->nullable();  // Menambah kolom kabupaten_name (untuk nama Kabupaten)
            $table->string('kecamatan_name')->nullable();  // Menambah kolom kecamatan_name (untuk nama Kecamatan)
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};

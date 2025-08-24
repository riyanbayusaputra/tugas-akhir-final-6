<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi_id');
            $table->string('provinsi_name');
            $table->string('kabupaten_id');
            $table->string('kabupaten_name');
            $table->string('kecamatan_id')->nullable();
            $table->string('kecamatan_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['provinsi_id', 'kabupaten_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_areas');
    }
};
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
        Schema::create('text_data', function (Blueprint $table) {
            $table->id();
            $table->longText('text');
            $table->unsignedBigInteger('file_id');
            $table->foreign('file_id')->references('id')->on('pdfdocs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_data');
    }
};

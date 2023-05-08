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
        Schema::create('text_vectors', function (Blueprint $table) {
            $table->id();
            $table->json('vector');
            $table->unsignedBigInteger('text_id');
            $table->unsignedBigInteger('file_id');
            $table->foreign('text_id')->references('id')->on('text_data')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('pdfdocs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_vectors');
    }
};

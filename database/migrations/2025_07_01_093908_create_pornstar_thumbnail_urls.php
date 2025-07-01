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
        Schema::create('pornstar_thumbnail_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thumbnail_id')->constrained('pornstar_thumbnails')->onDelete('cascade');
            $table->string('url');                  // Remote CDN URL
            $table->string('local_path')->nullable(); // Local cached file path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pornstar_thumbnail_urls');
    }
};

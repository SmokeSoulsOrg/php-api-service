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
        Schema::create('pornstars', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('link');
            $table->string('license')->nullable();
            $table->boolean('wl_status')->default(false);

            // Physical and sexual attributes
            $table->string('hair_color')->nullable();
            $table->string('ethnicity')->nullable();
            $table->boolean('has_tattoos')->nullable();
            $table->boolean('has_piercings')->nullable();
            $table->integer('breast_size')->nullable();
            $table->string('breast_type', 4)->nullable();
            $table->string('gender', 64)->nullable();
            $table->string('orientation', 16)->nullable();
            $table->integer('age')->nullable();

            // Stats
            $table->bigInteger('subscriptions')->nullable();
            $table->bigInteger('monthly_searches')->nullable();
            $table->bigInteger('views')->nullable();
            $table->integer('videos_count')->nullable();
            $table->integer('premium_videos_count')->nullable();
            $table->integer('white_label_video_count')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('rank_premium')->nullable();
            $table->integer('rank_wl')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pornstars');
    }
};

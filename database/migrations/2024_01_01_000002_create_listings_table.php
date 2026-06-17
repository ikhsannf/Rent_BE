<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price_per_day', 12, 2);
            $table->decimal('deposit', 12, 2)->nullable();
            $table->enum('condition', ['new', 'good', 'fair']);
            $table->enum('status', ['available', 'rented', 'unavailable'])->default('available');
            $table->string('location')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('min_rent_days')->default(1);
            $table->integer('max_rent_days')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index(['user_id', 'status']);
            
            // Only add fullText index if the driver supports it (MySQL/PostgreSQL)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};

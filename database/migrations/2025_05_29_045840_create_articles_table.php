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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary')->nullable(); // Ringkasan artikel
            $table->longText('content'); // Konten utama artikel
            $table->string('author', 100);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('featured_image')->nullable(); // URL gambar utama
            $table->string('tags')->nullable(); // Tags dalam format comma-separated
            $table->integer('views')->default(0); // Jumlah view artikel
            $table->boolean('is_featured')->default(false); // Artikel unggulan
            $table->timestamp('published_at')->nullable(); // Waktu publikasi
            $table->timestamps();
            
            // Indexes untuk optimasi query
            $table->index('status');
            $table->index('published_at');
            $table->index('category_id');
            $table->index('is_featured');
            $table->index(['status', 'published_at']);
            $table->fullText(['title', 'summary', 'content']); // Full-text search
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
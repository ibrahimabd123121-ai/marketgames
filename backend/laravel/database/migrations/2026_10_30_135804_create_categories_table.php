<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // القسم الأب، إذا كان القسم فرعياً
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->restrictOnDelete();

            // المعلومات الأساسية
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();

            // مسار صورة القسم
            $table->string('image')->nullable();

            // ترتيب العرض
            $table->unsignedInteger('sort_order')->default(0);

            // حالة القسم
            $table->boolean('status')->default(true);

            $table->timestamps();

            // تحسين الاستعلامات حسب حالة القسم وترتيبه
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
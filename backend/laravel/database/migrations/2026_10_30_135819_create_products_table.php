<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('status')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique(['slug']);

            $table->dropColumn([
                'category_id',
                'name',
                'slug',
                'description',
                'image',
                'price',
                'stock',
                'status',
            ]);
        });
    }
};
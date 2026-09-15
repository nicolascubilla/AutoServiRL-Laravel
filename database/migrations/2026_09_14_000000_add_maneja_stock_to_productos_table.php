<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'maneja_stock')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('maneja_stock', 1)->default('S');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'maneja_stock')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('maneja_stock');
            });
        }
    }
};
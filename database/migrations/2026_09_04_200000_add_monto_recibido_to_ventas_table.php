<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas', 'monto_recibido')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->integer('monto_recibido')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ventas', 'monto_recibido')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropColumn('monto_recibido');
            });
        }
    }
};
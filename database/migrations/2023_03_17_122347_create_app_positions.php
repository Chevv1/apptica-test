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
        Schema::create('app_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\App::class);
            $table->foreignIdFor(\App\Models\Category::class);
            $table->integer('position');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_positions');
    }
};

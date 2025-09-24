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
        if (!Schema::hasTable('flats')) {
            Schema::create('flats', function (Blueprint $table) {
                $table->id();
                $table->string('flat_number');
                $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ✅ added user_id
                $table->date('rent_due_date')->nullable();
                $table->decimal('rent_amount', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flats');
    }
};

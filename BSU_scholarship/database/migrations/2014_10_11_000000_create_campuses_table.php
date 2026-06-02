<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Pablo Borbon, Alangilan, ARASOF
            $table->enum('type', ['constituent', 'extension'])->default('constituent');
            $table->foreignId('parent_campus_id')->nullable()->constrained('campuses')->onDelete('cascade');
            $table->boolean('has_sfao_admin')->default(false); // Only constituent campuses have SFAO admins
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campuses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Address
            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('town')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            
            // Academics (if not in users, but we will sync or keep here for detail)
            // Users table has program/college, but profile might track history or extra details.
            // We will keep GWA and Units here as they change per sem.
            $table->decimal('gwa', 4, 2)->nullable();
            $table->integer('units_enrolled')->nullable();
            
            // Family
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->decimal('annual_gross_income', 12, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_profiles');
    }
};

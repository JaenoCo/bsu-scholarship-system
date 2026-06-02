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
        Schema::create('scholarship_required_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholarship_id');
            $table->string('document_name'); // e.g. 'Barangay Clearance', 'Birth Certificate'
            $table->enum('document_type', ['pdf', 'image', 'both'])->default('pdf');
            $table->boolean('is_mandatory')->default(true);
            $table->text('description')->nullable(); // Optional description of the document
            $table->timestamps();

            $table->foreign('scholarship_id')
                ->references('id')->on('scholarships')
                ->onDelete('cascade');
                
            $table->index(['scholarship_id', 'is_mandatory']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scholarship_required_documents');
    }
};